<?php
/**
 * Forms model: list, delete, get name/block id for gutena_forms post type.
 *
 * @since 1.7.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Forms_Model' ) ) :
	/**
	 * Data access for Gutena forms (CPT gutena_forms): list, delete, get name/block id.
	 *
	 * @since 1.7.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Forms_Model {
		/**
		 * Singleton instance.
		 *
		 * @since 1.7.0
		 * @var Gutena_Forms_Forms_Model $instance The single instance of the class.
		 */
		private static $instance;

		/**
		 * WordPress database instance.
		 *
		 * @since 1.7.0
		 * @var wpdb $wpdb WordPress database instance.
		 */
		private $wpdb;

		/**
		 * Store instance for table names and entry updates.
		 *
		 * @since 1.7.0
		 * @var Gutena_Forms_Store $store Store instance.
		 */
		private $store;

		/**
		 * Set WordPress database and store instances.
		 *
		 * @since 1.7.0
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb  = $wpdb;
			$this->store = new Gutena_Forms_Store();
		}

		/**
		 * Get singleton instance.
		 *
		 * @since 1.7.0
		 * @return Gutena_Forms_Forms_Model
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Get all published/draft forms with id, datetime, title, status, entries count, author, permalink.
		 *
		 * @since 1.7.0
		 * @return array[] List of form arrays.
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
						'status'    => ucfirst( $form->post_status ),
						'entries'   => Gutena_Forms_Entries_Model::get_instance()->get_count_by_form_id( $form->ID ),
						'author'    => get_the_author_meta( 'display_name', $form->post_author ),
						'permalink' => get_permalink( $form->ID ),
					);
				},
				$forms
			);
		}

		/**
		 * Permanently delete a form post by ID.
		 *
		 * @since 1.7.0
		 * @param int|string $form_id Form (post) ID.
		 * @return WP_Post|false|null Post object on success, false if not gutena_forms, null on failure.
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
		 * Get form display name by form ID.
		 *
		 * @since 1.7.0
		 * @param int|string $form_id Form ID.
		 * @return string Form name (currently placeholder).
		 */
		public function get_name_by_id( $form_id ) {
			$sql = 'SELECT form_name FROM %i WHERE form_id = %d';
			$sql = $this->wpdb->prepare( $sql, $this->store->table_gutenaforms, $form_id );

			return $this->wpdb->get_var( $sql );
		}

		/**
		 * Get list of published forms with title and block id (for search options).
		 *
		 * @since 1.7.0
		 * @return array[] List of arrays with 'title' and 'id' (block id).
		 */
		public function get_name_and_block_id() {
			$forms = get_posts(
				array(
					'post_type'      => 'gutena_forms',
					'post_status'    => array( 'publish' ),
					'posts_per_page' => -1,
				)
			);

			return array_map(
				function ( $form ) {
					return array(
						'title' => $form->post_title,
						'id'    => get_post_meta( $form->ID, 'gutena_form_id', true ),
					);
				},
				$forms
			);
		}
	}
endif;
