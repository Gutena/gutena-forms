<?php
/**
 * Class Admin Helper
 *
 * @since 1.8.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Admin_Helper' ) ) :
	class Gutena_Forms_Admin_Helper {
		public static function include_wp_list_table() {
			if ( ! class_exists( 'WP_List_Table' ) ) {
				if ( file_exists( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ) ) {
					require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
				}
			}
		}

		public static function feature_request_redirection() {
			if ( isset( $_GET['pagetype'] ) && 'feature-request' === sanitize_key( wp_unslash( $_GET['pagetype'] ) ) ) {
				echo '<script type="text/javascript">
					window.location.href = "https://gutenaforms.com/roadmap/?utm_source=plugin&utm_medium=tab&utm_campaign=feature_requests";
				</script>';
				exit;
			}
		}

		public static function pricing_page_redirection() {
			if ( isset( $_GET['page'] ) && 'gutena-forms-upgrade' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
				wp_redirect(
					add_query_arg(
						array(
							'utm_source'   => 'wordpress_admin_menu',
							'utm_medium'   => 'website',
							'utm_campaign' => 'free_plugin',
						),
						'https://gutenaforms.com/pricing/'
					)
				);
				exit;
			}
		}

		public static function optimize_submenu() {
			global $submenu;
			if ( isset( $submenu['gutena-forms'][0] ) ) {
				unset( $submenu['gutena-forms'][0] );
			}

			if ( isset( $submenu['gutena-forms'] ) && is_array( $submenu['gutena-forms'] ) ) {
				foreach ( $submenu['gutena-forms'] as $k => $gutena_form ) {
					if ( str_contains( $gutena_form[0], 'Add-Ons' ) ) {
						unset( $submenu['gutena-forms'][ $k ] );
					}
				}
			}
		}

		public static function is_admin( $check_permission = 'manage_options' ) {
			if ( ! function_exists( 'wp_get_current_user' ) && file_exists( ABSPATH . 'wp-includes/pluggable.php' ) ) {
				require_once ABSPATH . 'wp-includes/pluggable.php';
			}
			if ( ! function_exists( 'current_user_can' ) && file_exists( ABSPATH . 'wp-includes/capabilities.php' ) ) {
				require_once ABSPATH . 'wp-includes/capabilities.php';
			}
			return ( function_exists( 'is_admin' ) && is_admin() && function_exists( 'current_user_can' ) && current_user_can( $check_permission ) );
		}
		
		public static function get_changelog() {
			$response = wp_remote_get(
				GUTENA_FORMS_PLUGIN_URL . 'readme.txt',
				array(
					'sslverify' => false,
				)
			);
			if ( ! is_wp_error( $response ) ) {
				$response = wp_remote_retrieve_body( $response );
				$response = explode( '== Changelog ==', $response, 2 );
				if ( 2 === count( $response ) ) {
					$response = explode( '== Copyright ==', trim( $response[1] ), 2 );
					if ( ! empty( $response[0] ) ) {
						$response = $response[0];
						$response = str_ireplace( '= ', "<span class='version'>", $response );
						$response = str_ireplace( ' =', '</span>', $response );
						return $response;
					}
				}
			}
			return '';
		}
		
		public static function is_empty( $check_array, $keys, $logic_or = true ) {
			// return if values not provided
			if ( empty( $check_array ) || empty( $keys ) ) {
				return true;
			}
			
			foreach ( $keys as $key ) {
				if ( $logic_or ) {
					if ( empty( $check_array[ $key ] ) ) {
						return true;
					}
				} elseif ( ! empty( $check_array[ $key ] ) ) {
					return false;
				}
			}
			/**
			 * At the end for
			 * OR condition no one is empty  so return false
			 * AND all are empty so return true
			 * */
			return ! $logic_or;
		}

		public static function sanitize_serialize_data( $data ) {
			if ( empty( $data ) ) {
				return $data;
			}
			
			if ( is_object( $data ) ) {
				$data = clone $data;
			} elseif ( is_array( $data ) ) {
				$data = Gutena_Forms_Helper::sanitize_array( $data, true );
			} else {
				$data = sanitize_textarea_field( $data );
			}
			
			$data = sanitize_option( 'gutena_forms', $data );
			
			if ( ! is_serialized( $data ) ) {
				$data = maybe_serialize( $data );
			}
			
			return $data;
		}
	}
endif;
