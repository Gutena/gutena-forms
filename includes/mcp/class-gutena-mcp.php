<?php
/**
 * Gutena MCP class
 * 
 * @since 1.6.1
 * @package Gutena Forms
 */

use WP\MCP\Core\McpAdapter;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_MCPServer' ) ) :
    class Gutena_Forms_MCPServer {
        private static $instance;

        private function __construct() {

            add_action( 'mcp_adapter_init', array( $this, 'mcp_adapter_init' ) );
            require_once plugin_dir_path( __FILE__ ) . 'class-gutena-abilities.php';
            add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
            add_action( 'init', function () {

            session_start();

            // display the session id
            // echo 'Session ID: ' . session_id();
            } );
        }
	    
	    /**
	     * @param McpAdapter $adapter
	     */
        public function mcp_adapter_init( $adapter ) {
            $adapter->create_server(
                'gutena-forms-mcp',                    // Unique server identifier
                'gutena-forms',                    // REST API namespace
                'v1/mcp',                            // REST API route
                'My MCP Server',                  // Server name
                'Description of my server',       // Server description
                'v1.0.0',                        // Server version
                [                                 // Transport methods
                    \WP\MCP\Transport\HttpTransport::class,  // Recommended: MCP 2025-06-18 compliant
                ],
                \WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler::class, // Error handler
                \WP\MCP\Infrastructure\Observability\NullMcpObservabilityHandler::class, // Observability handler
                array(
                    'gutena-forms/get-forms',
                    'gutena-forms/get-form-entries',
                    'gutena-forms/get-entry-analysis',
                    'gutena-forms/delete-entry'
                ),         // Abilities to expose as tools
                [],                              // Resources (optional)
                [],                              // Prompts (optional)
            );
        }

        public function rest_api_init() {
        }


        public static function get_instance() {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Gutena_Forms_MCPServer ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }
    }

    Gutena_Forms_MCPServer::get_instance();
endif;
