<?php
/**
 * Gutena Forms Custom Post Type Class
 *
 * @since 1.5.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_CPT' ) ) :
	/**
	 * Cutena Forms CPT
	 *
	 * @since 1.5.0
	 */
	class Gutena_Forms_CPT {
		/**
		 * Instance of the class
		 *
		 * @since 1.5.0
		 * @var Gutena_Forms_CPT $instance Instance of the class.
		 */
		private static $instance = null;

		/**
		 * Flag to prevent infinite loops when updating connected posts.
		 *
		 * @since 1.5.0
		 * @var bool $updating_connected_posts Flag to prevent infinite loops when updating connected posts.
		 */
		private static $updating_connected_posts;

		/**
		 * Post type name
		 *
		 * @since 1.5.0
		 * @var string $post_type Post type name.
		 */
		private $post_type = 'gutena_forms';

		/**
		 * Getting the instance of the class
		 *
		 * @since 1.5.0
		 * @return Gutena_Forms_CPT
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Gutena_Forms_CPT Construct
		 *
		 * @since 1.5.0
		 */
		private function __construct() {
			add_action( 'init', array( $this, 'register_post_type' ) );
			add_filter( 'block_categories_all', array( $this, 'move_gutena_to_top' ), 100 );
			add_action( 'wp_trash_post', array( $this, 'trashing_post' ) );
			add_action( 'save_post', array( $this, 'save_post' ), -1, 3 );
		}

		/**
		 * Register post type
		 *
		 * @since 1.5.0
		 */
		public function register_post_type() {
			register_post_type( $this->post_type, $this->post_type_args() );
		}

		/**
		 * Post type args
		 *
		 * @since 1.5.0
		 * @return array
		 */
		private function post_type_args() {
			return array(
				'labels'          => $this->get_labels(),
				'public'          => true,
				'show_in_menu'    => false,
				'supports'        => array( 'title', 'editor' ),
				'has_archive'     => false,
				'show_in_rest'    => true,
				'capability_type' => 'post',
				'template'        => array(
					array( 'gutena/forms', array() ),
				),
			);
		}

		/**
		 * Post type labels
		 *
		 * @since 1.5.0
		 * @return array
		 */
		private function get_labels() {
			return array(
				'not_found_in_trash' => __( 'No gutena forms found in Trash.', 'gutena-forms' ),
				'not_found'          => __( 'No gutena forms found.', 'gutena-forms' ),
				'parent_item_colon'  => __( 'Parent Gutena Forms:', 'gutena-forms' ),
				'add_new_item'       => __( 'Add New Form', 'gutena-forms' ),
				'search_items'       => __( 'Search Gutena Forms', 'gutena-forms' ),
				'view_item'          => __( 'View Gutena Form', 'gutena-forms' ),
				'edit_item'          => __( 'Edit Gutena Form', 'gutena-forms' ),
				'all_items'          => __( 'Forms', 'gutena-forms' ),
				'new_item'           => __( 'New Gutena Form', 'gutena-forms' ),
				'menu_name'          => __( 'Gutena Forms', 'gutena-forms' ),
				'name'               => __( 'Gutena Forms', 'gutena-forms' ),
				'singular_name'      => __( 'Gutena Form', 'gutena-forms' ),
				'name_admin_bar'     => __( 'Gutena Form', 'gutena-forms' ),
				'add_new'            => __( 'Add New', 'gutena-forms' ),
			);
		}

		/**
		 * Move gutena forms category to top.
		 *
		 * @since 1.5.0
		 * @param array $block_categories All block categories.
		 *
		 * @return array
		 */
		public function move_gutena_to_top( $block_categories ) {
			$new_categories = array();
			$indexes        = array();

			foreach ( $block_categories as $index => $category ) {
				if ( 'gutena' === $category['slug'] || 'gutena-pro' === $category['slug'] ) {
					$indexes[]        = $index;
					$new_categories[] = $category;
				}
			}

			if ( isset( $indexes[0] ) && isset( $block_categories[ $indexes[0] ] ) ) {
				unset( $block_categories[ $indexes[0] ] );
			}

			if ( isset( $indexes[1] ) && isset( $block_categories[ $indexes[1] ] ) ) {
				unset( $block_categories[ $indexes[1] ] );
			}

			return array_merge( $new_categories, $block_categories );
		}

		/**
		 * Trashing post action
		 *
		 * @since 1.5.0
		 * @param int|string $post_id Post ID.
		 */
		public function trashing_post( $post_id ) {
			$post = get_post( $post_id );
			if ( empty( $post ) || $this->post_type !== $post->post_type ) {
				return;
			}

			$form_id         = get_post_meta( $post_id, 'gutena_form_id', true );
			$connected_posts = get_post_meta( $post_id, '_gutena_connected_posts', true );

			if ( ! is_array( $connected_posts ) || empty( $connected_posts ) ) {
				return;
			}

			// Remove references to this form from connected posts and replace with a text notice. "The form has been removed".
			foreach ( $connected_posts as $connected_post_id ) {
				$connected_post = get_post( $connected_post_id );
				if ( empty( $connected_post ) ) {
					continue;
				}

				$connected_post_blocks = parse_blocks( $connected_post->post_content );
				$updated               = false;

				foreach ( $connected_post_blocks as $index => $block ) {
					if ( isset( $block['blockName'] ) && 'gutena/forms' === $block['blockName'] ) {
						$block_form_id = $block['attrs']['formID'];
						if ( $form_id === $block_form_id ) {
							// Replace block with a paragraph block indicating removal.

							$connected_post_blocks[ $index ] = array(
								'blockName'    => 'core/paragraph',
								'attrs'        => array(),
								'innerContent' => array(
									'<p><em>' . esc_html__( 'This form has been deleted.', 'gutena-forms' ) . '</em></p>',
								),
								'innerHTML'    => '<p><em>' . esc_html__( 'This form has been deleted.', 'gutena-forms' ) . '</em></p>',
								'innerBlocks'  => array(),
							);

							$updated = true;
						}
					}
				}

				if ( $updated ) {
					$new_content = serialize_blocks( $connected_post_blocks );
					wp_update_post(
						array(
							'ID'           => $connected_post_id,
							'post_content' => $new_content,
						),
						false,
						false
					);
				}
			}
		}

		/**
		 * Save post action
		 *
		 * @since 1.5.0
		 * @param int|string $post_id Post ID.
		 * @param WP_Post    $post Post object.
		 * @param bool       $update Whether this is an existing post being updated or not.
		 */
		public function save_post( $post_id, $post, $update ) {
			if ( empty( $post_id ) || empty( $post ) || ! function_exists( 'parse_blocks' ) || ! function_exists( 'wp_is_post_revision' ) || wp_is_post_revision( $post_id ) || ! function_exists( 'get_post_status' ) || 'trash' === get_post_status( $post_id ) || ! has_block( 'gutena/forms', $post ) ) {
				return;
			}

			if ( str_contains( $post->post_content, 'gutena/existing-forms' ) ) {
				remove_action( 'save_post', array( $this, 'save_post' ) );
				add_action( 'save_post', array( $this, 'save_post' ), -1, 3 );
				remove_action( 'save_post', array( Gutena_Forms::get_instance(), 'save_gutena_forms_schema' ) );
				add_action( 'save_post', array( Gutena_Forms::get_instance(), 'save_gutena_forms_schema' ), 10, 3 );
				return;
			}

			if ( self::$updating_connected_posts ) {
				return;
			}

			if ( $this->post_type === $post->post_type ) {
				$this->updating_gutena_post_type( $post_id, $post, $update );

				remove_action( 'save_post', array( $this, 'save_post' ) );
				add_action( 'save_post', array( $this, 'save_post' ), -1, 3 );
				remove_action( 'save_post', array( Gutena_Forms::get_instance(), 'save_gutena_forms_schema' ) );
				add_action( 'save_post', array( Gutena_Forms::get_instance(), 'save_gutena_forms_schema' ), 10, 3 );
				return;
			}

			$blocks = parse_blocks( $post->post_content );
			$blocks = array_filter(
				$blocks,
				function ( $block ) {
					return isset( $block['blockName'] ) && 'gutena/forms' === $block['blockName'];
				}
			);

			if ( ! empty( $blocks ) ) {
				foreach ( $blocks as $block ) {
					$this->insert_or_update_form( $block, $post_id );
				}
			}
		}

		/**
		 * Updating gutena post type
		 *
		 * @since 1.5.0
		 * @param int|string $post_id Post ID.
		 * @param WP_Post    $post Post object.
		 */
		public function updating_gutena_post_type( $post_id, $post ) {
			// Prevent recursion.
			if ( self::$updating_connected_posts ) {
				return;
			}

			$content         = $post->post_content;
			$blocks          = parse_blocks( $content );
			$connected_posts = get_post_meta( $post_id, '_gutena_connected_posts', true );
			$form_id         = get_post_meta( $post_id, 'gutena_form_id', true );

			// Find the form block and extract form name to sync with post title.
			$form_block = null;
			$form_name  = '';

			foreach ( $blocks as $block ) {
				if ( isset( $block['blockName'] ) && 'gutena/forms' === $block['blockName'] ) {
					$form_block = $block;
					// Extract form name from block attributes, with fallback to "Contact Form".
					// This matches the behavior in insert_or_update_form method (line 290).
					$form_name = isset( $block['attrs']['formName'] ) && ! empty( $block['attrs']['formName'] )
						? sanitize_text_field( $block['attrs']['formName'] )
						: 'Contact Form';

					// Extract formID from block and set gutena_form_id meta if it doesn't exist.
					// This ensures the meta is set when a gutena_forms post is created directly.
					if ( $form_block && isset( $form_block['attrs']['formID'] ) && ! empty( $form_block['attrs']['formID'] ) ) {
						$block_form_id = $form_block['attrs']['formID'];
						// Only set meta if it doesn't already exist.
						if ( empty( $form_id ) ) {
							update_post_meta( $post_id, 'gutena_form_id', $block_form_id );
							$form_id = $block_form_id;
						}
					}

					break;
				}
			}

			// Set gutena_form_id meta if it doesn't exist and formID is available in block.
			if ( $form_block && isset( $form_block['attrs']['formID'] ) && ! empty( $form_block['attrs']['formID'] ) ) {
				$block_form_id = $form_block['attrs']['formID'];
				if ( empty( $form_id ) ) {
					update_post_meta( $post_id, 'gutena_form_id', $block_form_id );
					$form_id = $block_form_id;
				}
			}

			// Post title must always be form name - update it every time when form block exists.
			if ( $form_block && ! empty( $form_name ) ) {
				// Prevent infinite loop by removing actions before updating.
				self::$updating_connected_posts = true;

				// Remove the save_post hooks to prevent recursion.
				remove_action( 'save_post', array( $this, 'save_post' ), -1 );
				remove_action( 'save_post', array( Gutena_Forms::get_instance(), 'save_gutena_forms_schema' ), 10 );

				$new_post_content = '';

				$blocks = parse_blocks( $post->post_content );

				foreach ( $blocks as $k => $block ) {
					if ( isset( $block['blockName'] ) && 'gutena/forms' === $block['blockName'] ) {
						$blocks[ $k ]['attrs']['formName'] = $post->post_title;
					}
				}

				foreach ( $blocks as $block ) {
					$new_post_content .= serialize_block( $block );
				}

				wp_update_post(
					array(
						'ID'           => $post_id,
						'post_content' => $new_post_content,
					),
					false,
					false
				);

				// Re-add the actions after update.
				add_action( 'save_post', array( $this, 'save_post' ), -1, 3 );
				add_action( 'save_post', array( Gutena_Forms::get_instance(), 'save_gutena_forms_schema' ), 10, 3 );

				self::$updating_connected_posts = false;
			}

			if ( ! is_array( $connected_posts ) ) {
				return;
			}

			// Filter out invalid post IDs.
			$connected_posts = array_filter(
				$connected_posts,
				function ( $id ) {
					return is_numeric( $id ) && null !== get_post( $id );
				}
			);

			if ( empty( $connected_posts ) ) {
				return;
			}

			if ( $form_block && isset( $form_block['blockName'] ) && 'gutena/forms' === $form_block['blockName'] ) {
				// Prevent infinite loop by removing actions before updating.
				self::$updating_connected_posts = true;

				// Remove the save_post hooks to prevent recursion.
				remove_action( 'save_post', array( $this, 'save_post' ), -1 );
				remove_action( 'save_post', array( Gutena_Forms::get_instance(), 'save_gutena_forms_schema' ), 10 );

				// we need to updated all connected posts blocks.
				foreach ( $connected_posts as $connected_post_id ) {
					$connected_post = get_post( $connected_post_id );
					if ( empty( $connected_post ) ) {
						continue;
					}

					$connected_post_blocks = parse_blocks( $connected_post->post_content );
					$updated               = false;

					foreach ( $connected_post_blocks as $index => $block ) {
						if ( isset( $block['blockName'] ) && 'gutena/forms' === $block['blockName'] ) {
							$block_form_id = $block['attrs']['formID'];
							if ( $form_id === $block_form_id ) {
								$connected_post_blocks[ $index ] = $form_block;
								$updated                         = true;
							}
						}
					}

					if ( $updated ) {
						// serialize blocks back to content.
						$new_content = '';
						foreach ( $connected_post_blocks as $block ) {
							$new_content .= serialize_block( $block );
						}

						wp_update_post(
							array(
								'ID'           => $connected_post_id,
								'post_content' => $new_content,
							),
							false,
							false
						);
					}
				}

				// Re-add the actions after updates complete.
				add_action( 'save_post', array( $this, 'save_post' ), -1, 3 );
				add_action( 'save_post', array( Gutena_Forms::get_instance(), 'save_gutena_forms_schema' ), 10, 3 );

				self::$updating_connected_posts = false;
			}
		}

		/**
		 * Insert or update form
		 *
		 * @since 1.5.0
		 * @param array      $block Block data.
		 * @param int|string $parent_post_id Post ID.
		 */
		public function insert_or_update_form( $block, $parent_post_id ) {
			$form_name = $block['attrs']['formName'] ?? 'Contact Form';
			$form_id   = $block['attrs']['formID'];

			$post = get_posts(
				array(
					'post_type'      => $this->post_type,
					'meta_key'       => 'gutena_form_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_value'     => $form_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					'posts_per_page' => 1,
					'post_status'    => 'any',
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $post ) ) {
				$post_id = $post[0];
				wp_update_post(
					array(
						'ID'           => $post_id,
						'post_title'   => $form_name,
						'post_content' => serialize_block( $block ),
					),
					false,
					false
				);
			} else {
				$post_id = wp_insert_post(
					array(
						'post_type'    => $this->post_type,
						'post_title'   => $form_name,
						'post_status'  => 'publish',
						'post_content' => serialize_block( $block ),
					),
					false,
					false
				);

				update_post_meta( $post_id, 'gutena_form_id', $form_id );
				update_post_meta( $post_id, '_gutena_connected_posts', array( $parent_post_id ) );
			}

			$connected_posts = get_post_meta( $post_id, '_gutena_connected_posts', true );
			if ( ! is_array( $connected_posts ) ) {
				$connected_posts = array();
			}

			if ( ! in_array( $parent_post_id, $connected_posts, true ) ) {
				$connected_posts[] = $parent_post_id;
				update_post_meta( $post_id, '_gutena_connected_posts', $connected_posts );
			}
		}
	}

	Gutena_Forms_CPT::get_instance();
endif;
