<?php
/**
 * Class Gutena Helper
 *
 * @since 1.2.1
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Helper' ) ) :
	/**
	 * Gutena_Helper Class
	 *
	 * @since 1.2.1
	 */
	class Gutena_Helper {
		/**
		 * Get the single instance of the class
		 *
		 * @since 1.2.1
		 * @var Gutena_Helper $instance The single instance of the class.
		 */
		private static $instance;

		/**
		 * Constructor
		 *
		 * @since 1.2.1
		 */
		private function __construct() {
			add_action( 'init', array( $this, 'register_post_type' ) );
			add_action( 'save_post', array( $this, 'save_blocks_to_custom_post_type' ), -1, 2 );
		}

		/**
		 * Register Post Type
		 *
		 * @since 1.2.1
		 */
		public function register_post_type() {
			register_post_type(
				'gutena_form',
				$this->args()
			);
		}

		/**
		 * New Block
		 *
		 * @since 1.2.1
		 * @param array $x Block data.
		 *
		 * @return string
		 */
		public function new_block( $x ) {
			$form_id = isset( $x['formId'] ) ? intval( $x['formId'] ) : 0;
			ob_start();

			if ( $form_id ) {
				$form_post = get_post( $form_id );
				if ( $form_post && 'gutena_form' === $form_post->post_type ) {
					printf( '<!-- for debug display post title and id %s, %s -->', esc_html( get_the_title( $form_post->ID ) ), esc_html( $form_post->ID ) );
					echo apply_filters( 'the_content', $form_post->post_content );
				} else {
					echo '<p>' . esc_html__( 'Selected form not found.', 'gutena-forms' ) . '</p>';
				}
			} else {
				echo '<p>' . esc_html__( 'No form selected.', 'gutena-forms' ) . '</p>';
			}

			return ob_get_clean();
		}

		/**
		 * Save Blocks to Custom Post Type
		 *
		 * @since 1.2.1
		 * @param int     $post_id Post ID.
		 * @param WP_Post $post Post Object.
		 */
		public function save_blocks_to_custom_post_type( $post_id, $post ) {
			if ( 'gutena_form' === $post->post_type ) {


				$this->if_change_in_form_than_update_source_posts( $post_id, $post );

				remove_action( 'save_post', array( $this, 'save_blocks_to_custom_post_type' ), -1 );
				add_action( 'save_post', array( $this, 'save_blocks_to_custom_post_type' ), -1, 2 );

				remove_action( 'save_post', array( Gutena_Forms::get_instance(), 'save_gutena_forms_schema' ), 10 );
				add_action( 'save_post', array( Gutena_Forms::get_instance(), 'save_gutena_forms_schema' ), 10, 3 );
				return;
			}

			if ( empty( $post_id ) || empty( $post ) || ! function_exists( 'parse_blocks' ) || ! function_exists( 'wp_is_post_revision' ) || wp_is_post_revision( $post_id ) || ! function_exists( 'get_post_status' ) || 'trash' === get_post_status( $post_id ) || ! has_block( 'gutena/forms', $post ) ) {
				return;
			}

			$blocks = parse_blocks( $post->post_content );
			$blocks = $this->get_blocks_if_exists( $blocks );

			foreach ( $blocks as $block ) {
				$this->insert_or_update_gutena_form_block( $block, $post_id );
			}
		}

		/**
		 * If Change In Form Than Update Source Posts
		 *
		 * @since 1.2.1
		 * @param int|string $id Post ID.
		 * @param WP_Post    $post Post Object.
		 */
		private function if_change_in_form_than_update_source_posts( $id, $post ) {
			$source_posts = get_post_meta( $id, '_gutena_forms_source_posts', true );
			if ( ! is_array( $source_posts ) || empty( $source_posts ) ) { return; }

			$current_block = parse_blocks( $post->post_content );
			$current_block = $current_block[0];

			foreach ( $source_posts as $source_post ) {
				$post = get_post( $source_post );
				// search for gutena/forms block and replace it with <!-- wp:gutenaforms/existing-forms wp_json_encode( array( 'form_id' => $id ) ) -->
				if ( has_block( 'gutena/forms', $post ) ) {
					$blocks         = parse_blocks( $post->post_content );
					$updated_blocks = array();

					foreach ( $blocks as $block ) {
						if ( isset( $block['blockName'] ) && 'gutena/forms' === $block['blockName'] ) {
							$updated_blocks[] = $current_block;
						} else {
							$updated_blocks[] = $block;
						}
					}

					// serialize blocks back to post content
					$new_content = '';
					foreach ( $updated_blocks as $block ) {
						$new_content .= serialize_block( $block );
					}

					// update source post content
					wp_update_post(
						array(
							'ID'           => $source_post,
							'post_content' => $new_content,
						)
					);
				}
			}
		}

		/**
		 * Get Blocks If Exists
		 *
		 * @since 1.2.1
		 * @param array $blocks array of blocks.
		 *
		 * @return array|false
		 */
		private function get_blocks_if_exists( $blocks ) {
			$gutena_blocks = array();

			foreach ( $blocks as $block ) {
				if ( isset( $block['blockName'] ) && 'gutena/forms' === $block['blockName'] ) {
					$gutena_blocks[] = $block;
				}

				if ( ! empty( $block['innerBlocks'] ) ) {
					$inner_gutena_blocks = $this->get_blocks_if_exists( $block['innerBlocks'] );
					if ( $inner_gutena_blocks ) {
						$gutena_blocks = array_merge( $gutena_blocks, $inner_gutena_blocks );
					}
				}
			}

			return empty( $gutena_blocks ) ? false : $gutena_blocks;
		}

		/**
		 * Insert or Update Gutena Form Block
		 *
		 * @since 1.2.1
		 * @param array $block Block data.
		 * @param int   $source_post_id Source Post ID.
		 */
		private function insert_or_update_gutena_form_block( $block, int $source_post_id ) {
			$block_html     = serialize_block( $block );

			if ( isset( $block['attrs'] ) && is_array( $block['attrs'] ) ) {
				$form_signature = md5( wp_json_encode( $block['attrs'] ) );
			} else {
				$form_signature = md5( $block_html );
			}

			// try to find existing reusable form by hash
			$existing_block = get_posts(
				array(
					'post_type'   => 'gutena_form',
					'meta_key'    => '_gutena_forms_hash',
					'meta_value'  => $form_signature,
					'numberposts' => 1,
				)
			);

			// fallback to old-single-source lookup for backward compatibility
			if ( empty( $existing_block ) ) {
				$existing_block = get_posts(
					array(
						'post_type'   => 'gutena_form',
						'meta_key'    => '_gutena_forms_source_post',
						'meta_value'  => $source_post_id,
						'numberposts' => 1,
					)
				);
			}

			// form name or title
			$form_name = isset( $block['attrs']['formName'] ) ? $block['attrs']['formName'] : '';
			if ( empty( $form_name ) ) {
				$form_name = __( 'Contact Form', 'gutena-forms' );
			}

			$post_data = array(
				'post_title'   => sprintf( 'Gutena Form — %s', $form_name ),
				'post_content' => $block_html,
				'post_status'  => 'publish',
				'post_type'    => 'gutena_form',
			);

			if ( ! empty( $existing_block ) ) {
				// update existing reusable form
				$post_data['ID'] = $existing_block[0]->ID;
				wp_update_post( $post_data );
				$block_id = $existing_block[0]->ID;
			} else {
				// insert new reusable form and store its hash
				$block_id = wp_insert_post( $post_data, false, false );
				if ( $block_id ) {
					add_post_meta( $block_id, '_gutena_forms_hash', $form_signature, true );
				}
			}

			if ( $block_id ) {
				// maintain list of source posts that reference this reusable form
				$sources = get_post_meta( $block_id, '_gutena_forms_source_posts', true );
				if ( ! is_array( $sources ) ) {
					$sources = array();
				}
				if ( ! in_array( $source_post_id, $sources, true ) ) {
					$sources[] = (int) $source_post_id;
					update_post_meta( $block_id, '_gutena_forms_source_posts', $sources );
				}

				// keep quick-reference on the source post to the reusable block
				update_post_meta( $source_post_id, '_gutena_forms_reusable_block_id', $block_id );
			}
		}

		/**
		 * Post Type Args
		 *
		 * @since 1.2.1
		 * @return array
		 */
		private function args() {
			return array(
				'labels'       => $this->labels(),
				'public'       => true,
				'show_in_menu' => true,
				'supports'     => array( 'title', 'editor' ),
				'has_archive'  => false,
				'show_in_rest' => true,
			);
		}

		/**
		 * Post Type Labels
		 *
		 * @since 1.2.1
		 * @return array
		 */
		private function labels() {
			return array(
				'not_found_in_trash' => __( 'No gutena forms found in Trash.', 'gutena-forms' ),
				'not_found'          => __( 'No gutena forms found.', 'gutena-forms' ),
				'parent_item_colon'  => __( 'Parent Gutena Forms:','gutena-forms' ),
				'add_new_item'       => __( 'Add New Gutena Form', 'gutena-forms' ),
				'search_items'       => __( 'Search Gutena Forms', 'gutena-forms' ),
				'view_item'          => __( 'View Gutena Form', 'gutena-forms' ),
				'edit_item'          => __( 'Edit Gutena Form', 'gutena-forms' ),
				'all_items'          => __( 'All Gutena Forms', 'gutena-forms' ),
				'new_item'           => __( 'New Gutena Form','gutena-forms' ),
				'menu_name'          => __( 'Gutena Forms', 'gutena-forms' ),
				'name'               => __( 'Gutena Forms', 'gutena-forms' ),
				'singular_name'      => __( 'Gutena Form', 'gutena-forms' ),
				'name_admin_bar'     => __( 'Gutena Form', 'gutena-forms' ),
				'add_new'            => __( 'Add New', 'gutena-forms' ),
			);
		}

		/**
		 * Get Instance
		 *
		 * @since 1.2.1
		 * @return Gutena_Helper
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}

	Gutena_Helper::get_instance();
endif;
