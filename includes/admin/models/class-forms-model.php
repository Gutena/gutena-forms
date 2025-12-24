<?php
/**
 * Class Forms Model
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Forms_Model' ) ) :
	/**
	 * Gutena Forms Forms Model class.
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Forms_Model {
		public static function fetch_all_forms() {
			$forms = get_posts(
				array(

				)
			);
		}

		public static function fetch_form( $form_id ) {

		}
	}
endif;
