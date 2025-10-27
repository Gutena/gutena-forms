<?php
/**
 * Gutena Forms Custom Post Type Class
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_CPT' ) ) :
	class Gutena_CPT {
		private static $instance;
		private static $updating_connected_posts = false;

		private $post_type = 'gutena_forms';

		private function __construct() {
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'save_post', array( $this, 'save_post' ), -1, 3 );
		}

		public function init() {
			register_post_type( $this->post_type, $this->post_type_args() );
		}

		private function post_type_args() {
			return array(
				'labels'       => $this->post_type_labels(),
				'public'       => true,
				'show_in_menu' => true,
				'supports'     => array( 'title', 'editor' ),
				'has_archive'  => false,
				'show_in_rest' => true,
			);
		}

		private function post_type_labels() {
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
		 * @param $post_id
		 * @param WP_Post $post
		 * @param $update
		 *
		 * @return void
		 */
		public function save_post( $post_id, $post, $update ) {
			// Prevent infinite recursion
			if ( self::$updating_connected_posts ) {
				return;
			}

			if ( $this->post_type === $post->post_type ) {

				$this->on_update_gutena_form_post_type( $post_id, $post, $update );

				remove_action( 'save_post', array( $this, 'save_post' ) );
				add_action( 'save_post', array( $this, 'save_post' ), -1, 3 );
				remove_action( 'save_post', array( Gutena_Forms::get_instance(), 'save_gutena_forms_schema' ) );
				add_action( 'save_post', array( Gutena_Forms::get_instance(), 'save_gutena_forms_schema' ), 10, 3 );
				return;
			}

			if ( empty( $post_id ) || empty( $post ) || ! function_exists( 'parse_blocks' ) || ! function_exists( 'wp_is_post_revision' ) || wp_is_post_revision( $post_id ) || ! function_exists( 'get_post_status' ) || 'trash' === get_post_status( $post_id ) || ! has_block( 'gutena/forms', $post ) ) {
				return;
			}



			$blocks = parse_blocks( $post->post_content );
			$blocks = $this->filter_gutena_blocks( $blocks );

			if ( empty( $blocks ) ) {
				return;
			}

			foreach ( $blocks as $block ) {
				$this->insert_or_update_form( $block, $post_id );
			}
		}

		private function filter_gutena_blocks( $blocks ) {

			$gutena_blocks = array();
			foreach ( $blocks as $k => $block ) {
				if ( isset( $block['blockName'] ) && 'gutena/forms' === $block['blockName'] ) {
					$gutena_blocks[] = $block;
				}
			}

			return $gutena_blocks;
		}

		private function insert_or_update_form( $block, $parent_post_id ) {
			$form_name = isset( $block['attrs']['formName'] ) ? sanitize_text_field( $block['attrs']['formName'] ) : 'Contact Form';
			$form_id   = $block['attrs']['formID'];
			// only one post shud be created for one form ID
			$post = get_posts(
				array(
					'post_type'      => $this->post_type,
					'meta_key'       => 'gutena_form_id',
					'meta_value'     => $form_id,
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
						'post_type' => $this->post_type,
						'post_title' => $form_name,
						'post_status' => 'publish',
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

	private function on_update_gutena_form_post_type( $post_id, $post ) {
		// Prevent recursion
		if ( self::$updating_connected_posts ) {
			return;
		}

		$content         = $post->post_content;
		$blocks          = parse_blocks( $content );
		$connected_posts = get_post_meta( $post_id, '_gutena_connected_posts', true );
		$form_id         = get_post_meta( $post_id, 'gutena_form_id', true );

		if ( ! is_array( $connected_posts ) ) {
			return;
		}

		// Filter out invalid post IDs
		$connected_posts = array_filter( $connected_posts, function( $id ) {
			return is_numeric( $id ) && null !== get_post( $id );
		});

		if ( empty( $connected_posts ) ) {
			return;
		}

		if ( isset( $blocks[0]['blockName'] ) && 'gutena/forms' === $blocks[0]['blockName'] ) {
			// Prevent infinite loop by removing actions before updating
			self::$updating_connected_posts = true;
			
			// Remove the save_post hooks to prevent recursion
			remove_action( 'save_post', array( $this, 'save_post' ), -1 );
			remove_action( 'save_post', array( Gutena_Forms::get_instance(), 'save_gutena_forms_schema' ), 10 );

			// we need to updated all connected posts blocks
			foreach ( $connected_posts as $connected_post_id ) {
				$connected_post = get_post( $connected_post_id );
				if ( empty( $connected_post ) ) {
					continue;
				}

				$connected_post_blocks = parse_blocks( $connected_post->post_content );
				$updated = false;

				foreach ( $connected_post_blocks as $index => $block ) {
					if ( isset( $block['blockName'] ) && 'gutena/forms' === $block['blockName'] ) {
						$block_form_id = $block['attrs']['formID'];
						if ( $form_id === $block_form_id ) {
							$connected_post_blocks[ $index ] = $blocks[0];
							$updated = true;
						}
					}
				}

				if ( $updated ) {
					// serialize blocks back to content
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

			// Re-add the actions after updates complete
			add_action( 'save_post', array( $this, 'save_post' ), -1, 3 );
			add_action( 'save_post', array( Gutena_Forms::get_instance(), 'save_gutena_forms_schema' ), 10, 3 );
			
			self::$updating_connected_posts = false;
		}
	}

		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}

	Gutena_CPT::get_instance();
endif;
