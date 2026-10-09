<?php
/**
 * Form creation from templates.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Templates_Model' ) ) :
	/**
	 * Creates gutena_forms posts from template definitions.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_Templates_Model {

		/**
		 * Singleton instance.
		 *
		 * @since 2.2.0
		 * @var Gutena_Forms_Templates_Model|null
		 */
		private static $instance = null;

		/**
		 * Template registry.
		 *
		 * @since 2.2.0
		 * @var Gutena_Forms_Templates_Registry
		 */
		private $registry;

		/**
		 * Get singleton instance.
		 *
		 * @since 2.2.0
		 * @return Gutena_Forms_Templates_Model
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @since 2.2.0
		 */
		private function __construct() {
			$this->registry = Gutena_Forms_Templates_Registry::get_instance();
		}

		/**
		 * Create a blank form with default fields.
		 *
		 * @since 2.2.0
		 * @param string $title Optional post title.
		 * @return array|WP_Error
		 */
		public function create_blank( $title = '' ) {
			$inner_blocks = Gutena_Forms_Templates_Block_Builder::with_form_footer(
				Gutena_Forms_Templates_Block_Builder::blank_fields()
			);

			return $this->create_from_blocks(
				__( 'Untitled Form', 'gutena-forms' ) === $title || empty( $title ) ? __( 'Untitled Form', 'gutena-forms' ) : $title,
				array( 'style' => array( 'spacing' => array( 'blockGap' => '2rem', 'padding' => array( 'top' => '2rem', 'bottom' => '5rem' ) ) ) ),
				$inner_blocks
			);
		}

		/**
		 * Create a form from a template ID.
		 *
		 * @since 2.2.0
		 * @param string $template_id Template ID.
		 * @return array|WP_Error
		 */
		public function create_from_template( $template_id ) {
			$template = $this->registry->get_by_id( $template_id );

			if ( empty( $template ) ) {
				return new WP_Error(
					'gutena_forms_template_not_found',
					__( 'Template not found.', 'gutena-forms' ),
					array( 'status' => 404 )
				);
			}

			if ( empty( $template['is_free'] ) && ! is_gutena_forms_pro() ) {
				return new WP_Error(
					'gutena_forms_template_pro_required',
					__( 'This template requires Gutena Forms Pro.', 'gutena-forms' ),
					array( 'status' => 403 )
				);
			}

			return $this->create_from_blocks(
				$template['title'],
				! empty( $template['form_attrs'] ) ? $template['form_attrs'] : array(),
				$template['innerBlocks']
			);
		}

		/**
		 * Create a gutena_forms post from block templates.
		 *
		 * @since 2.2.0
		 * @param string $title Post title.
		 * @param array  $form_attrs Form block attributes.
		 * @param array  $inner_templates Inner block templates.
		 * @return array|WP_Error
		 */
		private function create_from_blocks( $title, $form_attrs, $inner_templates ) {
			$form_id    = Gutena_Forms_Templates_Block_Builder::generate_form_id();
			$form_block = Gutena_Forms_Templates_Block_Builder::build_form_block( $form_attrs, $inner_templates, $form_id );
			$form_block = Gutena_Forms_Templates_Block_Serializer::prepare_for_save( $form_block );

			$serialized = serialize_block( $form_block );

			$post_id = wp_insert_post(
				array(
					'post_type'    => 'gutena_forms',
					'post_title'   => sanitize_text_field( $title ),
					'post_status'  => 'publish',
					'post_content' => wp_slash( $serialized ),
				),
				true
			);

			if ( is_wp_error( $post_id ) ) {
				return $post_id;
			}

			update_post_meta( $post_id, 'gutena_form_id', $form_id );

			return array(
				'post_id'  => $post_id,
				'form_id'  => $form_id,
				'edit_url' => get_edit_post_link( $post_id, 'raw' ),
			);
		}
	}
endif;
