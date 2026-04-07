<?php

defined( 'ABSPATH' ) || exit;

use WP\MCP\Core\McpAdapter;
use WP\MCP\Transport\HttpTransport;
use WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler;
use WP\MCP\Infrastructure\Observability\NullMcpObservabilityHandler;

if ( ! class_exists( 'Gutena_Forms_MCP_Server' ) ) {
	class Gutena_Forms_MCP_Server {
		
		private static $instance;
		
		private $version = 'v1.0.0';
		
		public function __construct() {
			$settings = get_option( 'gutena_forms_mcp_settings', array() );
			
			if ( empty( $settings ) && ! is_array( $settings ) ) {
				return;
			}
			
			if ( ! isset( $settings['abilities_enabled'] ) && ! isset( $settings['mcp_enabled'] ) ) {
				return;
			}
			
			if ( $settings['abilities_enabled'] && $settings['mcp_enabled'] ) {
				$this->run();
			}
		}
		
		private function run() {
			add_action( 'mcp_adapter_init', array( $this, 'adapter_init' ) );
		}
		
		/**
		 * @param McpAdapter $adapter
		 */
		public function adapter_init( $adapter ) {
			$mcp_args = apply_filters(
				'gutena_forms__mcp_adapter_args',
				array(
					'tools'     => array(
						'gutena-forms/get-all-forms',
						'gutena-forms/get-all-entries',
						'gutena-forms/get-form-entries',
						'gutena-forms/get-entry-details',
					),
					'resources' => array(),
					'prompts'   => array(),
				)
			);
			
			$adapter->create_server(
				'gutena-forms-mcp',
				'gutena-forms',
				'v1/mcp',
				__( 'Gutena Forms MCP Server', 'gutena-forms' ),
				__( 'Gutena Forms MCP Server', 'gutena-forms' ),
				$this->version,
				array( HttpTransport::class ),
				ErrorLogMcpErrorHandler::class,
				NullMcpObservabilityHandler::class,
				$mcp_args['tools'],
				$mcp_args['resources'],
				$mcp_args['prompts']
			);
		}
		
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self;
			}
			
			return self::$instance;
		}
	}
	
	Gutena_Forms_MCP_Server::get_instance();
}