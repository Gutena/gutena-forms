<?php
/**
 * Class Forms
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Forms' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Class Forms
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Forms extends Gutena_Forms_Forms_Settings {
		/**
		 * Get Instance
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Forms $instance The single instance of the class.
		 */
		private static $instance;

		/**
		 * Get Instance
		 *
		 * @since 1.6.0
		 * @return Gutena_Forms_Forms
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.6.0
		 */
		public function __construct() {
			add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
		}

		/**
		 * REST API Init
		 *
		 * @since 1.6.0
		 * @param WP_REST_Server $server REST server.
		 */
		public function rest_api_init( $server ) {
			register_rest_route(
				Gutena_Forms_Rest_API_Controller::$namespace,
				'forms/get-all',
				array(
					'permission_callback' => array( 'Gutena_Forms_Rest_API_Controller', 'permission_callback' ),
					'methods'             => $server::READABLE,
					'callback'            => array( $this, 'get_forms' ),
				)
			);

			register_rest_route(
				Gutena_Forms_Rest_API_Controller::$namespace,
				'forms/delete',
				array(
					'permission_callback' => array( 'Gutena_Forms_Rest_API_Controller', 'permission_callback' ),
					'methods'             => $server::DELETABLE,
					'callback'            => array( $this, 'delete_form' ),
				)
			);
		}

		/**
		 * Get Forms
		 *
		 * @since 1.6.0
		 * @return WP_REST_Response
		 */
		public function get_forms() {
			$forms = get_posts(
				array(
					'post_type'      => 'gutena_forms',
					'post_status'    => array( 'publish', 'draft' ),
					'posts_per_page' => -1,
				)
			);

			$forms = array_map(
				function ( $form ) {
					return array(
						'id' => $form->ID,
						'datetime'  => gmdate( 'Y-m-d h:i A', strtotime( $form->post_date ) ),
						'title'     => $form->post_title,
						'status'    => $form->post_status,
						'entries'   => Gutena_Forms_Entries::get_instance()->get_entries_count_by_form_id( $form->ID ),
						'author'    => get_the_author_meta( 'display_name', $form->post_author ),
						'permalink' => get_permalink( $form->ID ),
					);
				},
				$forms
			);

			return rest_ensure_response(
				array(
					'forms'  => $forms,
					'status' => 'success',
				)
			);
		}

		/**
		 * Delete Form
		 *
		 * @since 1.6.0
		 * @param WP_REST_Request $request REST request.
		 */
		public function delete_form( $request ) {
			$form_id = wp_unslash( $request->get_param( 'form_id' ) );
			if ( is_array( $form_id ) ) {
				foreach ( $form_id as $id ) {
					$id = intval( $id );
					$this->delete_form_helper( $id );
				}
			} else {
				$form_id = intval( $form_id );
				$this->delete_form_helper( $form_id );
			}

			return rest_ensure_response(
				array(
					'message' => __( 'Form deleted successfully.', 'gutena-forms' ),
					'status'  => 'success',
				)
			);
		}

		/**
		 * Delete Form Helper
		 *
		 * @since 1.6.0
		 * @param int $form_id Form ID.
		 *
		 * @return array|false|WP_Post
		 */
		private function delete_form_helper( $form_id ) {
			if ( empty( $form_id ) ) {
				return false;
			}

			if ( 'gutena_forms' !== get_post_type( $form_id ) ) {
				return false;
			}

			return wp_delete_post( $form_id, true );
		}

		/**
		 * Register Module
		 *
		 * @since 1.6.0
		 */
		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {
					$settings['forms'] = __CLASS__;
					return $settings;
				}
			);

			self::get_instance();
		}

		/**
		 * Get Settings
		 *
		 * @since 1.6.0
		 * @return array
		 */
		public function get_settings() {
			return array(
				'fields' => array(
					array(
						'type' => 'template',
						'name' => 'forms',
					)
				),
			);
		}

		/**
		 * Save Settings
		 *
		 * @since 1.6.0
		 * @param array $settings Settings array.
		 */
		public function save_settings( $settings ) {
			// dummy function.
		}

		public static function get_form_name_by_id( $form_id ) {
			return 'Placeholder name';
		}
	}

	Gutena_Forms_Forms::register_module();
endif;
