<?php
/**
 * Square field block frontend rendering.
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Square_Field_Block' ) ) :
	/**
	 * Square payment field block.
	 */
	class Gutena_Forms_Square_Field_Block {
		/**
		 * Instance.
		 *
		 * @var Gutena_Forms_Square_Field_Block|null
		 */
		private static $instance = null;

		/**
		 * Get instance.
		 *
		 * @return Gutena_Forms_Square_Field_Block
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Track whether Square SDK script was enqueued.
		 *
		 * @var bool
		 */
		private static $square_script_enqueued = false;

		/**
		 * Render Square field on the frontend.
		 *
		 * @param array    $attributes Block attributes.
		 * @param string   $content    Saved block content.
		 * @param WP_Block $block      Block instance.
		 * @return string
		 */
		public function render_block( $attributes, $content, $block ) {
			if ( is_admin() ) {
				return $content;
			}

			if ( ! gutena_forms_is_square_gateway_enabled() ) {
				if ( current_user_can( 'manage_options' ) ) {
					return '<div class="gutena-forms-admin-notice" style="padding:12px 16px; background:#fff3cd; color:#856404; border:1px solid #ffeeba; border-radius:4px; font-size:13px; margin:15px 0;"><strong>Gutena Forms:</strong> ' . esc_html__( 'Square Payment Field is not rendering because the Square payment gateway is currently disabled in Gutena Forms → Settings → Payment Methods.', 'gutena-forms' ) . '</div>';
				}
				return '';
			}

			$form_id = '';
			if ( is_object( $block ) && ! empty( $block->context['gutena-forms/formID'] ) ) {
				$form_id = sanitize_key( $block->context['gutena-forms/formID'] );
			}

			$payment_square = $this->resolve_form_payment_square( $form_id );
			if ( empty( $payment_square['connected'] ) ) {
				if ( current_user_can( 'manage_options' ) ) {
					return '<div class="gutena-forms-admin-notice" style="padding:12px 16px; background:#fff3cd; color:#856404; border:1px solid #ffeeba; border-radius:4px; font-size:13px; margin:15px 0;"><strong>Gutena Forms:</strong> ' . esc_html__( 'Square Payment Field is not rendering because your Square account is not connected. Please connect your Square account in Gutena Forms → Settings → Square or in the form sidebar.', 'gutena-forms' ) . '</div>';
				}
				return '';
			}

			$attributes   = is_array( $attributes ) ? $attributes : array();
			$field_id     = ! empty( $attributes['nameAttr'] ) ? sanitize_key( $attributes['nameAttr'] ) : 'square_payment';
			$field_name   = ! empty( $attributes['fieldName'] ) ? $attributes['fieldName'] : __( 'Credit Card', 'gutena-forms' );
			$payment_type = ! empty( $attributes['paymentType'] ) ? sanitize_key( $attributes['paymentType'] ) : 'one_time';
			$amount_type  = ! empty( $attributes['amountType'] ) ? sanitize_key( $attributes['amountType'] ) : 'fixed';
			$fixed_amount = isset( $attributes['fixedAmount'] ) ? floatval( $attributes['fixedAmount'] ) : 0;
			$variable_field = ! empty( $attributes['variableAmountField'] ) ? sanitize_key( $attributes['variableAmountField'] ) : '';
			$minimum_amount = isset( $attributes['minimumAmount'] ) ? floatval( $attributes['minimumAmount'] ) : 0;
			$subscription_plan = ! empty( $attributes['subscriptionPlanName'] ) ? $attributes['subscriptionPlanName'] : '';
			$billing_interval  = ! empty( $attributes['billingInterval'] ) ? sanitize_key( $attributes['billingInterval'] ) : 'monthly';
			$billing_cycles    = ! empty( $attributes['billingCycles'] ) ? sanitize_key( $attributes['billingCycles'] ) : 'never';
			$custom_billing_cycles = isset( $attributes['customBillingCycles'] ) ? absint( $attributes['customBillingCycles'] ) : 1;
			$currency     = sanitize_text_field( $payment_square['merchant_currency'] ?? 'USD' );
			$payment_mode = sanitize_key( $payment_square['payment_mode'] ?? 'test' );
			$location_id  = sanitize_text_field( $payment_square['location_id'] ?? '' );
			$account_name = ! empty( $payment_square['account_name'] )
				? $payment_square['account_name']
				: __( 'the merchant', 'gutena-forms' );
			$app_id       = class_exists( 'Gutena_Forms_Square_Connect' )
				? Gutena_Forms_Square_Connect::get_application_id( $payment_mode )
				: '';

			$this->enqueue_square_js( $payment_mode );

			$amount_hint = $this->get_amount_hint(
				$currency,
				$amount_type,
				$fixed_amount,
				$payment_type,
				array(
					'billingInterval'     => $billing_interval,
					'billingCycles'       => $billing_cycles,
					'customBillingCycles' => $custom_billing_cycles,
				)
			);
			$show_subscription_notice = ( 'subscription' === $payment_type );
			$subscription_notice      = $show_subscription_notice
				? $this->get_subscription_authorization_notice( $account_name, $billing_cycles, $custom_billing_cycles )
				: '';

			$square_field_config = wp_json_encode(
				array(
					'nameAttr'             => $field_id,
					'blockName'            => 'gutena/square-field',
					'fieldType'            => 'square',
					'paymentType'          => $payment_type,
					'amountType'           => $amount_type,
					'fixedAmount'          => $fixed_amount,
					'variableAmountField'  => $variable_field,
					'minimumAmount'        => $minimum_amount,
					'customerEmailField'   => ! empty( $attributes['customerEmailField'] ) ? sanitize_key( $attributes['customerEmailField'] ) : '',
					'customerNameField'    => ! empty( $attributes['customerNameField'] ) ? sanitize_key( $attributes['customerNameField'] ) : '',
					'subscriptionPlanName' => $subscription_plan,
					'billingInterval'      => $billing_interval,
					'billingCycles'        => $billing_cycles,
					'customBillingCycles'  => $custom_billing_cycles,
				)
			);

			ob_start();
			?>
			<div
				class="wp-block-gutena-field-group wp-block-gutena-square-field field-group-type-square standalone-square-field<?php echo 'subscription' === $payment_type ? ' is-subscription-payment' : ''; ?>"
				data-square-field="<?php echo esc_attr( $field_id ); ?>"
				data-square-field-id="<?php echo esc_attr( $field_id ); ?>"
				data-square-application-id="<?php echo esc_attr( $app_id ); ?>"
				data-square-location-id="<?php echo esc_attr( $location_id ); ?>"
				data-square-payment-mode="<?php echo esc_attr( $payment_mode ); ?>"
				data-square-payment-type="<?php echo esc_attr( $payment_type ); ?>"
				data-square-amount-type="<?php echo esc_attr( $amount_type ); ?>"
				data-square-fixed-amount="<?php echo esc_attr( $fixed_amount ); ?>"
				data-square-minimum-amount="<?php echo esc_attr( $minimum_amount ); ?>"
				data-square-subscription-plan="<?php echo esc_attr( $subscription_plan ); ?>"
				data-square-billing-interval="<?php echo esc_attr( $billing_interval ); ?>"
				data-square-billing-cycles="<?php echo esc_attr( $billing_cycles ); ?>"
				data-square-custom-billing-cycles="<?php echo esc_attr( $custom_billing_cycles ); ?>"
				data-square-variable-amount-field="<?php echo esc_attr( $variable_field ); ?>"
				data-square-currency="<?php echo esc_attr( $currency ); ?>"
			>
				<label class="heading-input-label-gutena gutena-forms-square-payment__heading" for="<?php echo esc_attr( 'gutena-square-payment-' . $field_id ); ?>">
					<?php echo esc_html( $field_name ); ?>
				</label>

				<?php if ( 'subscription' === $payment_type ) : ?>
					<div class="gutena-forms-square-payment__subscription-details">
						<span class="gutena-forms-square-payment__subscription-badge"><?php esc_html_e( 'Subscription', 'gutena-forms' ); ?></span>
						<?php if ( '' !== $subscription_plan ) : ?>
							<p class="gutena-forms-square-payment__plan-name"><?php echo esc_html( $subscription_plan ); ?></p>
						<?php endif; ?>
						<p class="gutena-forms-square-payment__amount-hint gutena-forms-square-payment__subscription-summary"><?php echo esc_html( $amount_hint ); ?></p>
					</div>
				<?php else : ?>
					<p class="gutena-forms-square-payment__amount-hint"><?php echo esc_html( $amount_hint ); ?></p>
				<?php endif; ?>

				<div class="gutena-forms-square-payment__panel">
					<?php echo self::render_payment_chrome(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

					<div class="gutena-forms-square-payment__fields">
						<div class="gutena-forms-square-payment__field gutena-forms-square-payment__field--card">
							<div class="gutena-forms-square-payment__header-row">
								<label class="gutena-forms-square-payment__label" for="<?php echo esc_attr( 'gutena-square-payment-' . $field_id ); ?>">
									<?php esc_html_e( 'Card details', 'gutena-forms' ); ?>
								</label>
								<?php echo self::render_card_brand_icons(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
							<div
								id="<?php echo esc_attr( 'gutena-square-payment-' . $field_id ); ?>"
								class="gutena-forms-square-payment__container"
								data-square-payment-field="<?php echo esc_attr( $field_id ); ?>"
							></div>
						</div>

						<?php if ( $show_subscription_notice && '' !== $subscription_notice ) : ?>
							<p class="gutena-forms-square-payment__subscription-notice"><?php echo esc_html( $subscription_notice ); ?></p>
						<?php endif; ?>
					</div>
				</div>

				<p class="gutena-forms-field-error-msg gutena-forms-square-payment__error"></p>
				<input type="hidden" name="<?php echo esc_attr( $field_id . '_payment_token' ); ?>" class="gutena-forms-square-payment__token-input" value="" />
				<input type="hidden" name="<?php echo esc_attr( $field_id . '_square_config' ); ?>" value="<?php echo esc_attr( $square_field_config ); ?>" class="gutena-forms-square-payment__config-input" />
			</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * Card tab chrome matching embedded card UI.
		 *
		 * @return string
		 */
		private static function render_payment_chrome() {
			ob_start();
			?>
			<div class="gutena-forms-square-payment__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Payment method', 'gutena-forms' ); ?>">
				<div class="gutena-forms-square-payment__tab is-active" role="tab" aria-selected="true" tabindex="0">
					<span class="gutena-forms-square-payment__tab-icon" aria-hidden="true">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="3.5" width="14" height="9" rx="1.5" stroke="currentColor" stroke-width="1.25"/><path d="M1 6.5H15" stroke="currentColor" stroke-width="1.25"/></svg>
					</span>
					<span class="gutena-forms-square-payment__tab-label"><?php esc_html_e( 'Card', 'gutena-forms' ); ?></span>
				</div>
			</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * Accepted card brand icons for the card field.
		 *
		 * @return string
		 */
		private static function render_card_brand_icons() {
			ob_start();
			?>
			<div class="gutena-forms-square-payment__brand-icons" aria-hidden="true">
				<span class="gutena-forms-square-payment__brand-icon gutena-forms-square-payment__brand-icon--mastercard">
					<svg width="24" height="16" viewBox="0 0 24 16" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="24" height="16" rx="2" fill="#fff"/><circle cx="9" cy="8" r="4.5" fill="#EB001B"/><circle cx="15" cy="8" r="4.5" fill="#F79E1B"/><path d="M12 4.82C13.08 5.78 13.8 6.78 13.8 8C13.8 9.22 13.08 10.22 12 11.18C10.92 10.22 10.2 9.22 10.2 8C10.2 6.78 10.92 5.78 12 4.82Z" fill="#FF5F00"/></svg>
				</span>
				<span class="gutena-forms-square-payment__brand-icon gutena-forms-square-payment__brand-icon--visa">
					<svg width="24" height="16" viewBox="0 0 24 16" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="24" height="16" rx="2" fill="#fff"/><path d="M9.82 10.52L10.74 5.72H12.32L11.4 10.52H9.82ZM17.48 5.84C17.14 5.72 16.58 5.6 15.88 5.6C14.12 5.6 12.92 6.52 12.9 7.72C12.88 8.56 13.68 9.04 14.28 9.32C14.9 9.62 15.1 9.8 15.1 10.06C15.08 10.46 14.62 10.66 14.18 10.66C13.52 10.66 13.16 10.54 12.68 10.32L12.48 10.22L12.26 11.44C12.66 11.6 13.36 11.72 14.08 11.74C16 11.74 17.18 10.84 17.2 9.56C17.22 8.86 16.76 8.34 15.76 7.88C15.22 7.6 14.88 7.44 14.88 7.16C14.9 6.92 15.16 6.66 15.78 6.66C16.32 6.64 16.72 6.76 17.02 6.9L17.16 6.98L17.48 5.84ZM20.2 5.72H18.88C18.48 5.72 18.16 5.84 17.98 6.28L15.56 10.52H17.26C17.26 10.52 17.56 9.76 17.64 9.54C17.86 9.54 19.72 9.54 20.04 9.54C20.1 9.82 20.26 10.52 20.26 10.52H21.74L20.2 5.72ZM7.16 5.72L5.56 8.96L5.4 8.18C5.04 7.14 4.06 6.56 2.92 6.56H2.92L2.88 6.76C4.18 7.06 5.18 7.86 5.58 8.96L6.78 10.52H8.48L10.66 5.72H7.16Z" fill="#172B85"/></svg>
				</span>
				<span class="gutena-forms-square-payment__brand-icon gutena-forms-square-payment__brand-icon--amex">
					<svg width="24" height="16" viewBox="0 0 24 16" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="24" height="16" rx="2" fill="#1F72CD"/><path d="M3.2 7.08L2.4 5.72H1.6L3.36 8.92V10.52H4.08V8.92L5.84 5.72H5.08L4.28 7.08L3.48 5.72H2.72L3.2 7.08ZM6.56 10.52H7.28V5.72H6.56V10.52ZM9.04 10.52H11.44V9.92H9.76V8.48H11.28V7.88H9.76V6.32H11.44V5.72H9.04V10.52ZM12.16 10.52H14.72L15.04 9.72H16.72L17.04 10.52H17.84L16.32 5.72H15.44L13.92 10.52H12.16ZM15.24 9.16L15.88 7.28L16.52 9.16H15.24ZM18.48 10.52H21.28C21.76 10.52 22.12 10.4 22.36 10.12C22.56 9.88 22.64 9.6 22.64 9.24V6.96C22.64 6.6 22.56 6.32 22.36 6.08C22.12 5.8 21.76 5.68 21.28 5.68H18.48V10.52ZM19.2 6.28H21.16C21.4 6.28 21.56 6.32 21.64 6.4C21.72 6.48 21.76 6.64 21.76 6.88V9.32C21.76 9.56 21.72 9.72 21.64 9.8C21.56 9.88 21.4 9.92 21.16 9.92H19.2V6.28Z" fill="#fff"/></svg>
				</span>
				<span class="gutena-forms-square-payment__brand-icon gutena-forms-square-payment__brand-icon--discover">
					<svg width="24" height="16" viewBox="0 0 24 16" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="24" height="16" rx="2" fill="#fff"/><rect x="0.5" y="0.5" width="23" height="15" rx="1.5" stroke="#E6E6E6"/><circle cx="13.5" cy="8" r="3.5" fill="#F47216"/><path d="M5.2 6.4H6.48C7.28 6.4 7.84 6.96 7.84 7.68C7.84 8.4 7.28 8.96 6.48 8.96H6.08V10.08H5.2V6.4ZM6.08 8.24H6.4C6.8 8.24 7.04 8 7.04 7.68C7.04 7.36 6.8 7.12 6.4 7.12H6.08V8.24Z" fill="#111"/><path d="M8.4 6.4H9.28V10.08H8.4V6.4Z" fill="#111"/><path d="M9.84 8.24C9.84 7.2 10.64 6.32 11.84 6.32C12.08 6.32 12.28 6.36 12.48 6.4V7.28C12.28 7.2 12.04 7.16 11.84 7.16C11.28 7.16 10.8 7.64 10.8 8.24C10.8 8.84 11.28 9.32 11.84 9.32C12.04 9.32 12.28 9.28 12.48 9.2V10.08C12.28 10.12 12.08 10.16 11.84 10.16C10.64 10.16 9.84 9.28 9.84 8.24Z" fill="#111"/><path d="M16.8 6.48C17.84 6.48 18.56 7.08 18.56 8C18.56 8.92 17.84 9.52 16.8 9.52C15.76 9.52 15.04 8.92 15.04 8C15.04 7.08 15.76 6.48 16.8 6.48ZM16.8 7.2C16.24 7.2 15.84 7.56 15.84 8C15.84 8.44 16.24 8.8 16.8 8.8C17.36 8.8 17.76 8.44 17.76 8C17.76 7.56 17.36 7.2 16.8 7.2Z" fill="#111"/></svg>
				</span>
			</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * Subscription authorization notice shown above the card fields.
		 *
		 * @param string $account_name          Connected Square account name.
		 * @param string $billing_cycles        Billing cycles key.
		 * @param int    $custom_billing_cycles Custom payment count.
		 * @return string
		 */
		private function get_subscription_authorization_notice( $account_name, $billing_cycles, $custom_billing_cycles ) {
			if ( 'never' === sanitize_key( $billing_cycles ) ) {
				return sprintf(
					/* translators: %s: connected Square account name */
					__( 'By subscribing, you authorise %s to charge you according to the terms until you cancel.', 'gutena-forms' ),
					$account_name
				);
			}

			$payment_count = 0;
			if ( in_array( $billing_cycles, array( '2', '3', '4', '5' ), true ) ) {
				$payment_count = (int) $billing_cycles;
			} elseif ( 'custom' === $billing_cycles ) {
				$payment_count = max( 0, (int) $custom_billing_cycles );
			}

			if ( $payment_count > 0 ) {
				return sprintf(
					/* translators: 1: connected Square account name, 2: number of payments */
					__( 'By subscribing, you authorise %1$s to charge you for %2$d scheduled payments according to the terms above.', 'gutena-forms' ),
					$account_name,
					$payment_count
				);
			}

			return sprintf(
				/* translators: %s: connected Square account name */
				__( 'By subscribing, you authorise %s to charge you according to the terms above.', 'gutena-forms' ),
				$account_name
			);
		}

		/**
		 * Initial amount hint text.
		 *
		 * @param string $currency          Currency.
		 * @param string $amount_type       Amount type.
		 * @param float  $fixed_amount      Fixed amount.
		 * @param string $payment_type      Payment type.
		 * @param array  $subscription_args Subscription args.
		 * @return string
		 */
		private function get_amount_hint( $currency, $amount_type, $fixed_amount, $payment_type, $subscription_args = array() ) {
			if ( 'subscription' === $payment_type ) {
				return $this->get_subscription_amount_hint(
					$currency,
					$fixed_amount,
					$subscription_args['billingInterval'] ?? 'monthly',
					$subscription_args['billingCycles'] ?? 'never',
					$subscription_args['customBillingCycles'] ?? 1
				);
			}

			if ( 'variable' === $amount_type ) {
				return __( 'Complete the form to view the amount.', 'gutena-forms' );
			}

			if ( $fixed_amount > 0 ) {
				return $this->format_amount( $currency, $fixed_amount );
			}

			return '';
		}

		/**
		 * Subscription billing summary.
		 *
		 * @param string $currency              Currency.
		 * @param float  $fixed_amount          Fixed amount.
		 * @param string $billing_interval      Interval key.
		 * @param string $billing_cycles        Cycles key.
		 * @param int    $custom_billing_cycles Custom cycles.
		 * @return string
		 */
		private function get_subscription_amount_hint( $currency, $fixed_amount, $billing_interval, $billing_cycles, $custom_billing_cycles ) {
			if ( $fixed_amount <= 0 ) {
				return '';
			}

			$interval_phrases = array(
				'daily'     => __( 'every day', 'gutena-forms' ),
				'weekly'    => __( 'every week', 'gutena-forms' ),
				'monthly'   => __( 'every month', 'gutena-forms' ),
				'quarterly' => __( 'every 3 months', 'gutena-forms' ),
				'yearly'    => __( 'every year', 'gutena-forms' ),
			);

			$amount_text     = $this->format_amount( $currency, $fixed_amount );
			$interval_phrase = $interval_phrases[ $billing_interval ] ?? $billing_interval;

			if ( 'never' === $billing_cycles ) {
				return sprintf(
					/* translators: 1: formatted amount, 2: billing interval phrase */
					__( '%1$s %2$s (until cancelled)', 'gutena-forms' ),
					$amount_text,
					$interval_phrase
				);
			}

			$payment_count = 0;
			if ( in_array( $billing_cycles, array( '2', '3', '4', '5' ), true ) ) {
				$payment_count = (int) $billing_cycles;
			} elseif ( 'custom' === $billing_cycles ) {
				$payment_count = max( 0, (int) $custom_billing_cycles );
			}

			if ( $payment_count > 0 ) {
				return sprintf(
					/* translators: 1: formatted amount, 2: billing interval phrase, 3: number of payments */
					__( '%1$s %2$s · %3$d payments', 'gutena-forms' ),
					$amount_text,
					$interval_phrase,
					$payment_count
				);
			}

			return $amount_text . ' ' . $interval_phrase;
		}

		/**
		 * Format currency amount for display.
		 *
		 * @param string $currency Currency code.
		 * @param float  $amount   Amount.
		 * @return string
		 */
		private function format_amount( $currency, $amount ) {
			$symbols = array(
				'USD' => '$',
				'EUR' => '€',
				'GBP' => '£',
				'AUD' => '$',
				'CAD' => '$',
				'INR' => '₹',
				'BDT' => '৳',
				'JPY' => '¥',
				'BRL' => 'R$',
				'MYR' => 'RM',
				'SGD' => '$',
				'HKD' => '$',
				'NZD' => '$',
				'MXN' => '$',
				'TWD' => '$',
				'CHF' => 'CHF',
				'TRY' => '₺',
				'THB' => '฿',
				'ILS' => '₪',
				'KRW' => '₩',
				'AED' => 'د.إ',
				'SAR' => 'ر.س',
				'PLN' => 'zł',
				'CZK' => 'Kč',
			);

			$symbol = isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : $currency . ' ';

			return $symbol . number_format_i18n( $amount, 2 );
		}

		/**
		 * Enqueue Square Web Payments SDK once per request.
		 *
		 * @param string $payment_mode test|live.
		 * @return void
		 */
		private function enqueue_square_js( $payment_mode = 'test' ) {
			if ( self::$square_script_enqueued ) {
				return;
			}

			$payment_mode = in_array( $payment_mode, array( 'live', 'test' ), true ) ? $payment_mode : 'test';
			$src          = 'live' === $payment_mode
				? 'https://web.squarecdn.com/v1/square.js'
				: 'https://sandbox.web.squarecdn.com/v1/square.js';

			wp_enqueue_script(
				'square-payments-sdk',
				$src,
				array(),
				null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
				true
			);

			self::$square_script_enqueued = true;
		}

		/**
		 * Resolve effective Square settings for a form.
		 *
		 * @param string $form_id Form ID.
		 * @return array
		 */
		private function resolve_form_payment_square( $form_id ) {
			$block_square = array();

			if ( '' !== $form_id ) {
				$schema = class_exists( 'Gutena_Forms_Helper' )
					? Gutena_Forms_Helper::get_form_schema_record( $form_id )
					: ( function_exists( 'gutena_forms_get_form_schema_option' ) ? gutena_forms_get_form_schema_option( $form_id, false ) : false );

				if ( is_array( $schema ) && ! empty( $schema['form_attrs']['paymentSquare'] ) ) {
					$block_square = $schema['form_attrs']['paymentSquare'];
				}
			}

			if ( class_exists( 'Gutena_Forms_Form_Block' ) ) {
				return Gutena_Forms_Form_Block::get_effective_payment_square( $block_square );
			}

			return is_array( $block_square ) ? $block_square : array();
		}
	}

	Gutena_Forms_Square_Field_Block::get_instance();
endif;
