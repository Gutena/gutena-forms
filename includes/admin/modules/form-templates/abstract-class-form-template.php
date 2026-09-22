<?php
/**
 * Abstract base class for Gutena Forms template library entries.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Abstract_Form_Template' ) ) :
	/**
	 * Base class for form template definitions.
	 *
	 * Each concrete template class is responsible for returning its own
	 * complete definition via {@see get_template()}.
	 *
	 * @since 2.2.0
	 * @package Gutena Forms
	 */
	abstract class Gutena_Forms_Abstract_Form_Template {
		/**
		 * Return the complete template definition.
		 *
		 * @since 2.2.0
		 * @return array {
		 *     Template definition array.
		 *
		 *     @type string $id              Stable template identifier.
		 *     @type string $title           Translated template title.
		 *     @type string $description     Translated template description.
		 *     @type string $category        Machine-readable category slug.
		 *     @type bool   $is_pro          Whether the template requires Pro.
		 *     @type array  $fields          Field definitions compatible with Gutena block attributes.
		 *     @type string $submit_label    Translated submit button label.
		 *     @type string $success_message Translated success message.
		 *     @type string $preview_image   Optional preview image path relative to the plugin root.
		 * }
		 */
		abstract public static function get_template();
	}
endif;
