<?php
/**
 * Service layer for the Gutena Forms template library.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Form_Template_Service' ) ) :
	/**
	 * Query, format, and apply template catalog operations.
	 *
	 * @since 2.2.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Form_Template_Service {
		/**
		 * Template registry instance.
		 *
		 * @since 2.2.0
		 * @var Gutena_Forms_Form_Template_Registry
		 */
		private $registry;

		/**
		 * Constructor.
		 *
		 * @since 2.2.0
		 */
		public function __construct() {
			$this->registry = Gutena_Forms_Form_Template_Registry::get_instance();
		}

		/**
		 * Get filtered and paginated template summaries.
		 *
		 * @since 2.2.0
		 * @param array $args {
		 *     Query arguments.
		 *
		 *     @type string $category Optional category slug.
		 *     @type string $search   Optional search string.
		 *     @type int    $page     Page number.
		 *     @type int    $per_page Items per page.
		 * }
		 * @return array
		 */
		public function get_templates( $args = array() ) {
			$defaults = array(
				'category' => '',
				'search'   => '',
				'page'     => 1,
				'per_page' => 20,
			);

			$args = wp_parse_args( $args, $defaults );

			$page     = max( 1, absint( $args['page'] ) );
			$per_page = max( 1, min( 100, absint( $args['per_page'] ) ) );
			$category = sanitize_key( $args['category'] );
			$search   = sanitize_text_field( $args['search'] );

			$templates = array_values( $this->registry->get_all() );

			if ( '' !== $category ) {
				$templates = array_values(
					array_filter(
						$templates,
						function ( $template ) use ( $category ) {
							return isset( $template['category'] ) && $category === sanitize_key( $template['category'] );
						}
					)
				);
			}

			if ( '' !== $search ) {
				$templates = array_values(
					array_filter(
						$templates,
						function ( $template ) use ( $search ) {
							return $this->template_matches_search( $template, $search );
						}
					)
				);
			}

			$total       = count( $templates );
			$total_pages = $total > 0 ? (int) ceil( $total / $per_page ) : 0;
			$offset      = ( $page - 1 ) * $per_page;
			$templates   = array_slice( $templates, $offset, $per_page );

			return array(
				'templates'       => array_map( array( $this, 'format_template_summary' ), $templates ),
				'category_counts' => $this->registry->get_category_counts(),
				'pagination'      => array(
					'page'        => $page,
					'per_page'    => $per_page,
					'total'       => $total,
					'total_pages' => $total_pages,
				),
			);
		}

		/**
		 * Get category definitions with counts.
		 *
		 * @since 2.2.0
		 * @return array
		 */
		public function get_categories() {
			$labels = $this->registry->get_categories();
			$counts = $this->registry->get_category_counts();
			$items  = array();

			foreach ( $labels as $category_id => $label ) {
				$items[] = array(
					'id'    => $category_id,
					'label' => $label,
					'count' => isset( $counts[ $category_id ] ) ? (int) $counts[ $category_id ] : 0,
				);
			}

			return $items;
		}

		/**
		 * Get a single template detail response.
		 *
		 * @since 2.2.0
		 * @param string $template_id Template identifier.
		 * @return array|WP_Error
		 */
		public function get_template_detail( $template_id ) {
			$template = $this->registry->get_by_id( $template_id );

			if ( null === $template ) {
				return new WP_Error(
					'gutena_forms_template_not_found',
					__( 'Template not found.', 'gutena-forms' ),
					array( 'status' => 404 )
				);
			}

			return $this->format_template_detail( $template );
		}

		/**
		 * Create a gutena_forms post from a registered template.
		 *
		 * @since 2.2.0
		 * @param string $template_id Template identifier.
		 * @param string $form_name   Optional custom form name.
		 * @return array|WP_Error
		 */
		public function create_form_from_template( $template_id, $form_name = '' ) {
			$template = $this->registry->get_by_id( $template_id );

			if ( null === $template ) {
				return new WP_Error(
					'gutena_forms_template_not_found',
					__( 'Template not found.', 'gutena-forms' ),
					array( 'status' => 404 )
				);
			}

			if ( ! $this->can_use_template( $template ) ) {
				return new WP_Error(
					'gutena_forms_template_pro_required',
					__( 'This template requires Gutena Forms Pro.', 'gutena-forms' ),
					array( 'status' => 403 )
				);
			}

			if ( ! current_user_can( 'edit_posts' ) ) {
				return new WP_Error(
					'gutena_forms_template_forbidden',
					__( 'You do not have permission to create forms.', 'gutena-forms' ),
					array( 'status' => 403 )
				);
			}

			$form_name = '' !== $form_name ? sanitize_text_field( $form_name ) : sanitize_text_field( $template['title'] );

			if ( '' === $form_name ) {
				$form_name = __( 'Contact Form', 'gutena-forms' );
			}

			$this->ensure_template_blocks_registered();

			$block_form_id   = Gutena_Forms_Form_Template_Builder::generate_form_id();
			$post_content    = Gutena_Forms_Form_Template_Builder::serialize_form_block( $template, $block_form_id, $form_name );

			if ( null === $post_content || '' === $post_content ) {
				return new WP_Error(
					'gutena_forms_template_build_failed',
					__( 'Unable to create a form from this template.', 'gutena-forms' ),
					array( 'status' => 500 )
				);
			}

			$kses_removed = false;

			// Form field blocks require form/input markup that wp_kses_post strips for non-unfiltered users.
			if ( function_exists( 'kses_remove_filters' ) ) {
				kses_remove_filters();
				$kses_removed = true;
			}

			$post_id = wp_insert_post(
				array(
					'post_type'    => 'gutena_forms',
					'post_title'   => $form_name,
					'post_status'  => 'draft',
					'post_content' => wp_slash( $post_content ),
				),
				true
			);

			if ( $kses_removed && function_exists( 'kses_init_filters' ) ) {
				kses_init_filters();
			}

			if ( is_wp_error( $post_id ) ) {
				return new WP_Error(
					'gutena_forms_template_create_failed',
					__( 'Unable to create the form.', 'gutena-forms' ),
					array(
						'status' => 500,
						'data'   => $post_id->get_error_message(),
					)
				);
			}

			update_post_meta( $post_id, 'gutena_form_id', $block_form_id );

			$this->persist_form_schema( $post_id );

			return array(
				'form' => array(
					'id'       => (int) $post_id,
					'title'    => $form_name,
					'form_id'  => $block_form_id,
					'edit_url' => admin_url( 'post.php?post=' . absint( $post_id ) . '&action=edit' ),
				),
			);
		}

		/**
		 * Ensure Gutena field blocks are registered before building template content.
		 *
		 * @since 2.2.0
		 */
		private function ensure_template_blocks_registered() {
			if ( ! function_exists( 'WP_Block_Type_Registry' ) ) {
				return;
			}

			$registry = WP_Block_Type_Registry::get_instance();

			if ( $registry->is_registered( 'gutena/text-field' ) ) {
				return;
			}

			if ( class_exists( 'Gutena_Forms' ) ) {
				$plugin = Gutena_Forms::get_instance();

				if ( method_exists( $plugin, 'register_blocks_and_scripts' ) ) {
					$plugin->register_blocks_and_scripts();
				}
			}
		}

		/**
		 * Persist the form schema after creating a template-based form post.
		 *
		 * @since 2.2.0
		 * @param int $post_id Created form post ID.
		 */
		private function persist_form_schema( $post_id ) {
			$post = get_post( $post_id );

			if ( ! $post || ! class_exists( 'Gutena_Forms' ) ) {
				return;
			}

			$plugin = Gutena_Forms::get_instance();

			if ( method_exists( $plugin, 'save_gutena_forms_schema' ) ) {
				$plugin->save_gutena_forms_schema( $post_id, $post, false );
			}
		}

		/**
		 * Whether the current user may preview a template.
		 *
		 * @since 2.2.0
		 * @param array $template Template definition.
		 * @return bool
		 */
		public function can_preview_template( $template ) {
			return is_array( $template ) && ! empty( $template['id'] );
		}

		/**
		 * Whether the current user may create a form from a template.
		 *
		 * @since 2.2.0
		 * @param array $template Template definition.
		 * @return bool
		 */
		public function can_use_template( $template ) {
			if ( empty( $template['is_pro'] ) ) {
				return true;
			}

			// Pro templates require a valid Pro entitlement (GUTENA_FORMS__PRO_LOADED).
			return defined( 'GUTENA_FORMS__PRO_LOADED' ) && GUTENA_FORMS__PRO_LOADED;
		}

		/**
		 * Case-insensitive search against title and description.
		 *
		 * @since 2.2.0
		 * @param array  $template Template definition.
		 * @param string $search   Search string.
		 * @return bool
		 */
		private function template_matches_search( $template, $search ) {
			$search = strtolower( $search );
			$haystack = strtolower(
				trim(
					( isset( $template['title'] ) ? $template['title'] : '' ) . ' ' .
					( isset( $template['description'] ) ? $template['description'] : '' )
				)
			);

			return false !== strpos( $haystack, $search );
		}

		/**
		 * Format a template for list responses.
		 *
		 * @since 2.2.0
		 * @param array $template Template definition.
		 * @return array
		 */
		public function format_template_summary( $template ) {
			$category_labels = $this->registry->get_categories();
			$category_id     = isset( $template['category'] ) ? sanitize_key( $template['category'] ) : '';

			return array(
				'id'              => $template['id'],
				'title'           => $template['title'],
				'description'     => $template['description'],
				'category'        => $category_id,
				'category_label'  => isset( $category_labels[ $category_id ] ) ? $category_labels[ $category_id ] : '',
				'is_pro'          => (bool) $template['is_pro'],
				'is_visible'      => true,
				'can_preview'     => $this->can_preview_template( $template ),
				'can_use'         => $this->can_use_template( $template ),
				'submit_label'    => $template['submit_label'],
				'preview_image'   => $this->format_preview_image_url( $template ),
			);
		}

		/**
		 * Format a template for detail responses.
		 *
		 * @since 2.2.0
		 * @param array $template Template definition.
		 * @return array
		 */
		public function format_template_detail( $template ) {
			$summary = $this->format_template_summary( $template );

			$summary['fields']          = $template['fields'];
			$summary['success_message'] = $template['success_message'];

			return $summary;
		}

		/**
		 * Convert a relative preview image path to an absolute URL.
		 *
		 * @since 2.2.0
		 * @param array $template Template definition.
		 * @return string
		 */
		private function format_preview_image_url( $template ) {
			if ( empty( $template['preview_image'] ) ) {
				return '';
			}

			if ( 0 === strpos( $template['preview_image'], 'http' ) ) {
				return esc_url_raw( $template['preview_image'] );
			}

			if ( defined( 'GUTENA_FORMS_PLUGIN_URL' ) ) {
				return esc_url_raw( trailingslashit( GUTENA_FORMS_PLUGIN_URL ) . ltrim( $template['preview_image'], '/' ) );
			}

			return '';
		}
	}
endif;
