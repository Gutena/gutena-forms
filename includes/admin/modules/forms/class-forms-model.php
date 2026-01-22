<?php
/**
 * Gutena Forms Forms Model
 *
 * @since 1.6.0
 * @package GutenaForms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Forms_Model' ) ) :
	/**
	 * Gutena Forms Forms Model Class
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Forms_Model {
		/**
		 * The single instance of the class.
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Forms_Model $instance The single instance of the class.
		 */
		private static $instance;

		/**
		 * The WordPress Database Object.
		 *
		 * @since 1.6.0
		 * @var wpdb $wpdb WordPress Database Object.
		 */
		private $wpdb;

		/**
		 * Forms Store Instance.
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Store $store Forms Store Instance.
		 */
		private $store;

		/**
		 * Constructor
		 *
		 * @since 1.6.0
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb  = $wpdb;
			$this->store = new Gutena_Forms_Store();
		}

		/**
		 * Get Instance
		 *
		 * @since 1.6.0
		 * @return Gutena_Forms_Forms_Model
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Get All Forms
		 *
		 * @since 1.6.0
		 * @return array|array[]
		 */
		public function get_all() {
			$forms = get_posts(
				array(
					'post_type'      => 'gutena_forms',
					'post_status'    => array( 'publish', 'draft' ),
					'posts_per_page' => -1,
				)
			);

			return array_map(
				function ( $form ) {
					return array(
						'id'        => $form->ID,
						'datetime'  => gmdate( 'Y-m-d h:i A', strtotime( $form->post_date ) ),
						'title'     => $form->post_title,
						'status'    => $form->post_status,
						'entries'   => Gutena_Forms_Entries_Model::get_instance()->get_count_by_form_id( $form->ID ),
						'author'    => get_the_author_meta( 'display_name', $form->post_author ),
						'permalink' => get_permalink( $form->ID ),
					);
				},
				$forms
			);
		}

		/**
		 * Delete Form
		 *
		 * @since 1.6.0
		 * @param int|string $form_id Form ID.
		 *
		 * @return array|false|WP_Post|null
		 */
		public function delete( $form_id ) {
			if ( empty( $form_id ) ) {
				return false;
			}

			if ( 'gutena_forms' !== get_post_type( $form_id ) ) {
				return false;
			}

			return wp_delete_post( $form_id, true );
		}

		/**
		 * Get Name By ID
		 *
		 * @since 1.6.0
		 * @param int|string $form_id Form ID.
		 *
		 * @return string
		 */
		public function get_name_by_id( $form_id ) {
			return 'Placeholder name';
		}
	}
endif;
