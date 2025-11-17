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

		/**
		 * Whether there are any existing Gutena Forms posts.
		 * Computed during init() to avoid repeated queries.
		 *
		 * @var bool
		 */
		private $has_forms = false;

		private function __construct() {
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'save_post', array( $this, 'save_post' ), -1, 3 );
			add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
			add_action( 'admin_head', array( $this, 'admin_head' ) );
			add_action( 'admin_footer', array( $this, 'admin_footer' ) );
		}

		public function init() {
			register_post_type( $this->post_type, $this->post_type_args() );

			// Determine if there are any Gutena Forms (cheap count). Compute once per request.

				// Fallback to a lightweight query if wp_count_posts isn't available for some reason
				$posts = get_posts( array(
					'post_type'      => $this->post_type,
					'posts_per_page' => 1,
					'post_status'    => 'any',
					'fields'         => 'ids',
				) );
				$this->has_forms = ! empty( $posts );


			register_block_type(
				GUTENA_FORMS_DIR_PATH . 'build/existing-forms',
				array(
					'render_callback' => array( $this, 'render_existing_form' ),
					'attributes'      => array(
						'formId' => array(
							'type'    => 'number',
							'default' => 0
						)
					),
				)
			);
		}

		public function render_existing_form( $attributes ) {
			$form_id = isset( $attributes['formId'] ) ? intval( $attributes['formId'] ) : 0;

			if ( empty( $form_id ) ) {
				return '';
			}

			$form = get_post( $form_id );

			if ( ! $form || $form->post_type !== $this->post_type ) {
				return '';
			}

			return do_blocks( $form->post_content );
		}

		/**
		 * Register REST API routes
		 */
		public function register_rest_routes() {
			register_rest_route(
				'gutena-forms/v1',
				'/forms',
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_forms' ),
					'permission_callback' => array( $this, 'rest_permissions_check' ),
				)
			);

			register_rest_route(
				'gutena-forms/v1',
				'/forms/(?P<id>\d+)',
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_form' ),
					'permission_callback' => array( $this, 'rest_permissions_check' ),
				)
			);

			register_rest_route(
				'gutena-forms/v1',
				'/forms/(?P<id>\d+)/update',
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'update_form' ),
					'permission_callback' => array( $this, 'rest_permissions_check' ),
				)
			);
		}

		/**
		 * Check permissions for REST API requests
		 */
		public function rest_permissions_check() {
			return current_user_can( 'edit_posts' );
		}

		/**
		 * Get all forms
		 */
		public function get_forms() {
			$forms = get_posts(
				array(
					'post_type'      => $this->post_type,
					'posts_per_page' => -1,
					'post_status'    => 'any',
				)
			);

			return array_map( function( $form ) {
				return array(
					'id'    => $form->ID,
					'title' => $form->post_title,
				);
			}, $forms );
		}

		/**
		 * Get single form
		 */
		public function get_form( $request ) {
			$form_id = $request['id'];
			$form    = get_post( $form_id );

			if ( ! $form || $form->post_type !== $this->post_type ) {
				return new WP_Error( 'form_not_found', __( 'Form not found', 'gutena-forms' ), array( 'status' => 404 ) );
			}

			// Parse the blocks to get form block with attributes
			$blocks = parse_blocks( $form->post_content );
			$form_block = null;

			foreach ( $blocks as $block ) {
				if ( isset( $block['blockName'] ) && 'gutena/forms' === $block['blockName'] ) {
					$form_block = $block;
					break;
				}
			}

			return array(
				'id'         => $form->ID,
				'title'      => $form->post_title,
				'content'    => $form->post_content,
				'attributes' => $form_block ? $form_block['attrs'] : array(),
			);
		}

		/**
		 * Update form
		 */
		public function update_form( $request ) {
			$form_id = $request['id'];
			$content = isset( $request['content'] ) ? $request['content'] : '';

			$result = wp_update_post(
				array(
					'ID'           => $form_id,
					'post_content' => $content,
				)
			);

			if ( is_wp_error( $result ) ) {
				return $result;
			}

			return array(
				'success' => true,
				'id'      => $form_id,
			);
		}

		private function post_type_args() {
			return array(
				'labels'          => $this->post_type_labels(),
				'public'          => true,
				'show_in_menu'    => true,
				'supports'        => array( 'title', 'editor' ),
				'has_archive'     => false,
				'show_in_rest'    => true,
				'menu_icon'       => 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTQuMzUyMTEgMTEuNDE1NUgwLjE1NzYyNUwxMS40MTcgMC4xNTU1NzJWNC4zMTc1TDQuMzUyMTEgMTEuNDE1NVoiIGZpbGw9IndoaXRlIi8+CjxwYXRoIGQ9Ik0xOS44MDYxIDExLjQxNDFIMjQuMDAwNkwxMi43NDEyIDAuMTU0MTA3VjQuMzE2MDRMMTkuODA2MSAxMS40MTQxWiIgZmlsbD0id2hpdGUiLz4KPHBhdGggZD0iTTQuMzQ5MTggMTIuNzM5M0gwLjE1NDY5NkwxMS40MTQxIDIzLjk5OTJWMTkuODM3M0w0LjM0OTE4IDEyLjczOTNaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTkuODAzMiAxMi43NDAySDIzLjk5NzZMMTIuNzM4MyAyNC4wMDAyVjE5LjgzODNMMTkuODAzMiAxMi43NDAyWiIgZmlsbD0id2hpdGUiLz4KPHJlY3Qgd2lkdGg9IjguNzU3MjkiIGhlaWdodD0iMi41ODY4NiIgdHJhbnNmb3JtPSJtYXRyaXgoMSAwIDAgLTEgMTIuMDQxIDE1LjMyNjIpIiBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4K',
				'capability_type' => 'post',
				'template'        => array(
					array( 'gutena/forms', array() ),
				),
			);
		}

		private function post_type_labels() {
			return array(
				'not_found_in_trash' => __( 'No gutena forms found in Trash.', 'gutena-forms' ),
				'not_found'          => __( 'No gutena forms found.', 'gutena-forms' ),
				'parent_item_colon'  => __( 'Parent Gutena Forms:','gutena-forms' ),
				'add_new_item'       => __( 'Add New Form', 'gutena-forms' ),
				'search_items'       => __( 'Search Gutena Forms', 'gutena-forms' ),
				'view_item'          => __( 'View Gutena Form', 'gutena-forms' ),
				'edit_item'          => __( 'Edit Gutena Form', 'gutena-forms' ),
				'all_items'          => __( 'Forms', 'gutena-forms' ),
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

		public function admin_head() {
			global $current_screen;

			if ( 'edit-gutena_forms' !== $current_screen->id ) { return; }

			// If there are existing forms, show the normal list table — don't hide it.
			if ( $this->has_forms ) {
				return;
			}

			echo '<style>
					.wrap { display: none !important; }
					body { background: #ffffff !important; }
				</style>';
		}

		public function admin_footer() {
			global $current_screen;

			if ( 'edit-gutena_forms' !== $current_screen->id ) { return; }

			// If there are existing forms, don't inject the custom UI — let WP list table render.
			if ( $this->has_forms ) {
				return;
			}

			echo '<script>
			    ( function ( window ) {
			        const elementInnerHtml = `<div>
			        <style>
			            .gutena-forms__wrapper { margin: 30px 0 0 50px; }
			            .gutena-forms__add-new-form { color: #FFF !important; border: none !important; font-size: 14px !important; font-weight: 700 !important; border-radius: 4px !important; background: #0DA88C !important; }
			            .gutena-forms__add-new-form svg { vertical-align: middle; margin-right: 5px; }
			        </style>
			        <div class="gutena-forms__wrapper">
			            <div>
			                <h2 style="display: inline-block;margin-right: 20px;">
			                    ' . __( 'Gutena Forms', 'gutena-forms' ) . '
			                    <a style="display: inline-block;margin: -6px 0 0 20px;" href="' . esc_url( admin_url( 'post-new.php?post_type=' . $this->post_type ) ) . '" rel="noopener noreferrer" class="button gutena-forms__add-new-form">
			                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
			                            <circle cx="9" cy="9" r="9" fill="#D2FFF7"/>
			                            <path d="M8.17405 12.6V6.00001H9.84158V12.6H8.17405ZM5.40002 10.0714V8.54287H12.6V10.0714H5.40002Z" fill="#0DA88C"/>
			                        </svg>
			                        ' . __( 'Add New Form', 'gutena-forms' ) . '
			                    </a>
			                </h2>
			            </div>
			            <div style="margin-top: 30px;display: flex;justify-content: center;align-items: center;">
			                <div style="width: 660px;text-align: center;">
			                    <h3>👋🏼 Hi there!</h3>
			                    <p>
			                        <strong>It looks like you haven\'t created any forms yet.</strong>
			                        <br />
			                        You can use Gutena Forms to build contact forms, surveys, and more
			                        <br />
			                        with just a few clicks.
			                    </p>
			                    <div style="margin: 30px 80px;border-radius: 10px;padding: 30px;background: #D2FFF7;position: relative;display:flex;align-items:center;justify-content:space-between;gap:20px;">
			                        <div style="flex:1;padding-right:20px;">
			                            <h1 style="color: #067971;font-size: 24px;font-weight: 700;line-height: 1.4;margin:0;">
			                                How to Create your First Form With Gutena Forms (step by step)
			                            </h1>
			                        </div>
			                        <div style="flex:1;text-align:right;display:flex;justify-content:center;align-items:center;">
			                            <div style="display:inline-block;">
			                                <img src="' . GUTENA_FORMS_PLUGIN_URL . 'assets/img/form-illustration.png" alt="form-illustration" style="max-width:100%;height:auto;display:block;"/>
			                                <button aria-label="Play video" onclick="(function(){var m=document.getElementById(\'gf-video-modal\');var i=m.querySelector(\'iframe\');i.src=\'https://www.youtube.com/embed/2ZVS3b_02CA?autoplay=1&rel=0\';m.classList.add(\'show\');})();" style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);border:none;background:transparent;cursor:pointer;padding:0;display:flex;align-items:center;justify-content:center;width:83px;height:83px;">
			                                    <svg xmlns="http://www.w3.org/2000/svg" width="83" height="83" viewBox="0 0 83 83" fill="none">
			                                        <path d="M41.5 7C22.4698 7 7 22.4836 7 41.5C7 60.5302 22.4698 76 41.5 76C60.5164 76 76 60.5302 76 41.5C76 22.4836 60.5164 7 41.5 7Z" fill="#0DA88C" fill-opacity="0.3"/>
			                                        <path d="M36.8208 55.7548L53.5589 42.4193C53.8304 42.1975 54 41.8588 54 41.4968C54 41.1348 53.8304 40.7962 53.5589 40.5743L36.8208 27.2389C36.4815 26.9703 36.0179 26.9236 35.6333 27.1221C35.443 27.2184 35.2827 27.368 35.1707 27.5538C35.0587 27.7396 34.9996 27.9542 35 28.1731V54.8323C35 55.276 35.2488 55.6847 35.6333 55.8832C35.7917 55.9533 35.9613 56 36.131 56C36.3798 56 36.6173 55.9183 36.8208 55.7548Z" fill="white"/>
			                                    </svg>
			                                </button>
			                            </div>
			                        </div>
			                        <!-- Video modal -->
			                        <style type="text/css">
			                            /* Modal base */
			                            .gf-modal { position:fixed; inset:0; display:none; align-items:center; justify-content:center; background:rgba(0,0,0,0.6); z-index:99999; }
			                            .gf-modal.show { display:flex; }
			                            .gf-modal-dialog { width:90%; max-width:900px; background:#fff; border-radius:8px; overflow:hidden; transform-origin:center center; }
			                            .gf-modal-content { position:relative; padding-top:56.25%; } /* 16:9 ratio */
			                            .gf-modal-content iframe { position:absolute; inset:0; width:100%; height:100%; border:0; display:block; }
			                            .gf-modal-close { position:absolute; right:8px; top:8px; z-index:2; background:#fff;border:0;border-radius:50%;width:36px;height:36px;cursor:pointer;font-size:18px; }
			                            /* Animations (no CDN) */
			                            @keyframes gf-zoom-in { from { transform: scale(0.8); opacity:0 } to { transform: scale(1); opacity:1 } }
			                            @keyframes gf-zoom-out { from { transform: scale(1); opacity:1 } to { transform: scale(0.8); opacity:0 } }
			                            .gf-modal.show .gf-modal-dialog { animation: gf-zoom-in 300ms ease forwards; }
			                            .gf-modal.closing .gf-modal-dialog { animation: gf-zoom-out 300ms ease forwards; }
			                        </style>
			                        <div id="gf-video-modal" class="gf-modal" aria-hidden="true">
			                            <div class="gf-modal-dialog" role="dialog" aria-modal="true">
			                                <button class="gf-modal-close" onclick="(function(){var m=document.getElementById(\'gf-video-modal\');m.classList.add(\'closing\');setTimeout(function(){m.classList.remove(\'show\');m.classList.remove(\'closing\');m.querySelector(\'iframe\').src=\'\';},300);})();">×</button>
			                                <div class="gf-modal-content">
			                                    <iframe src="" title="Gutena Forms Intro" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
			                                </div>
			                            </div>
			                        </div>
			                    </div>
			                    <a style="margin-bottom: 30px;" href="' . esc_url( admin_url( 'post-new.php?post_type=' . $this->post_type ) ) . '" rel="noopener noreferrer" class="button gutena-forms__add-new-form">Create Your First Form</a>
			                    <p>
			                        Need some help? Check out our <a href="https://gutenaforms.com/#faq" target="_blank" rel="noopener noreferrer" style="color: #0DA88C;text-decoration: none;">comprehensive guide.</a>
			                    </p>
			                </div>
			            </div>
			        </div>
			        </div>`;
			        const element = window.document.getElementById( "wpbody-content" );
			        if ( element ) {
			            element.innerHTML = elementInnerHtml;
			        }
			    } )( window );
			</script>';
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
