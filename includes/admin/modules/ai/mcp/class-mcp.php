<?php
/**
 * Class Gutena Forms MCP Settings
 *
 * @since 1.8.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_MCP' ) ) :
	/**
	 * Gutena Forms MCP Settings class.
	 *
	 * @since 1.8.0
	 */
	class Gutena_Forms_MCP {
		private static $instance;
		
		private $version = '1.0.0';

		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
		
		public function __construct() {
			add_action( 'mcp_adapter_init', array( $this, 'mcp_adapter_init' ) );
		}
		
		/**
		 * @param \WP\MCP\Core\McpAdapter $adapter
		 */
		public function mcp_adapter_init( $adapter ) {
			$adapter->create_server(
				'gutena-forms-mcp',
				'gutena-forms',
				'v1/mcp',
				__( 'Gutena Forms MCP Server', 'gutena-forms' ),
				'',
				sprintf( 'v%s', $this->version ),
				array(
					\WP\MCP\Transport\HttpTransport::class,
				),
				\WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler::class,
				\WP\MCP\Infrastructure\Observability\NullMcpObservabilityHandler::class,
				array(
					'gutena-forms/get-forms',
					'gutena-forms/get-entries',
				),
				array(),
				array(),
			);
		}
	}
	
	Gutena_Forms_MCP::get_instance();
endif;
