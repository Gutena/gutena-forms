<?php
/**
 * Abstract Integrations Class
 *
 * @since 1.8.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Integration_Settings' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	abstract class Gutena_Forms_Integration_Settings extends Gutena_Forms_Forms_Settings {} {

	}
endif;