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
		 * Render Existing Form Block
		 *
		 * @since 1.2.1
		 * @param array $attributes Block attributes.
		 *
		 * @return string
		 */
		public function render_existing_form( $attributes ) {
			$form_id = isset( $attributes['formId'] ) ? intval( $attributes['formId'] ) : 0;
			ob_start();

			if ( $form_id ) {
				$form_post = get_post( $form_id );
				if ( $form_post && 'gutena_form' === $form_post->post_type ) {
					// Render the form content
					echo apply_filters( 'the_content', $form_post->post_content );
				} else {
					echo '<p class="gutena-forms-error">' . esc_html__( 'Selected form not found.', 'gutena-forms' ) . '</p>';
				}
			} else {
				echo '<p class="gutena-forms-error">' . esc_html__( 'No form selected.', 'gutena-forms' ) . '</p>';
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
			// Prevent infinite loops by checking if we're already processing this post
			static $processing_posts = array();
			
			if ( isset( $processing_posts[ $post_id ] ) ) {
				return;
			}

			// Mark this post as being processed
			$processing_posts[ $post_id ] = true;

			if ( 'gutena_form' === $post->post_type ) {
				$this->if_change_in_form_than_update_source_posts( $post_id, $post );
				
				// Remove from processing list
				unset( $processing_posts[ $post_id ] );
				return;
			}

			if ( empty( $post_id ) || empty( $post ) || ! function_exists( 'parse_blocks' ) || ! function_exists( 'wp_is_post_revision' ) || wp_is_post_revision( $post_id ) || ! function_exists( 'get_post_status' ) || 'trash' === get_post_status( $post_id ) || ! has_block( 'gutena/forms', $post ) ) {
				// Remove from processing list
				unset( $processing_posts[ $post_id ] );
				return;
			}

			$blocks = parse_blocks( $post->post_content );
			$blocks = $this->get_blocks_if_exists( $blocks );

			foreach ( $blocks as $block ) {
				$this->insert_or_update_gutena_form_block( $block, $post_id );
			}

			// Remove from processing list
			unset( $processing_posts[ $post_id ] );
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
				// Skip existing-forms blocks - they shouldn't be saved as form content
				if ( isset( $block['blockName'] ) && $block['blockName'] === 'gutena/existing-forms' ) {
					continue;
				}

				if ( isset( $block['blockName'] ) && 'gutena/forms' === $block['blockName'] ) {
					// Only add gutena/forms blocks that don't contain existing-forms blocks
					if ( ! $this->contains_existing_forms_block( $block ) ) {
						$gutena_blocks[] = $block;
					}
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
		 * Check if block contains existing-forms blocks recursively
		 *
		 * @since 1.2.1
		 * @param array $block Block data.
		 * @return bool
		 */
		private function contains_existing_forms_block( $block ) {
			// Check if this block itself is an existing-forms block
			if ( isset( $block['blockName'] ) && $block['blockName'] === 'gutena/existing-forms' ) {
				return true;
			}

			// Check inner blocks recursively
			if ( ! empty( $block['innerBlocks'] ) ) {
				foreach ( $block['innerBlocks'] as $inner_block ) {
					if ( $this->contains_existing_forms_block( $inner_block ) ) {
						return true;
					}
				}
			}

			return false;
		}

		/**
		 * Insert or Update Gutena Form Block
		 *
		 * @since 1.2.1
		 * @param array $block Block data.
		 * @param int   $source_post_id Source Post ID.
		 */
		private function insert_or_update_gutena_form_block( $block, int $source_post_id ) {
			// Skip existing-forms blocks - they shouldn't be saved as form content
			if ( isset( $block['blockName'] ) && $block['blockName'] === 'gutena/existing-forms' ) {
				return;
			}
			
			$block_html = serialize_block( $block );

			if ( isset( $block['attrs'] ) && is_array( $block['attrs'] ) ) {
				$form_signature = md5( wp_json_encode( $block['attrs'] ) );
			} else {
				$form_signature = md5( $block_html );
			}

			// Get form name from attributes
			$form_name = isset( $block['attrs']['formName'] ) ? $block['attrs']['formName'] : '';
			if ( empty( $form_name ) ) {
				$form_name = __( 'Contact Form', 'gutena-forms' );
			}

			// Check if this form already exists by formID
			$existing_block = null;
			if ( isset( $block['attrs']['formID'] ) && ! empty( $block['attrs']['formID'] ) ) {
				$existing_block = get_posts(
					array(
						'post_type'   => 'gutena_form',
						'meta_key'    => '_gutena_forms_form_id',
						'meta_value'  => $block['attrs']['formID'],
						'numberposts' => 1,
					)
				);
			}

			// If not found by formID, try to find by hash
			if ( empty( $existing_block ) ) {
				$existing_block = get_posts(
					array(
						'post_type'   => 'gutena_form',
						'meta_key'    => '_gutena_forms_hash',
						'meta_value'  => $form_signature,
						'numberposts' => 1,
					)
				);
			}

			// Fallback to old-single-source lookup for backward compatibility
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

			$post_data = array(
				'post_title'   => sprintf( 'Gutena Form — %s', $form_name ),
				'post_content' => $block_html,
				'post_status'  => 'publish',
				'post_type'    => 'gutena_form',
			);

			if ( ! empty( $existing_block ) ) {
				// Update existing reusable form
				$post_data['ID'] = $existing_block[0]->ID;
				
				// Temporarily remove our save_post hook to prevent infinite loop
				remove_action( 'save_post', array( $this, 'save_blocks_to_custom_post_type' ), -1 );
				remove_action( 'save_post', array( Gutena_Forms::get_instance(), 'save_gutena_forms_schema' ), 10 );
				
				wp_update_post( $post_data );
				$block_id = $existing_block[0]->ID;
				
				// Sync changes across all instances
				$this->sync_form_changes( $block_id, $post_data['post_content'] );
				
				// Re-add our save_post hook
				add_action( 'save_post', array( $this, 'save_blocks_to_custom_post_type' ), -1, 2 );
				add_action( 'save_post', array( Gutena_Forms::get_instance(), 'save_gutena_forms_schema' ), 10, 3 );
			} else {
				// Insert new reusable form
				$block_id = wp_insert_post( $post_data, false, false );
				if ( $block_id ) {
					add_post_meta( $block_id, '_gutena_forms_hash', $form_signature, true );
					
					// Store form ID if available
					if ( isset( $block['attrs']['formID'] ) && ! empty( $block['attrs']['formID'] ) ) {
						add_post_meta( $block_id, '_gutena_forms_form_id', $block['attrs']['formID'], true );
					}
				}
			}

			if ( $block_id ) {
				// Maintain list of source posts that reference this reusable form
				$sources = get_post_meta( $block_id, '_gutena_forms_source_posts', true );
				if ( ! is_array( $sources ) ) {
					$sources = array();
				}
				if ( ! in_array( $source_post_id, $sources, true ) ) {
					$sources[] = (int) $source_post_id;
					update_post_meta( $block_id, '_gutena_forms_source_posts', $sources );
				}

				// Keep quick-reference on the source post to the reusable block
				update_post_meta( $source_post_id, '_gutena_forms_reusable_block_id', $block_id );
			}
		}

		/**
		 * Sync form changes across all instances
		 *
		 * @since 1.2.1
		 * @param int    $form_id Form ID in custom post type.
		 * @param string $new_content New form content.
		 */
		public function sync_form_changes( $form_id, $new_content ) {
			// Prevent infinite loops by checking if we're already syncing
			static $syncing_forms = array();
			
			if ( isset( $syncing_forms[ $form_id ] ) ) {
				return;
			}
			
			$syncing_forms[ $form_id ] = true;

			try {
				// Get all posts that use this form
				$source_posts = get_post_meta( $form_id, '_gutena_forms_source_posts', true );
				if ( ! is_array( $source_posts ) ) {
					$source_posts = array();
				}

				// Also find posts that use this form via existing-forms blocks
				$form_id_meta = get_post_meta( $form_id, '_gutena_forms_form_id', true );
				$existing_forms_posts = array();
				
				if ( ! empty( $form_id_meta ) ) {
					$existing_forms_posts = get_posts( array(
						'post_type' => 'any',
						'post_status' => 'publish',
						'numberposts' => 50, // Limit to prevent memory issues
						'meta_query' => array(
							array(
								'key' => '_gutena_forms_form_id',
								'value' => $form_id_meta,
								'compare' => '='
							)
						)
					) );
				}

				// Merge all posts that need updating
				$all_posts = array_unique( array_merge( $source_posts, wp_list_pluck( $existing_forms_posts, 'ID' ) ) );

				// Limit the number of posts to update to prevent timeouts
				$all_posts = array_slice( $all_posts, 0, 20 );

				foreach ( $all_posts as $post_id ) {
					$this->update_form_in_post( $post_id, $form_id, $new_content );
				}
			} catch ( Exception $e ) {
				// Log error but don't break the save process
				error_log( 'Gutena Forms Sync Error: ' . $e->getMessage() );
			} finally {
				// Always remove from syncing list
				unset( $syncing_forms[ $form_id ] );
			}
		}

		/**
		 * Update form content in a specific post
		 *
		 * @since 1.2.1
		 * @param int    $post_id Post ID.
		 * @param int    $form_id Form ID in custom post type.
		 * @param string $new_content New form content.
		 */
		private function update_form_in_post( $post_id, $form_id, $new_content ) {
			// Skip if we're already processing this post
			static $processing_posts = array();
			
			if ( isset( $processing_posts[ $post_id ] ) ) {
				return;
			}
			
			$processing_posts[ $post_id ] = true;

			try {
				$post = get_post( $post_id );
				if ( ! $post ) {
					return;
				}

				$blocks = parse_blocks( $post->post_content );
				$updated = false;

				foreach ( $blocks as &$block ) {
					if ( $this->update_block_content( $block, $form_id, $new_content ) ) {
						$updated = true;
					}
				}

				if ( $updated ) {
					$serialized_content = serialize_blocks( $blocks );
					
					// Temporarily remove hooks to prevent infinite loops
					remove_action( 'save_post', array( $this, 'save_blocks_to_custom_post_type' ), -1 );
					remove_action( 'save_post', array( Gutena_Forms::get_instance(), 'save_gutena_forms_schema' ), 10 );
					
					$result = wp_update_post( array(
						'ID' => $post_id,
						'post_content' => $serialized_content
					) );
					
					// Re-add hooks
					add_action( 'save_post', array( $this, 'save_blocks_to_custom_post_type' ), -1, 2 );
					add_action( 'save_post', array( Gutena_Forms::get_instance(), 'save_gutena_forms_schema' ), 10, 3 );
					
					if ( is_wp_error( $result ) ) {
						error_log( 'Gutena Forms Update Error for post ' . $post_id . ': ' . $result->get_error_message() );
					}
				}
			} catch ( Exception $e ) {
				error_log( 'Gutena Forms Update Exception for post ' . $post_id . ': ' . $e->getMessage() );
			} finally {
				// Always remove from processing list
				unset( $processing_posts[ $post_id ] );
			}
		}

		/**
		 * Update block content recursively
		 *
		 * @since 1.2.1
		 * @param array  $block Block data.
		 * @param int    $form_id Form ID in custom post type.
		 * @param string $new_content New form content.
		 * @return bool Whether the block was updated.
		 */
		private function update_block_content( &$block, $form_id, $new_content ) {
			$updated = false;

			// Check if this is a gutena/forms block that matches our form
			if ( isset( $block['blockName'] ) && $block['blockName'] === 'gutena/forms' ) {
				$block_form_id = get_post_meta( $form_id, '_gutena_forms_form_id', true );
				if ( isset( $block['attrs']['formID'] ) && $block['attrs']['formID'] === $block_form_id ) {
					// Update the inner blocks with new content
					$parsed_new_blocks = parse_blocks( $new_content );
					if ( ! empty( $parsed_new_blocks ) ) {
						$form_block = $parsed_new_blocks[0];
						if ( isset( $form_block['innerBlocks'] ) ) {
							$block['innerBlocks'] = $form_block['innerBlocks'];
							$updated = true;
						}
					}
				}
			}

			// Check if this is an existing-forms block that references our form
			if ( isset( $block['blockName'] ) && $block['blockName'] === 'gutena/existing-forms' ) {
				$block_form_id = get_post_meta( $form_id, '_gutena_forms_form_id', true );
				if ( isset( $block['attrs']['formId'] ) && $block['attrs']['formId'] == $form_id ) {
					// Update the inner blocks with new content
					$parsed_new_blocks = parse_blocks( $new_content );
					if ( ! empty( $parsed_new_blocks ) ) {
						$form_block = $parsed_new_blocks[0];
						if ( isset( $form_block['innerBlocks'] ) ) {
							$block['innerBlocks'] = $form_block['innerBlocks'];
							$updated = true;
						}
					}
				}
			}

			// Recursively check inner blocks
			if ( ! empty( $block['innerBlocks'] ) ) {
				foreach ( $block['innerBlocks'] as &$inner_block ) {
					if ( $this->update_block_content( $inner_block, $form_id, $new_content ) ) {
						$updated = true;
					}
				}
			}

			return $updated;
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
