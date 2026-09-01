<?php
/**
 * Class Settings Migrator
 *
 * @since 1.8.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Settings_Migrator' ) ) :
	/**
	 * Gutena Forms Settings Migrator
	 *
	 * @since 1.8.0
	 */
	class Gutena_Forms_Settings_Migrator {
		/**
		 * The instance of the class
		 *
		 * @since 1.8.0
		 * @var Gutena_Forms_Settings_Migrator $instance Instance of the class.
		 */
		private static $instance;

		/**
		 * One-time 1.9 global settings upgrade flag.
		 *
		 * @since 1.9.0
		 */
		const UPGRADE_190_GLOBALS_FLAG = 'gutena_forms_upgrade_190_globals_done';

		/**
		 * Getting the instance of the class
		 *
		 * @since 1.8.0
		 * @return Gutena_Forms_Settings_Migrator
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor of the class
		 *
		 * @since 1.8.0
		 */
		private function __construct() {
			add_action( 'admin_init', array( $this, 'fetch_all_gutena_forms' ) );
			add_action( 'gutena_forms__saving_block', array( __CLASS__, 'on_saving_form_block' ), 5, 3 );
		}

		/**
		 * Seed or sync globals when a gutena/forms block is saved.
		 *
		 * @since 1.9.0
		 * @param array   $attributes Block attributes.
		 * @param array   $block      Block data.
		 * @param WP_Post $post       Post object.
		 */
		public static function on_saving_form_block( $attributes, $block, $post ) {
			unset( $block, $post );
			self::seed_globals_from_form_block( $attributes );
		}

		/**
		 * Distinct form IDs for this site.
		 *
		 * @since 1.9.0
		 * @return array
		 */
		public static function get_form_ids_list() {
			$ids = get_option( 'gutena_form_ids', array() );
			if ( ! is_array( $ids ) ) {
				$ids = array();
			}

			$unique = array();
			foreach ( $ids as $id ) {
				$id = sanitize_key( $id );
				if ( '' !== $id && ! in_array( $id, $unique, true ) ) {
					$unique[] = $id;
				}
			}

			if ( ! empty( $unique ) ) {
				return $unique;
			}

			return self::collect_form_ids_from_cpts();
		}

		/**
		 * Collect form IDs from gutena_forms CPT posts when gutena_form_ids is empty.
		 *
		 * @since 1.9.0
		 * @return array
		 */
		private static function collect_form_ids_from_cpts() {
			$posts = get_posts(
				array(
					'post_type'      => 'gutena_forms',
					'posts_per_page' => -1,
					'post_status'    => array( 'publish', 'draft', 'private' ),
					'orderby'        => 'date',
					'order'          => 'ASC',
				)
			);

			$form_ids = array();
			foreach ( $posts as $post ) {
				if ( empty( $post->post_content ) ) {
					continue;
				}
				$blocks = parse_blocks( $post->post_content );
				foreach ( $blocks as $block ) {
					if ( empty( $block['blockName'] ) || 'gutena/forms' !== $block['blockName'] ) {
						continue;
					}
					if ( empty( $block['attrs']['formID'] ) ) {
						continue;
					}
					$form_id = sanitize_key( $block['attrs']['formID'] );
					if ( '' !== $form_id && ! in_array( $form_id, $form_ids, true ) ) {
						$form_ids[] = $form_id;
					}
				}
			}

			return $form_ids;
		}

		/**
		 * Number of distinct forms on the site.
		 *
		 * @since 1.9.0
		 * @return int
		 */
		public static function get_form_count() {
			$from_option = count( self::get_form_ids_list() );
			$from_cpt    = count( self::collect_form_ids_from_cpts() );

			return max( $from_option, $from_cpt );
		}

		/**
		 * First form ID in gutena_form_ids order (source for global settings on upgrade).
		 *
		 * @since 1.9.0
		 * @return string|false
		 */
		public static function get_first_form_id() {
			$ids = self::get_form_ids_list();
			if ( empty( $ids ) ) {
				return false;
			}
			return $ids[0];
		}

		/**
		 * Resolve gutena_forms CPT for a form ID.
		 *
		 * @since 1.9.0
		 * @param string $form_id Form ID.
		 * @return WP_Post|false
		 */
		public static function get_form_post_by_form_id( $form_id ) {
			$form_id = sanitize_key( $form_id );
			if ( '' === $form_id ) {
				return false;
			}

			$posts = get_posts(
				array(
					'post_type'      => 'gutena_forms',
					'posts_per_page' => 1,
					'post_status'    => array( 'publish', 'draft', 'private' ),
					'meta_key'       => 'gutena_form_id',
					'meta_value'     => $form_id,
				)
			);

			if ( ! empty( $posts ) ) {
				return $posts[0];
			}

			$all_posts = get_posts(
				array(
					'post_type'      => 'gutena_forms',
					'posts_per_page' => -1,
					'post_status'    => array( 'publish', 'draft', 'private' ),
				)
			);

			foreach ( $all_posts as $post ) {
				if ( empty( $post->post_content ) ) {
					continue;
				}
				$blocks = parse_blocks( $post->post_content );
				foreach ( $blocks as $block ) {
					if ( empty( $block['blockName'] ) || 'gutena/forms' !== $block['blockName'] ) {
						continue;
					}
					if ( ! empty( $block['attrs']['formID'] ) && sanitize_key( $block['attrs']['formID'] ) === $form_id ) {
						return $post;
					}
				}
			}

			return false;
		}

		/**
		 * Form block attrs for migration (CPT post_content only).
		 *
		 * @since 1.9.0
		 * @param string $form_id Form ID.
		 * @return array
		 */
		public static function get_form_attrs_for_migration( $form_id ) {
			$form_id = sanitize_key( $form_id );
			if ( '' === $form_id ) {
				return array();
			}

			$post = self::get_form_post_by_form_id( $form_id );
			if ( $post && ! empty( $post->post_content ) ) {
				$blocks = parse_blocks( $post->post_content );
				foreach ( $blocks as $block ) {
					if ( empty( $block['blockName'] ) || 'gutena/forms' !== $block['blockName'] ) {
						continue;
					}
					if ( ! empty( $block['attrs']['formID'] ) && sanitize_key( $block['attrs']['formID'] ) === $form_id ) {
						return isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
					}
				}
			}

			return array();
		}

		/**
		 * Whether block attrs contain settings that can seed global options.
		 *
		 * @since 1.9.0
		 * @param array $attrs Form block attrs.
		 * @return bool
		 */
		private static function form_attrs_have_migratable_settings( $attrs ) {
			if ( ! is_array( $attrs ) || empty( $attrs ) ) {
				return false;
			}

			foreach ( array( 'recaptcha', 'cloudflareTurnstile', 'honeypot', 'messages' ) as $module_key ) {
				if ( ! empty( $attrs[ $module_key ] ) && is_array( $attrs[ $module_key ] ) ) {
					return true;
				}
			}

			if (
				! empty( $attrs['settings']['integration'] )
				&& is_array( $attrs['settings']['integration'] )
			) {
				foreach ( array( 'mailchimp', 'brevo', 'activecampaign' ) as $integration_id ) {
					if (
						! empty( $attrs['settings']['integration'][ $integration_id ] )
						&& is_array( $attrs['settings']['integration'][ $integration_id ] )
					) {
						return true;
					}
				}
			}

			return false;
		}

		/**
		 * Fetch primary form post (single-form sites only).
		 *
		 * @since 1.8.0
		 * @return false|WP_Post
		 */
		public static function fetch_primary_form() {
			if ( 1 !== self::get_form_count() ) {
				return false;
			}

			$form_id = self::get_first_form_id();
			if ( ! $form_id ) {
				return false;
			}

			$post = self::get_form_post_by_form_id( $form_id );
			if ( ! $post || ! self::$instance->post_has_gutena_form_block( $post ) ) {
				return false;
			}

			return $post;
		}

		/**
		 * Updating the primary form
		 *
		 * @since 1.8.0
		 * @param string $key Key of settings.
		 * @param array  $settings Settings need to update in block.
		 *
		 * @return false|int|WP_Error
		 */
		public static function update_primary_form( $key, $settings ) {
			$post = self::fetch_primary_form();
			if ( ! $post || ! self::$instance->post_has_gutena_form_block( $post ) ) {
				return false;
			}

			$gutena_block = parse_blocks( $post->post_content );
			foreach ( $gutena_block as $k => $block ) {
				if ( 'gutena/forms' === $block['blockName'] ) {
					$gutena_block[ $k ]['attrs'][ $key ] = $settings;
				}
			}

			$new_content = '';
			foreach ( $gutena_block as $k => $block ) {
				$new_content .= serialize_block( $block );
			}

			return wp_update_post(
				array(
					'ID'           => $post->ID,
					'post_content' => $new_content,
				),
				false,
				false
			);
		}

		/**
		 * Update integration slice on the primary form block (attrs.settings.integration).
		 *
		 * @since 1.8.0
		 * @param string $integration_id Integration key (e.g. mailchimp, brevo, activecampaign).
		 * @param array  $settings       Settings stored under settings.integration[ id ].
		 * @return false|int|WP_Error
		 */
		public static function update_primary_form_integration( $integration_id, $settings ) {
			$post = self::fetch_primary_form();
			if ( ! $post || ! self::$instance->post_has_gutena_form_block( $post ) ) {
				return false;
			}

			$integration_id = sanitize_key( $integration_id );
			if ( '' === $integration_id ) {
				return false;
			}

			$gutena_block = parse_blocks( $post->post_content );
			foreach ( $gutena_block as $k => $block ) {
				if ( 'gutena/forms' !== $block['blockName'] ) {
					continue;
				}

				$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
				if ( ! isset( $attrs['settings'] ) || ! is_array( $attrs['settings'] ) ) {
					$attrs['settings'] = array();
				}
				if ( ! isset( $attrs['settings']['integration'] ) || ! is_array( $attrs['settings']['integration'] ) ) {
					$attrs['settings']['integration'] = array();
				}

				$attrs['settings']['integration'][ $integration_id ] = is_array( $settings ) ? $settings : array();
				$gutena_block[ $k ]['attrs']                           = $attrs;
			}

			$new_content = '';
			foreach ( $gutena_block as $k => $block ) {
				$new_content .= serialize_block( $block );
			}

			return wp_update_post(
				array(
					'ID'           => $post->ID,
					'post_content' => $new_content,
				),
				false,
				false
			);
		}

		/**
		 * Check whether site has a single form (primary form case).
		 *
		 * @since 1.8.0
		 * @return bool
		 */
		public static function is_single_form_site() {
			return 1 === self::get_form_count();
		}

		/**
		 * Prepare block settings for global option storage.
		 *
		 * @since 1.8.0
		 * @param array $settings Raw block settings.
		 * @return array
		 */
		public static function sanitize_settings_for_option( $settings ) {
			$settings = is_array( $settings ) ? $settings : array();

			if ( isset( $settings['defaultSettings'] ) ) {
				unset( $settings['defaultSettings'] );
			}

			return $settings;
		}

		/**
		 * Whether a one-time 1.9 global settings upgrade should run.
		 *
		 * @since 1.9.0
		 * @return bool
		 */
		public static function needs_upgrade_190() {
			if ( get_option( self::UPGRADE_190_GLOBALS_FLAG, false ) ) {
				return false;
			}

			$stored_version = get_option( 'gutena_forms_version', '0' );
			if ( defined( 'GUTENA_FORMS_VERSION' ) && version_compare( $stored_version, '1.9.0', '<' ) ) {
				return true;
			}

			if ( self::has_migration_source_data() && self::has_empty_new_global_options() ) {
				return true;
			}

			return false;
		}

		/**
		 * Whether the first form block attrs can seed globals.
		 *
		 * @since 1.9.0
		 * @return bool
		 */
		private static function has_migration_source_data() {
			$form_id = self::get_first_form_id();
			if ( ! $form_id ) {
				return false;
			}

			$attrs = self::get_form_attrs_for_migration( $form_id );

			return self::form_attrs_have_migratable_settings( $attrs );
		}

		/**
		 * Whether new-style global options are all empty.
		 *
		 * @since 1.9.0
		 * @return bool
		 */
		private static function has_empty_new_global_options() {
			$new_options = array(
				get_option( 'gutena_forms__recaptcha', array() ),
				get_option( 'gutena_forms__form_validation_messages', array() ),
				get_option( 'gutena_forms__cloudflare', array() ),
				get_option( 'gutena_forms__honeypot', array() ),
				get_option( 'gutena_forms__integration_settings', array() ),
			);

			foreach ( $new_options as $value ) {
				if ( ! empty( $value ) && is_array( $value ) ) {
					return false;
				}
			}

			return true;
		}

		/**
		 * One-time upgrade: seed 1.9 globals from first form block attrs only.
		 *
		 * @since 1.9.0
		 * @return bool True if upgrade ran or was already complete.
		 */
		public static function upgrade_globals_to_1_9() {
			if ( get_option( self::UPGRADE_190_GLOBALS_FLAG, false ) ) {
				return true;
			}

			if ( ! self::needs_upgrade_190() && ! self::has_migration_source_data() ) {
				update_option( self::UPGRADE_190_GLOBALS_FLAG, 1 );
				if ( defined( 'GUTENA_FORMS_VERSION' ) ) {
					update_option( 'gutena_forms_version', GUTENA_FORMS_VERSION );
				}
				return true;
			}

			$form_id = self::get_first_form_id();
			$attrs   = $form_id ? self::get_form_attrs_for_migration( $form_id ) : array();

			$previous_globals = self::get_pre_upgrade_global_modules_snapshot();

			self::apply_attrs_to_globals( $attrs, true );

			if ( $form_id ) {
				self::mark_form_modules_use_global_defaults(
					$form_id,
					array( 'recaptcha', 'cloudflareTurnstile', 'honeypot', 'messages' )
				);
			}

			self::sync_other_forms_after_upgrade( $previous_globals );

			if ( defined( 'GUTENA_FORMS_VERSION' ) ) {
				update_option( 'gutena_forms_version', GUTENA_FORMS_VERSION );
			}

			update_option( self::UPGRADE_190_GLOBALS_FLAG, 1 );

			/**
			 * Fires after 1.9 global settings have been migrated (Pro integrations may listen).
			 *
			 * @since 1.9.0
			 * @param string|false $form_id First form ID used as migration source.
			 */
			do_action( 'gutena_forms_upgrade_globals_complete', $form_id );

			return true;
		}

		/**
		 * Snapshot of 1.9 module globals before upgrade (for sync comparison).
		 *
		 * @since 1.9.0
		 * @return array Keys: recaptcha, cloudflareTurnstile, honeypot, messages.
		 */
		private static function get_pre_upgrade_global_modules_snapshot() {
			return array(
				'recaptcha'           => get_option( 'gutena_forms__recaptcha', array() ),
				'cloudflareTurnstile' => get_option( 'gutena_forms__cloudflare', array() ),
				'honeypot'            => get_option( 'gutena_forms__honeypot', array() ),
				'messages'            => get_option( 'gutena_forms__form_validation_messages', array() ),
				'paymentStripe'       => self::get_payment_stripe_global_settings(),
				'paymentSquare'       => self::get_payment_square_global_settings(),
			);
		}

		/**
		 * Stripe settings slice from gutena_forms__payment_settings.
		 *
		 * @since 2.0.0
		 * @return array
		 */
		public static function get_payment_stripe_global_settings() {
			$all = get_option( 'gutena_forms__payment_settings', array() );
			if ( ! is_array( $all ) || empty( $all['stripe'] ) || ! is_array( $all['stripe'] ) ) {
				return array();
			}

			return self::sanitize_payment_stripe_settings( $all['stripe'] );
		}

		/**
		 * Persist Stripe public settings into the payment settings option.
		 *
		 * @since 2.0.0
		 * @param array $settings Stripe settings slice.
		 * @return void
		 */
		public static function update_payment_stripe_global_settings( $settings ) {
			$all = get_option( 'gutena_forms__payment_settings', array() );
			if ( ! is_array( $all ) ) {
				$all = array();
			}

			$current = isset( $all['stripe'] ) && is_array( $all['stripe'] ) ? $all['stripe'] : array();
			$all['stripe'] = array_merge( $current, self::sanitize_payment_stripe_settings( $settings ) );
			update_option( 'gutena_forms__payment_settings', $all );
		}

		/**
		 * Square settings slice from gutena_forms__payment_settings.
		 *
		 * @since 2.1.0
		 * @return array
		 */
		public static function get_payment_square_global_settings() {
			$all = get_option( 'gutena_forms__payment_settings', array() );
			if ( ! is_array( $all ) || empty( $all['square'] ) || ! is_array( $all['square'] ) ) {
				return array();
			}

			return self::sanitize_payment_square_settings( $all['square'] );
		}

		/**
		 * Persist Square public settings into the payment settings option.
		 *
		 * @since 2.1.0
		 * @param array $settings Square settings slice.
		 * @return void
		 */
		public static function update_payment_square_global_settings( $settings ) {
			$all = get_option( 'gutena_forms__payment_settings', array() );
			if ( ! is_array( $all ) ) {
				$all = array();
			}

			$current = isset( $all['square'] ) && is_array( $all['square'] ) ? $all['square'] : array();
			$all['square'] = array_merge( $current, self::sanitize_payment_square_settings( $settings ) );
			update_option( 'gutena_forms__payment_settings', $all );
		}

		/**
		 * Sanitize Square settings stored on forms or globally.
		 *
		 * @since 2.1.0
		 * @param array $settings Raw settings.
		 * @return array
		 */
		public static function sanitize_payment_square_settings( $settings ) {
			$settings = self::sanitize_settings_for_option( is_array( $settings ) ? $settings : array() );

			$payment_mode = in_array( $settings['payment_mode'] ?? 'test', array( 'live', 'test' ), true )
				? $settings['payment_mode']
				: 'test';

			$locations = class_exists( 'Gutena_Forms_Square_Connect' )
				? Gutena_Forms_Square_Connect::sanitize_locations( $settings['business_locations'] ?? array() )
				: array();

			return array(
				'enable'             => ! empty( $settings['enable'] ),
				'payment_mode'       => $payment_mode,
				'connected'          => ! empty( $settings['connected'] ),
				'account_name'       => sanitize_text_field( $settings['account_name'] ?? '' ),
				'merchant_currency'  => sanitize_text_field( $settings['merchant_currency'] ?? '' ),
				'location_id'        => sanitize_text_field( $settings['location_id'] ?? '' ),
				'business_locations' => $locations,
				'currency'           => sanitize_text_field( $settings['currency'] ?? $settings['merchant_currency'] ?? 'USD' ),
			);
		}

		/**
		 * Sanitize Stripe settings stored on forms or globally.
		 *
		 * @since 2.0.0
		 * @param array $settings Raw settings.
		 * @return array
		 */
		public static function sanitize_payment_stripe_settings( $settings ) {
			$settings = self::sanitize_settings_for_option( is_array( $settings ) ? $settings : array() );

			$payment_mode = in_array( $settings['payment_mode'] ?? 'test', array( 'live', 'test' ), true )
				? $settings['payment_mode']
				: 'test';

			$allowed_positions = array( 'left', 'right', 'left_space', 'right_space' );
			$sign_position     = in_array( $settings['currency_sign_position'] ?? 'left', $allowed_positions, true )
				? $settings['currency_sign_position']
				: 'left';

			return array(
				'enable'                 => ! empty( $settings['enable'] ),
				'payment_mode'           => $payment_mode,
				'currency'               => sanitize_text_field( $settings['currency'] ?? 'USD' ),
				'currency_sign_position' => $sign_position,
				'connected'              => ! empty( $settings['connected'] ),
				'account_name'           => sanitize_text_field( $settings['account_name'] ?? '' ),
				'webhook_connected'      => ! empty( $settings['webhook_connected'] ),
				'webhook_slots_exceeded' => ! empty( $settings['webhook_slots_exceeded'] ),
			);
		}

		/**
		 * Whether a global module option is empty or has no meaningful configuration.
		 *
		 * @since 1.9.0
		 * @param string $module_key Module attr key (recaptcha, cloudflareTurnstile, honeypot, messages).
		 * @return bool
		 */
		public static function is_global_module_empty( $module_key ) {
			if ( 'paymentStripe' === $module_key ) {
				return ! self::is_module_settings_meaningful(
					$module_key,
					self::get_payment_stripe_global_settings()
				);
			}

			if ( 'paymentSquare' === $module_key ) {
				return ! self::is_module_settings_meaningful(
					$module_key,
					self::get_payment_square_global_settings()
				);
			}

			$option_name = self::global_option_name_for_module( $module_key );
			if ( '' === $option_name ) {
				return true;
			}

			$settings = get_option( $option_name, array() );

			return ! self::is_module_settings_meaningful( $module_key, $settings );
		}

		/**
		 * Whether a global integration slice is empty or has no meaningful configuration.
		 *
		 * @since 1.9.0
		 * @param string $integration_id Integration key (mailchimp, brevo, activecampaign).
		 * @return bool
		 */
		public static function is_integration_global_empty( $integration_id ) {
			$integration_id = sanitize_key( $integration_id );
			if ( '' === $integration_id ) {
				return true;
			}

			$all_settings = get_option( 'gutena_forms__integration_settings', array() );
			if ( ! is_array( $all_settings ) || empty( $all_settings[ $integration_id ] ) ) {
				return true;
			}

			return ! self::is_integration_attrs_meaningful( $integration_id, $all_settings[ $integration_id ] );
		}

		/**
		 * Whether form block attrs for a module contain meaningful configuration worth seeding globals.
		 *
		 * @since 1.9.0
		 * @param string $module_key Module attr key.
		 * @param array  $slice      Module settings from block attrs.
		 * @return bool
		 */
		public static function is_module_attrs_meaningful( $module_key, $slice ) {
			return self::is_module_settings_meaningful( $module_key, $slice );
		}

		/**
		 * Whether stored module settings contain meaningful configuration.
		 *
		 * @since 1.9.0
		 * @param string $module_key Module attr key.
		 * @param array  $settings   Settings array.
		 * @return bool
		 */
		public static function is_module_settings_meaningful( $module_key, $settings ) {
			$settings = self::sanitize_settings_for_option( is_array( $settings ) ? $settings : array() );

			switch ( $module_key ) {
				case 'recaptcha':
					if ( ! empty( $settings['enable'] ) ) {
						return true;
					}
					if ( class_exists( 'Gutena_Forms_ReCAPTCHA' ) ) {
						$resolved = Gutena_Forms_ReCAPTCHA::resolve_settings( $settings );
						return ! empty( $resolved['site_key'] ) || ! empty( $resolved['secret_key'] );
					}
					return ! empty( $settings['site_key'] ) || ! empty( $settings['secret_key'] );

				case 'cloudflareTurnstile':
					if ( ! empty( $settings['enable'] ) ) {
						return true;
					}
					return ! empty( $settings['site_key'] ) || ! empty( $settings['secret_key'] );

				case 'honeypot':
					return ! empty( $settings['enable'] ) || ! empty( $settings['timeCheckValue'] );

				case 'messages':
					$message_keys = array(
						'required_msg',
						'required_msg_optin',
						'required_msg_select',
						'required_msg_check',
						'invalid_email_msg',
						'invalid_url_msg',
						'min_value_msg',
						'max_value_msg',
					);
					foreach ( $message_keys as $message_key ) {
						if ( ! empty( $settings[ $message_key ] ) ) {
							return true;
						}
					}
					return false;

				case 'paymentStripe':
					if ( ! empty( $settings['connected'] ) || ! empty( $settings['enable'] ) ) {
						return true;
					}
					if ( ! empty( $settings['payment_mode'] ) && 'test' !== $settings['payment_mode'] ) {
						return true;
					}
					if ( ! empty( $settings['currency'] ) && 'USD' !== $settings['currency'] ) {
						return true;
					}
					return false;

				case 'paymentSquare':
					if ( ! empty( $settings['connected'] ) || ! empty( $settings['enable'] ) ) {
						return true;
					}
					if ( ! empty( $settings['payment_mode'] ) && 'test' !== $settings['payment_mode'] ) {
						return true;
					}
					if ( ! empty( $settings['location_id'] ) ) {
						return true;
					}
					return false;

				default:
					return ! empty( $settings );
			}
		}

		/**
		 * Whether integration block attrs contain meaningful configuration.
		 *
		 * @since 1.9.0
		 * @param string $integration_id Integration key.
		 * @param array  $slice          Integration settings from block attrs.
		 * @return bool
		 */
		public static function is_integration_attrs_meaningful( $integration_id, $slice ) {
			$integration_id = sanitize_key( $integration_id );
			$slice          = self::sanitize_settings_for_option( is_array( $slice ) ? $slice : array() );

			if ( ! empty( $slice['enable'] ) ) {
				return true;
			}

			switch ( $integration_id ) {
				case 'mailchimp':
				case 'brevo':
					return ! empty( $slice['api_key'] ) || ! empty( $slice['list_id'] );
				case 'activecampaign':
					return ! empty( $slice['api_key'] ) || ! empty( $slice['api_url'] );
				default:
					return ! empty( $slice );
			}
		}

		/**
		 * Whether form block attrs may update the global option for a module.
		 *
		 * @since 1.9.0
		 * @param string $module_key    Module attr key.
		 * @param array  $module_settings Module settings from block attrs.
		 * @param bool   $only_if_empty Multi-form seed mode when true.
		 * @return bool
		 */
		public static function should_form_push_module_to_global( $module_key, $module_settings, $only_if_empty ) {
			if ( ! self::is_module_attrs_meaningful( $module_key, $module_settings ) ) {
				return false;
			}

			if ( self::is_global_module_empty( $module_key ) ) {
				return true;
			}

			$uses_global_defaults = ! isset( $module_settings['defaultSettings'] )
				|| rest_sanitize_boolean( $module_settings['defaultSettings'] );

			if ( ! $uses_global_defaults ) {
				return false;
			}

			return ! $only_if_empty;
		}

		/**
		 * Whether form block attrs may update the global option for an integration.
		 *
		 * @since 1.9.0
		 * @param string $integration_id Integration key.
		 * @param array  $slice          Integration settings from block attrs.
		 * @param bool   $only_if_empty Multi-form seed mode when true.
		 * @return bool
		 */
		public static function should_form_push_integration_to_global( $integration_id, $slice, $only_if_empty ) {
			if ( ! self::is_integration_attrs_meaningful( $integration_id, $slice ) ) {
				return false;
			}

			if ( self::is_integration_global_empty( $integration_id ) ) {
				return true;
			}

			$uses_global_defaults = ! isset( $slice['defaultSettings'] )
				|| rest_sanitize_boolean( $slice['defaultSettings'] );

			if ( ! $uses_global_defaults ) {
				return false;
			}

			return ! $only_if_empty;
		}

		/**
		 * Seed globals from form block attrs and sync inheriting forms.
		 *
		 * @since 1.9.0
		 * @param array $attributes Form block attributes.
		 */
		public static function seed_globals_from_form_block( $attributes ) {
			$attributes    = is_array( $attributes ) ? $attributes : array();
			$only_if_empty = ! self::is_single_form_site();

			$previous_modules      = self::get_pre_upgrade_global_modules_snapshot();
			$previous_integrations = get_option( 'gutena_forms__integration_settings', array() );
			if ( ! is_array( $previous_integrations ) ) {
				$previous_integrations = array();
			}

			$seeded = self::apply_attrs_to_globals( $attributes, $only_if_empty );

			foreach ( $seeded['modules'] as $module_key ) {
				if ( 'paymentStripe' === $module_key ) {
					$prev = isset( $previous_modules[ $module_key ] ) ? $previous_modules[ $module_key ] : array();
					$next = self::get_payment_stripe_global_settings();
					self::sync_global_module_to_forms( $module_key, $prev, $next );
					continue;
				}

				if ( 'paymentSquare' === $module_key ) {
					$prev = isset( $previous_modules[ $module_key ] ) ? $previous_modules[ $module_key ] : array();
					$next = self::get_payment_square_global_settings();
					self::sync_global_module_to_forms( $module_key, $prev, $next );
					continue;
				}

				$option_name = self::global_option_name_for_module( $module_key );
				if ( '' === $option_name ) {
					continue;
				}
				$next = get_option( $option_name, array() );
				$prev = isset( $previous_modules[ $module_key ] ) ? $previous_modules[ $module_key ] : array();
				self::sync_global_module_to_forms( $module_key, $prev, $next );
			}

			foreach ( $seeded['integrations'] as $integration_id ) {
				$all_settings = get_option( 'gutena_forms__integration_settings', array() );
				$next         = ( is_array( $all_settings ) && isset( $all_settings[ $integration_id ] ) )
					? $all_settings[ $integration_id ]
					: array();
				$prev         = isset( $previous_integrations[ $integration_id ] )
					? $previous_integrations[ $integration_id ]
					: array();
				self::sync_global_integration_to_forms(
					$integration_id,
					self::sanitize_settings_for_option( $prev ),
					self::sanitize_settings_for_option( $next )
				);
			}
		}

		/**
		 * Write global options from form attrs (only fills empty options when $only_if_empty).
		 *
		 * @since 1.9.0
		 * @param array $attributes Form block attrs.
		 * @param bool  $only_if_empty Only update when target option is empty.
		 * @return array{modules: string[], integrations: string[]} Keys of modules/integrations written.
		 */
		public static function apply_attrs_to_globals( $attributes, $only_if_empty = false ) {
			$attributes = is_array( $attributes ) ? $attributes : array();
			$seeded     = array(
				'modules'      => array(),
				'integrations' => array(),
			);

			if ( ! empty( $attributes['recaptcha'] ) && is_array( $attributes['recaptcha'] ) ) {
				if ( self::should_form_push_module_to_global( 'recaptcha', $attributes['recaptcha'], $only_if_empty ) ) {
					// Keep versioned keys (v2_*/v3_*); do not flatten via resolve_settings.
					update_option(
						'gutena_forms__recaptcha',
						self::sanitize_settings_for_option( $attributes['recaptcha'] )
					);
					$seeded['modules'][] = 'recaptcha';
				}
			}

			if ( ! empty( $attributes['cloudflareTurnstile'] ) && is_array( $attributes['cloudflareTurnstile'] ) ) {
				if ( self::should_form_push_module_to_global( 'cloudflareTurnstile', $attributes['cloudflareTurnstile'], $only_if_empty ) ) {
					update_option(
						'gutena_forms__cloudflare',
						self::sanitize_settings_for_option( $attributes['cloudflareTurnstile'] )
					);
					$seeded['modules'][] = 'cloudflareTurnstile';
				}
			}

			if ( ! empty( $attributes['honeypot'] ) && is_array( $attributes['honeypot'] ) ) {
				if ( self::should_form_push_module_to_global( 'honeypot', $attributes['honeypot'], $only_if_empty ) ) {
					update_option(
						'gutena_forms__honeypot',
						self::sanitize_settings_for_option( $attributes['honeypot'] )
					);
					$seeded['modules'][] = 'honeypot';
				}
			}

			if ( ! empty( $attributes['messages'] ) && is_array( $attributes['messages'] ) ) {
				if ( self::should_form_push_module_to_global( 'messages', $attributes['messages'], $only_if_empty ) ) {
					update_option(
						'gutena_forms__form_validation_messages',
						self::sanitize_settings_for_option( $attributes['messages'] )
					);
					$seeded['modules'][] = 'messages';
				}
			}

			if ( ! empty( $attributes['paymentStripe'] ) && is_array( $attributes['paymentStripe'] ) ) {
				if ( self::should_form_push_module_to_global( 'paymentStripe', $attributes['paymentStripe'], $only_if_empty ) ) {
					self::update_payment_stripe_global_settings(
						self::sanitize_payment_stripe_settings( $attributes['paymentStripe'] )
					);
					$seeded['modules'][] = 'paymentStripe';
				}
			}

			if ( ! empty( $attributes['paymentSquare'] ) && is_array( $attributes['paymentSquare'] ) ) {
				if ( self::should_form_push_module_to_global( 'paymentSquare', $attributes['paymentSquare'], $only_if_empty ) ) {
					self::update_payment_square_global_settings(
						self::sanitize_payment_square_settings( $attributes['paymentSquare'] )
					);
					$seeded['modules'][] = 'paymentSquare';
				}
			}

			if (
				! empty( $attributes['settings']['integration'] )
				&& is_array( $attributes['settings']['integration'] )
			) {
				$integration_option = get_option( 'gutena_forms__integration_settings', array() );
				if ( ! is_array( $integration_option ) ) {
					$integration_option = array();
				}
				$changed = false;
				foreach ( array( 'mailchimp', 'brevo', 'activecampaign' ) as $integration_id ) {
					if (
						empty( $attributes['settings']['integration'][ $integration_id ] )
						|| ! is_array( $attributes['settings']['integration'][ $integration_id ] )
					) {
						continue;
					}
					$slice = $attributes['settings']['integration'][ $integration_id ];
					if ( ! self::should_form_push_integration_to_global( $integration_id, $slice, $only_if_empty ) ) {
						continue;
					}
					$integration_option[ $integration_id ] = self::sanitize_settings_for_option( $slice );
					$seeded['integrations'][]              = $integration_id;
					$changed                               = true;
				}
				if ( $changed ) {
					update_option( 'gutena_forms__integration_settings', $integration_option );
				}
			}

			return $seeded;
		}

		/**
		 * Set defaultSettings on the migration source form block for modules now stored globally.
		 *
		 * @since 1.9.0
		 * @param string $form_id Form ID.
		 * @param array  $module_keys Module attr keys to mark.
		 */
		private static function mark_form_modules_use_global_defaults( $form_id, $module_keys ) {
			$post = self::get_form_post_by_form_id( $form_id );
			if ( ! $post || empty( $post->post_content ) ) {
				return;
			}

			$blocks  = parse_blocks( $post->post_content );
			$updated = false;

			foreach ( $blocks as $index => $block ) {
				if ( empty( $block['blockName'] ) || 'gutena/forms' !== $block['blockName'] ) {
					continue;
				}
				if ( empty( $block['attrs']['formID'] ) || sanitize_key( $block['attrs']['formID'] ) !== sanitize_key( $form_id ) ) {
					continue;
				}

				$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();

				foreach ( $module_keys as $module_key ) {
					$global = get_option( self::global_option_name_for_module( $module_key ), array() );
					if ( empty( $global ) || ! is_array( $global ) ) {
						continue;
					}
					$global_with_default                    = $global;
					$global_with_default['defaultSettings'] = true;
					$attrs[ $module_key ]                   = $global_with_default;
				}

				$integration_global = get_option( 'gutena_forms__integration_settings', array() );
				if ( ! empty( $integration_global ) && is_array( $integration_global ) ) {
					if ( ! isset( $attrs['settings'] ) || ! is_array( $attrs['settings'] ) ) {
						$attrs['settings'] = array();
					}
					if ( ! isset( $attrs['settings']['integration'] ) || ! is_array( $attrs['settings']['integration'] ) ) {
						$attrs['settings']['integration'] = array();
					}
					foreach ( array( 'mailchimp', 'brevo', 'activecampaign' ) as $integration_id ) {
						if ( empty( $integration_global[ $integration_id ] ) ) {
							continue;
						}
						$slice = $integration_global[ $integration_id ];
						$slice['defaultSettings'] = true;
						$attrs['settings']['integration'][ $integration_id ] = $slice;
					}
				}

				$blocks[ $index ]['attrs'] = $attrs;
				$updated                   = true;
			}

			if ( ! $updated ) {
				return;
			}

			$new_content = '';
			foreach ( $blocks as $block ) {
				$new_content .= serialize_block( $block );
			}

			wp_update_post(
				array(
					'ID'           => $post->ID,
					'post_content' => $new_content,
				),
				false,
				false
			);
		}

		/**
		 * Map module attr key to global option name.
		 *
		 * @since 1.9.0
		 * @param string $module_key Module key.
		 * @return string
		 */
		private static function global_option_name_for_module( $module_key ) {
			$map = array(
				'recaptcha'           => 'gutena_forms__recaptcha',
				'cloudflareTurnstile' => 'gutena_forms__cloudflare',
				'honeypot'            => 'gutena_forms__honeypot',
				'messages'            => 'gutena_forms__form_validation_messages',
			);

			return isset( $map[ $module_key ] ) ? $map[ $module_key ] : '';
		}

		/**
		 * Promote globals to other forms that matched pre-upgrade globals or use defaults.
		 *
		 * @since 1.9.0
		 * @param array $previous_globals Snapshot before upgrade.
		 */
		private static function sync_other_forms_after_upgrade( $previous_globals ) {
			$modules = array(
				'recaptcha'           => 'gutena_forms__recaptcha',
				'cloudflareTurnstile' => 'gutena_forms__cloudflare',
				'honeypot'            => 'gutena_forms__honeypot',
				'messages'            => 'gutena_forms__form_validation_messages',
				'paymentStripe'       => 'gutena_forms__payment_settings',
				'paymentSquare'       => 'gutena_forms__payment_settings',
			);

			foreach ( $modules as $module_key => $option_name ) {
				if ( 'paymentStripe' === $module_key ) {
					$next = self::get_payment_stripe_global_settings();
					if ( empty( $next ) ) {
						continue;
					}
					$prev = isset( $previous_globals[ $module_key ] ) ? $previous_globals[ $module_key ] : array();
					self::sync_global_module_to_forms( $module_key, $prev, $next, false );
					continue;
				}

				if ( 'paymentSquare' === $module_key ) {
					$next = self::get_payment_square_global_settings();
					if ( empty( $next ) ) {
						continue;
					}
					$prev = isset( $previous_globals[ $module_key ] ) ? $previous_globals[ $module_key ] : array();
					self::sync_global_module_to_forms( $module_key, $prev, $next, false );
					continue;
				}

				$next = get_option( $option_name, array() );
				if ( empty( $next ) || ! is_array( $next ) ) {
					continue;
				}
				$prev = isset( $previous_globals[ $module_key ] ) ? $previous_globals[ $module_key ] : array();
				// Upgrade: do not treat missing defaultSettings as "use global" (1.8 forms omit the flag).
				self::sync_global_module_to_forms( $module_key, $prev, $next, false );
			}

			$integration_global = get_option( 'gutena_forms__integration_settings', array() );
			if ( ! empty( $integration_global ) && is_array( $integration_global ) ) {
				foreach ( array( 'mailchimp', 'brevo', 'activecampaign' ) as $integration_id ) {
					if ( empty( $integration_global[ $integration_id ] ) ) {
						continue;
					}
					self::sync_global_integration_to_forms(
						$integration_id,
						array(),
						self::sanitize_settings_for_option( $integration_global[ $integration_id ] ),
						false
					);
				}
			}
		}

		/**
		 * Sync module settings to all form blocks using global defaults.
		 * Also recovers forms that were previously in single-form mode and have
		 * stale explicit settings equal to old globals.
		 *
		 * @since 1.8.0
		 * @param string $module_key Module key inside form attrs.
		 * @param array  $previous_global Previous global settings.
		 * @param array  $next_global New global settings.
		 * @param bool   $missing_default_means_use_global When true (admin save), missing defaultSettings inherits globals; when false (upgrade), only explicit defaultSettings or a match to previous globals.
		 */
		public static function sync_global_module_to_forms( $module_key, $previous_global, $next_global, $missing_default_means_use_global = true ) {
			$posts = get_posts(
				array(
					'posts_per_page' => -1,
					'post_type'      => 'gutena_forms',
					'post_status'    => array( 'publish', 'draft', 'private' ),
				)
			);

			if ( empty( $posts ) || ! is_array( $posts ) ) {
				return;
			}

			foreach ( $posts as $post ) {
				if ( empty( $post ) || empty( $post->post_content ) ) {
					continue;
				}

				$blocks  = parse_blocks( $post->post_content );
				$updated = false;

				foreach ( $blocks as $index => $block ) {
					if ( empty( $block['blockName'] ) || 'gutena/forms' !== $block['blockName'] ) {
						continue;
					}

					$attrs           = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
					$module_settings = isset( $attrs[ $module_key ] ) && is_array( $attrs[ $module_key ] ) ? $attrs[ $module_key ] : array();
					if ( $missing_default_means_use_global ) {
						$use_global = ! isset( $module_settings['defaultSettings'] ) || false !== rest_sanitize_boolean( $module_settings['defaultSettings'] );
					} else {
						$use_global = isset( $module_settings['defaultSettings'] ) && rest_sanitize_boolean( $module_settings['defaultSettings'] );
					}

					$module_without_default = self::sanitize_settings_for_option( $module_settings );
					$matched_old_global     = self::settings_equal( $module_without_default, $previous_global );
					$should_promote_global  = $use_global || $matched_old_global;

					if ( ! $should_promote_global ) {
						continue;
					}

					$attrs[ $module_key ]                    = is_array( $next_global ) ? $next_global : array();
					$attrs[ $module_key ]['defaultSettings'] = true;
					$blocks[ $index ]['attrs']               = $attrs;
					$updated                                 = true;
				}

				if ( ! $updated ) {
					continue;
				}

				$new_content = '';
				foreach ( $blocks as $block ) {
					$new_content .= serialize_block( $block );
				}

				wp_update_post(
					array(
						'ID'           => $post->ID,
						'post_content' => $new_content,
					),
					false,
					false
				);
			}
		}

		/**
		 * Sync integration settings under attrs.settings.integration to all form blocks using global defaults.
		 *
		 * @since 1.8.0
		 * @param string $integration_id Integration key (e.g. mailchimp, brevo, activecampaign).
		 * @param array  $previous_global Previous global settings for this integration (sanitized).
		 * @param array  $next_global New global settings for this integration (sanitized, no defaultSettings).
		 * @param bool   $missing_default_means_use_global Same semantics as sync_global_module_to_forms().
		 */
		public static function sync_global_integration_to_forms( $integration_id, $previous_global, $next_global, $missing_default_means_use_global = true ) {
			$integration_id = sanitize_key( $integration_id );
			if ( '' === $integration_id ) {
				return;
			}

			$posts = get_posts(
				array(
					'posts_per_page' => -1,
					'post_type'      => 'gutena_forms',
					'post_status'    => array( 'publish', 'draft', 'private' ),
				)
			);

			if ( empty( $posts ) || ! is_array( $posts ) ) {
				return;
			}

			foreach ( $posts as $post ) {
				if ( empty( $post ) || empty( $post->post_content ) ) {
					continue;
				}

				$blocks  = parse_blocks( $post->post_content );
				$updated = false;

				foreach ( $blocks as $index => $block ) {
					if ( empty( $block['blockName'] ) || 'gutena/forms' !== $block['blockName'] ) {
						continue;
					}

					$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
					if ( ! isset( $attrs['settings'] ) || ! is_array( $attrs['settings'] ) ) {
						$attrs['settings'] = array();
					}
					if ( ! isset( $attrs['settings']['integration'] ) || ! is_array( $attrs['settings']['integration'] ) ) {
						$attrs['settings']['integration'] = array();
					}

					$module_settings = isset( $attrs['settings']['integration'][ $integration_id ] ) && is_array( $attrs['settings']['integration'][ $integration_id ] )
						? $attrs['settings']['integration'][ $integration_id ]
						: array();
					if ( $missing_default_means_use_global ) {
						$use_global = ! isset( $module_settings['defaultSettings'] ) || false !== rest_sanitize_boolean( $module_settings['defaultSettings'] );
					} else {
						$use_global = isset( $module_settings['defaultSettings'] ) && rest_sanitize_boolean( $module_settings['defaultSettings'] );
					}

					$module_without_default = self::sanitize_settings_for_option( $module_settings );
					$matched_old_global     = self::settings_equal( $module_without_default, $previous_global );
					$should_promote_global  = $use_global || $matched_old_global;

					if ( ! $should_promote_global ) {
						continue;
					}

					$attrs['settings']['integration'][ $integration_id ]                    = is_array( $next_global ) ? $next_global : array();
					$attrs['settings']['integration'][ $integration_id ]['defaultSettings'] = true;
					$blocks[ $index ]['attrs']                                              = $attrs;
					$updated                                                                = true;
				}

				if ( ! $updated ) {
					continue;
				}

				$new_content = '';
				foreach ( $blocks as $block ) {
					$new_content .= serialize_block( $block );
				}

				wp_update_post(
					array(
						'ID'           => $post->ID,
						'post_content' => $new_content,
					),
					false,
					false
				);
			}
		}

		/**
		 * Compare settings arrays ignoring key order and defaultSettings marker.
		 *
		 * @param array $left First settings array.
		 * @param array $right Second settings array.
		 * @return bool
		 */
		private static function settings_equal( $left, $right ) {
			$left  = self::sanitize_settings_for_option( $left );
			$right = self::sanitize_settings_for_option( $right );
			self::sort_recursive( $left );
			self::sort_recursive( $right );
			return wp_json_encode( $left ) === wp_json_encode( $right );
		}

		/**
		 * Recursively sort arrays for deterministic comparison.
		 *
		 * @param mixed $value Value to sort.
		 */
		private static function sort_recursive( &$value ) {
			if ( ! is_array( $value ) ) {
				return;
			}

			foreach ( $value as &$child ) {
				if ( is_array( $child ) ) {
					self::sort_recursive( $child );
				}
			}

			if ( array_keys( $value ) !== range( 0, count( $value ) - 1 ) ) {
				ksort( $value );
			}
		}

		/**
		 * Run 1.9 upgrade and optional single-form sync from block attrs.
		 *
		 * @since 1.8.0
		 */
		public function fetch_all_gutena_forms() {
			if ( ! is_admin() ) {
				return;
			}

			self::upgrade_globals_to_1_9();

			if ( ! self::is_single_form_site() ) {
				return;
			}

			$form_id = self::get_first_form_id();
			if ( ! $form_id ) {
				return;
			}

			$attrs = self::get_form_attrs_for_migration( $form_id );
			if ( empty( $attrs ) ) {
				return;
			}

			// Case 3: fill any globals still empty from the single form block.
			self::apply_attrs_to_globals( $attrs, true );
		}

		/**
		 * Post contains at least one gutena/forms block.
		 *
		 * @since 1.9.0
		 * @param WP_Post $post WP Post object.
		 * @return bool
		 */
		private function post_has_gutena_form_block( $post ) {
			$gutena_blocks = $this->has_gutena_blocks( $post->post_content );

			return is_array( $gutena_blocks ) && count( $gutena_blocks ) >= 1;
		}

		/**
		 * Has gutena blocks
		 *
		 * @since 1.8.0
		 * @param array|string $blocks Contains array of blocks.
		 *
		 * @return array|false
		 */
		private function has_gutena_blocks( $blocks ) {
			$blocks = is_string( $blocks ) ? parse_blocks( $blocks ) : $blocks;

			$blocks = array_filter(
				$blocks,
				function ( $block ) {
					return 'gutena/forms' === $block['blockName'];
				}
			);

			return ! empty( $blocks ) ? array_values( $blocks ) : false;
		}
	}

	Gutena_Forms_Settings_Migrator::get_instance();
endif;
