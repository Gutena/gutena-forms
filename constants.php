<?php
/**
 * Gutena Forms Constants File
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'GUTENA_FORMS_FILE' ) ) {
	/**
	 * Plugin dir path
	 */
	define( 'GUTENA_FORMS_FILE',  __FILE__ );
}

if ( ! defined( 'GUTENA_FORMS_DIR_PATH' ) ) {
	/**
	 * Plugin dir path
	 */
	define( 'GUTENA_FORMS_DIR_PATH',  plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'GUTENA_FORMS_PLUGIN_URL' ) ) {
	/**
	 * Plugin url
	 */
	define( 'GUTENA_FORMS_PLUGIN_URL', esc_url( trailingslashit( plugins_url( '', __FILE__ ) ) ) );
}

if ( ! defined( 'GUTENA_FORMS_VERSION' ) ) {
	/**
	 * Plugin version.
	 */
	define( 'GUTENA_FORMS_VERSION', '1.5.0' );
}
