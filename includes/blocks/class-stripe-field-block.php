<?php
/**
 * Stripe field block frontend rendering.
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Stripe_Field_Block' ) ) :
	/**
	 * Stripe payment field block.
	 */
	class Gutena_Forms_Stripe_Field_Block {
		/**
		 * Instance.
		 *
		 * @var Gutena_Forms_Stripe_Field_Block|null
		 */
		private static $instance = null;

		/**
		 * Whether Stripe.js was enqueued for this request.
		 *
		 * @var bool
		 */
		private static $stripe_script_enqueued = false;

		/**
		 * Whether the stripe view script was localized for frontend config.
		 *
		 * @var bool
		 */
		private static $view_script_localized = false;

		/**
		 * Get instance.
		 *
		 * @return Gutena_Forms_Stripe_Field_Block
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Render Stripe field on the frontend.
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

			if ( ! gutena_forms_is_stripe_gateway_enabled() ) {
				return '';
			}

			$form_id = '';
			if ( is_object( $block ) && ! empty( $block->context['gutena-forms/formID'] ) ) {
				$form_id = sanitize_key( $block->context['gutena-forms/formID'] );
			}

			$payment_stripe = $this->resolve_form_payment_stripe( $form_id );
			if ( empty( $payment_stripe['connected'] ) ) {
				return '';
			}

			$attributes   = is_array( $attributes ) ? $attributes : array();
			$field_id     = ! empty( $attributes['nameAttr'] ) ? sanitize_key( $attributes['nameAttr'] ) : 'stripe_payment';
			$field_name   = ! empty( $attributes['fieldName'] ) ? $attributes['fieldName'] : __( 'Payment', 'gutena-forms' );
			$payment_type = ! empty( $attributes['paymentType'] ) ? sanitize_key( $attributes['paymentType'] ) : 'one_time';
			$amount_type  = ! empty( $attributes['amountType'] ) ? sanitize_key( $attributes['amountType'] ) : 'fixed';
			$fixed_amount = isset( $attributes['fixedAmount'] ) ? floatval( $attributes['fixedAmount'] ) : 0;
			$variable_field = ! empty( $attributes['variableAmountField'] ) ? sanitize_key( $attributes['variableAmountField'] ) : '';
			$customer_email_field = ! empty( $attributes['customerEmailField'] ) ? sanitize_key( $attributes['customerEmailField'] ) : '';
			$customer_name_field  = ! empty( $attributes['customerNameField'] ) ? sanitize_key( $attributes['customerNameField'] ) : '';

			$publishable_key = class_exists( 'Gutena_Forms_Stripe_Connect' )
				? Gutena_Forms_Stripe_Connect::get_publishable_key( $payment_stripe['payment_mode'] ?? 'test' )
				: '';

			$account_id = class_exists( 'Gutena_Forms_Stripe_Connect' )
				? Gutena_Forms_Stripe_Connect::get_stripe_js_account_id( $payment_stripe['payment_mode'] ?? 'test' )
				: '';

			$this->enqueue_stripe_js();
			$this->localize_stripe_view_script();

			$currency = sanitize_text_field( $payment_stripe['currency'] ?? 'USD' );
			$mode     = sanitize_key( $payment_stripe['payment_mode'] ?? 'test' );
			$account_name = ! empty( $payment_stripe['account_name'] )
				? $payment_stripe['account_name']
				: __( 'the merchant', 'gutena-forms' );

			$minimum_amount       = isset( $attributes['minimumAmount'] ) ? floatval( $attributes['minimumAmount'] ) : 0;
			$subscription_plan    = ! empty( $attributes['subscriptionPlanName'] ) ? $attributes['subscriptionPlanName'] : '';
			$billing_interval     = ! empty( $attributes['billingInterval'] ) ? sanitize_key( $attributes['billingInterval'] ) : 'monthly';
			$billing_cycles       = ! empty( $attributes['billingCycles'] ) ? sanitize_key( $attributes['billingCycles'] ) : 'never';
			$custom_billing_cycles = isset( $attributes['customBillingCycles'] ) ? absint( $attributes['customBillingCycles'] ) : 1;

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

			$stripe_field_config = wp_json_encode(
				array(
					'nameAttr'            => $field_id,
					'blockName'           => 'gutena/stripe-field',
					'fieldType'           => 'stripe',
					'paymentType'         => $payment_type,
					'amountType'          => $amount_type,
					'fixedAmount'         => $fixed_amount,
					'variableAmountField' => $variable_field,
					'minimumAmount'       => $minimum_amount,
					'customerEmailField'  => $customer_email_field,
					'customerNameField'   => $customer_name_field,
					'subscriptionPlanName' => $subscription_plan,
					'billingInterval'     => $billing_interval,
					'billingCycles'       => $billing_cycles,
					'customBillingCycles' => $custom_billing_cycles,
				)
			);

			ob_start();
			?>
			<div
				class="wp-block-gutena-field-group wp-block-gutena-stripe-field field-group-type-stripe standalone-stripe-field<?php echo 'subscription' === $payment_type ? ' is-subscription-payment' : ''; ?>"
				data-stripe-field="<?php echo esc_attr( $field_id ); ?>"
				data-gutena-stripe-payment="1"
				data-stripe-field-id="<?php echo esc_attr( $field_id ); ?>"
				data-stripe-publishable-key="<?php echo esc_attr( $publishable_key ); ?>"
				data-stripe-account="<?php echo esc_attr( $account_id ); ?>"
				data-stripe-currency="<?php echo esc_attr( $currency ); ?>"
				data-stripe-mode="<?php echo esc_attr( $mode ); ?>"
				data-form-id="<?php echo esc_attr( $form_id ); ?>"
				data-payment-type="<?php echo esc_attr( $payment_type ); ?>"
				data-amount-type="<?php echo esc_attr( $amount_type ); ?>"
				data-fixed-amount="<?php echo esc_attr( $fixed_amount ); ?>"
				data-minimum-amount="<?php echo esc_attr( $minimum_amount ); ?>"
				data-subscription-plan="<?php echo esc_attr( $subscription_plan ); ?>"
				data-billing-interval="<?php echo esc_attr( $billing_interval ); ?>"
				data-billing-cycles="<?php echo esc_attr( $billing_cycles ); ?>"
				data-custom-billing-cycles="<?php echo esc_attr( $custom_billing_cycles ); ?>"
				data-variable-amount-field="<?php echo esc_attr( $variable_field ); ?>"
				data-customer-email-field="<?php echo esc_attr( $customer_email_field ); ?>"
				data-customer-name-field="<?php echo esc_attr( $customer_name_field ); ?>"
				data-rest-url="<?php echo esc_url( rest_url( 'gutena-forms/v1/' ) ); ?>"
				data-form-nonce="<?php echo esc_attr( wp_create_nonce( 'gutena_Forms' ) ); ?>"
				data-wp-rest-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>"
			>
				<label class="heading-input-label-gutena gutena-forms-stripe-payment__heading" for="<?php echo esc_attr( 'gutena-stripe-card-number-' . $field_id ); ?>">
					<?php echo esc_html( $field_name ); ?>
				</label>

				<?php if ( 'subscription' === $payment_type ) : ?>
					<div class="gutena-forms-stripe-payment__subscription-details">
						<span class="gutena-forms-stripe-payment__subscription-badge"><?php esc_html_e( 'Subscription', 'gutena-forms' ); ?></span>
						<?php if ( '' !== $subscription_plan ) : ?>
							<p class="gutena-forms-stripe-payment__plan-name"><?php echo esc_html( $subscription_plan ); ?></p>
						<?php endif; ?>
						<p class="gutena-forms-stripe-payment__amount-hint gutena-forms-stripe-payment__subscription-summary"><?php echo esc_html( $amount_hint ); ?></p>
					</div>
				<?php else : ?>
					<p class="gutena-forms-stripe-payment__amount-hint"><?php echo esc_html( $amount_hint ); ?></p>
				<?php endif; ?>

				<div class="gutena-forms-stripe-payment__panel">
					<?php echo self::render_payment_chrome(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

					<div class="gutena-forms-stripe-payment__fields">
						<div class="gutena-forms-stripe-payment__field gutena-forms-stripe-payment__field--number">
							<label class="gutena-forms-stripe-payment__label" for="<?php echo esc_attr( 'gutena-stripe-card-number-' . $field_id ); ?>">
								<?php esc_html_e( 'Card number', 'gutena-forms' ); ?>
							</label>
							<div class="gutena-forms-stripe-payment__input-wrap gutena-forms-stripe-payment__input-wrap--number">
								<div id="<?php echo esc_attr( 'gutena-stripe-card-number-' . $field_id ); ?>" class="gutena-forms-stripe-payment__element gutena-forms-stripe-payment__element--number"></div>
								<?php echo self::render_card_brand_icons(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						</div>

						<div class="gutena-forms-stripe-payment__row">
							<div class="gutena-forms-stripe-payment__field gutena-forms-stripe-payment__field--expiry">
								<label class="gutena-forms-stripe-payment__label" for="<?php echo esc_attr( 'gutena-stripe-card-expiry-' . $field_id ); ?>">
									<?php esc_html_e( 'Expiration date', 'gutena-forms' ); ?>
								</label>
								<div class="gutena-forms-stripe-payment__input-wrap">
									<div id="<?php echo esc_attr( 'gutena-stripe-card-expiry-' . $field_id ); ?>" class="gutena-forms-stripe-payment__element gutena-forms-stripe-payment__element--expiry"></div>
								</div>
							</div>

							<div class="gutena-forms-stripe-payment__field gutena-forms-stripe-payment__field--cvc">
								<label class="gutena-forms-stripe-payment__label" for="<?php echo esc_attr( 'gutena-stripe-card-cvc-' . $field_id ); ?>">
									<?php esc_html_e( 'Security code', 'gutena-forms' ); ?>
								</label>
								<div class="gutena-forms-stripe-payment__input-wrap">
									<div id="<?php echo esc_attr( 'gutena-stripe-card-cvc-' . $field_id ); ?>" class="gutena-forms-stripe-payment__element gutena-forms-stripe-payment__element--cvc"></div>
								</div>
							</div>
						</div>

						<div class="gutena-forms-stripe-payment__field gutena-forms-stripe-payment__field--country">
							<label class="gutena-forms-stripe-payment__label" for="<?php echo esc_attr( 'gutena-stripe-country-' . $field_id ); ?>">
								<?php esc_html_e( 'Country', 'gutena-forms' ); ?>
							</label>
							<div class="gutena-forms-stripe-payment__select-wrap">
								<select
									id="<?php echo esc_attr( 'gutena-stripe-country-' . $field_id ); ?>"
									name="<?php echo esc_attr( $field_id . '_country' ); ?>"
									class="gutena-forms-stripe-payment__country gutena-forms-field"
									required
								>
									<?php foreach ( self::get_countries() as $country ) : ?>
										<option value="<?php echo esc_attr( $country['value'] ); ?>"><?php echo esc_html( $country['label'] ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>

						<?php if ( $show_subscription_notice && '' !== $subscription_notice ) : ?>
							<p class="gutena-forms-stripe-payment__subscription-notice"><?php echo esc_html( $subscription_notice ); ?></p>
						<?php endif; ?>
					</div>
				</div>

				<p class="gutena-forms-field-error-msg gutena-forms-stripe-payment__error"></p>
				<input type="hidden" name="<?php echo esc_attr( $field_id . '_payment_method' ); ?>" value="" />
				<input type="hidden" name="<?php echo esc_attr( $field_id . '_payment_intent' ); ?>" value="" class="gutena-forms-stripe-payment__intent-input" />
				<input type="hidden" name="<?php echo esc_attr( $field_id . '_stripe_config' ); ?>" value="<?php echo esc_attr( $stripe_field_config ); ?>" class="gutena-forms-stripe-payment__config-input" />
			</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * Card tab + Link banner chrome matching embedded Stripe card UI.
		 *
		 * @return string
		 */
		private static function render_payment_chrome() {
			ob_start();
			?>
			<div class="gutena-forms-stripe-payment__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Payment method', 'gutena-forms' ); ?>">
				<div class="gutena-forms-stripe-payment__tab is-active" role="tab" aria-selected="true" tabindex="0">
					<span class="gutena-forms-stripe-payment__tab-icon" aria-hidden="true">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="3.5" width="14" height="9" rx="1.5" stroke="currentColor" stroke-width="1.25"/><path d="M1 6.5H15" stroke="currentColor" stroke-width="1.25"/></svg>
					</span>
					<span class="gutena-forms-stripe-payment__tab-label"><?php esc_html_e( 'Card', 'gutena-forms' ); ?></span>
				</div>
			</div>
			<div class="gutena-forms-stripe-payment__link-banner" aria-hidden="true">
				<span class="gutena-forms-stripe-payment__link-icon" aria-hidden="true">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M4.5 7V5.25C4.5 3.45507 5.95507 2 7.75 2H8.25C10.0449 2 11.5 3.45507 11.5 5.25V7M3.75 7H12.25C12.9404 7 13.5 7.55964 13.5 8.25V12.75C13.5 13.4404 12.9404 14 12.25 14H3.75C3.05964 14 2.5 13.4404 2.5 12.75V8.25C2.5 7.55964 3.05964 7 3.75 7Z" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</span>
				<span class="gutena-forms-stripe-payment__link-text"><?php esc_html_e( 'Secure, fast checkout with Link', 'gutena-forms' ); ?></span>
				<span class="gutena-forms-stripe-payment__link-chevron" aria-hidden="true">
					<svg width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</span>
			</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * Accepted card brand icons for the card number field.
		 *
		 * @return string
		 */
		private static function render_card_brand_icons() {
			ob_start();
			?>
			<div class="gutena-forms-stripe-payment__brand-icons" aria-hidden="true">
				<span class="gutena-forms-stripe-payment__brand-icon gutena-forms-stripe-payment__brand-icon--mastercard">
					<svg width="24" height="16" viewBox="0 0 24 16" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="24" height="16" rx="2" fill="#fff"/><circle cx="9" cy="8" r="4.5" fill="#EB001B"/><circle cx="15" cy="8" r="4.5" fill="#F79E1B"/><path d="M12 4.82C13.08 5.78 13.8 6.78 13.8 8C13.8 9.22 13.08 10.22 12 11.18C10.92 10.22 10.2 9.22 10.2 8C10.2 6.78 10.92 5.78 12 4.82Z" fill="#FF5F00"/></svg>
				</span>
				<span class="gutena-forms-stripe-payment__brand-icon gutena-forms-stripe-payment__brand-icon--visa">
					<svg width="24" height="16" viewBox="0 0 24 16" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="24" height="16" rx="2" fill="#fff"/><path d="M9.82 10.52L10.74 5.72H12.32L11.4 10.52H9.82ZM17.48 5.84C17.14 5.72 16.58 5.6 15.88 5.6C14.12 5.6 12.92 6.52 12.9 7.72C12.88 8.56 13.68 9.04 14.28 9.32C14.9 9.62 15.1 9.8 15.1 10.06C15.08 10.46 14.62 10.66 14.18 10.66C13.52 10.66 13.16 10.54 12.68 10.32L12.48 10.22L12.26 11.44C12.66 11.6 13.36 11.72 14.08 11.74C16 11.74 17.18 10.84 17.2 9.56C17.22 8.86 16.76 8.34 15.76 7.88C15.22 7.6 14.88 7.44 14.88 7.16C14.9 6.92 15.16 6.66 15.78 6.66C16.32 6.64 16.72 6.76 17.02 6.9L17.16 6.98L17.48 5.84ZM20.2 5.72H18.88C18.48 5.72 18.16 5.84 17.98 6.28L15.56 10.52H17.26C17.26 10.52 17.56 9.76 17.64 9.54C17.86 9.54 19.72 9.54 20.04 9.54C20.1 9.82 20.26 10.52 20.26 10.52H21.74L20.2 5.72ZM7.16 5.72L5.56 8.96L5.4 8.18C5.04 7.14 4.06 6.56 2.92 6.56H2.92L2.88 6.76C4.18 7.06 5.18 7.86 5.58 8.96L6.78 10.52H8.48L10.66 5.72H7.16Z" fill="#172B85"/></svg>
				</span>
				<span class="gutena-forms-stripe-payment__brand-icon gutena-forms-stripe-payment__brand-icon--amex">
					<svg width="24" height="16" viewBox="0 0 24 16" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="24" height="16" rx="2" fill="#1F72CD"/><path d="M3.2 7.08L2.4 5.72H1.6L3.36 8.92V10.52H4.08V8.92L5.84 5.72H5.08L4.28 7.08L3.48 5.72H2.72L3.2 7.08ZM6.56 10.52H7.28V5.72H6.56V10.52ZM9.04 10.52H11.44V9.92H9.76V8.48H11.28V7.88H9.76V6.32H11.44V5.72H9.04V10.52ZM12.16 10.52H14.72L15.04 9.72H16.72L17.04 10.52H17.84L16.32 5.72H15.44L13.92 10.52H12.16ZM15.24 9.16L15.88 7.28L16.52 9.16H15.24ZM18.48 10.52H21.28C21.76 10.52 22.12 10.4 22.36 10.12C22.56 9.88 22.64 9.6 22.64 9.24V6.96C22.64 6.6 22.56 6.32 22.36 6.08C22.12 5.8 21.76 5.68 21.28 5.68H18.48V10.52ZM19.2 6.28H21.16C21.4 6.28 21.56 6.32 21.64 6.4C21.72 6.48 21.76 6.64 21.76 6.88V9.32C21.76 9.56 21.72 9.72 21.64 9.8C21.56 9.88 21.4 9.92 21.16 9.92H19.2V6.28Z" fill="#fff"/></svg>
				</span>
				<span class="gutena-forms-stripe-payment__brand-icon gutena-forms-stripe-payment__brand-icon--discover">
					<svg width="24" height="16" viewBox="0 0 24 16" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="24" height="16" rx="2" fill="#fff"/><rect x="0.5" y="0.5" width="23" height="15" rx="1.5" stroke="#E6E6E6"/><circle cx="13.5" cy="8" r="3.5" fill="#F47216"/><path d="M5.2 6.4H6.48C7.28 6.4 7.84 6.96 7.84 7.68C7.84 8.4 7.28 8.96 6.48 8.96H6.08V10.08H5.2V6.4ZM6.08 8.24H6.4C6.8 8.24 7.04 8 7.04 7.68C7.04 7.36 6.8 7.12 6.4 7.12H6.08V8.24Z" fill="#111"/><path d="M8.4 6.4H9.28V10.08H8.4V6.4Z" fill="#111"/><path d="M9.84 8.24C9.84 7.2 10.64 6.32 11.84 6.32C12.08 6.32 12.28 6.36 12.48 6.4V7.28C12.28 7.2 12.04 7.16 11.84 7.16C11.28 7.16 10.8 7.64 10.8 8.24C10.8 8.84 11.28 9.32 11.84 9.32C12.04 9.32 12.28 9.28 12.48 9.2V10.08C12.28 10.12 12.08 10.16 11.84 10.16C10.64 10.16 9.84 9.28 9.84 8.24Z" fill="#111"/><path d="M16.8 6.48C17.84 6.48 18.56 7.08 18.56 8C18.56 8.92 17.84 9.52 16.8 9.52C15.76 9.52 15.04 8.92 15.04 8C15.04 7.08 15.76 6.48 16.8 6.48ZM16.8 7.2C16.24 7.2 15.84 7.56 15.84 8C15.84 8.44 16.24 8.8 16.8 8.8C17.36 8.8 17.76 8.44 17.76 8C17.76 7.56 17.36 7.2 16.8 7.2Z" fill="#111"/></svg>
				</span>
			</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * Subscription authorization notice shown above the card fields.
		 *
		 * @param string $account_name          Connected Stripe account name.
		 * @param string $billing_cycles        Billing cycles key.
		 * @param int    $custom_billing_cycles Custom payment count.
		 * @return string
		 */
		private function get_subscription_authorization_notice( $account_name, $billing_cycles, $custom_billing_cycles ) {
			if ( 'never' === sanitize_key( $billing_cycles ) ) {
				return sprintf(
					/* translators: %s: connected Stripe account name */
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
					/* translators: 1: connected Stripe account name, 2: number of payments */
					__( 'By subscribing, you authorise %1$s to charge you for %2$d scheduled payments according to the terms above.', 'gutena-forms' ),
					$account_name,
					$payment_count
				);
			}

			return sprintf(
				/* translators: %s: connected Stripe account name */
				__( 'By subscribing, you authorise %s to charge you according to the terms above.', 'gutena-forms' ),
				$account_name
			);
		}

		/**
		 * Initial amount hint text.
		 *
		 * @param string $currency     Currency code.
		 * @param string $amount_type  fixed|variable.
		 * @param float  $fixed_amount Fixed amount.
		 * @param string $payment_type one_time|subscription.
		 * @param array  $subscription Subscription settings.
		 * @return string
		 */
		private function get_amount_hint( $currency, $amount_type, $fixed_amount, $payment_type = 'one_time', $subscription = array() ) {
			if ( 'subscription' === $payment_type ) {
				return $this->format_subscription_summary(
					$currency,
					$fixed_amount,
					$subscription['billingInterval'] ?? 'monthly',
					$subscription['billingCycles'] ?? 'never',
					$subscription['customBillingCycles'] ?? 1
				);
			}

			if ( 'fixed' === $amount_type && $fixed_amount > 0 ) {
				return $this->format_amount( $currency, $fixed_amount );
			}

			return __( 'Complete the form to view the amount.', 'gutena-forms' );
		}

		/**
		 * Format subscription billing summary for frontend display.
		 *
		 * @param string $currency              Currency code.
		 * @param float  $fixed_amount          Recurring amount.
		 * @param string $billing_interval      Billing interval key.
		 * @param string $billing_cycles        Billing cycles key.
		 * @param int    $custom_billing_cycles Custom payment count.
		 * @return string
		 */
		private function format_subscription_summary( $currency, $fixed_amount, $billing_interval, $billing_cycles, $custom_billing_cycles ) {
			if ( $fixed_amount <= 0 ) {
				return __( 'Complete the form to view the amount.', 'gutena-forms' );
			}

			$interval_phrases = array(
				'daily'     => __( 'every day', 'gutena-forms' ),
				'weekly'    => __( 'every week', 'gutena-forms' ),
				'monthly'   => __( 'every month', 'gutena-forms' ),
				'quarterly' => __( 'every 3 months', 'gutena-forms' ),
				'yearly'    => __( 'every year', 'gutena-forms' ),
			);

			$amount_text      = $this->format_amount( $currency, $fixed_amount );
			$interval_phrase  = $interval_phrases[ $billing_interval ] ?? $billing_interval;

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
		 * Country options for billing country field.
		 *
		 * @return array<int, array{value: string, label: string}>
		 */
		private static function get_countries() {
			return array(
				array(
					'value' => '',
					'label' => __( 'Select country', 'gutena-forms' ),
				),
				array(
					'value' => 'US',
					'label' => __( 'United States', 'gutena-forms' ),
				),
				array(
					'value' => 'GB',
					'label' => __( 'United Kingdom', 'gutena-forms' ),
				),
				array(
					'value' => 'CA',
					'label' => __( 'Canada', 'gutena-forms' ),
				),
				array(
					'value' => 'AU',
					'label' => __( 'Australia', 'gutena-forms' ),
				),
				array(
					'value' => 'DE',
					'label' => __( 'Germany', 'gutena-forms' ),
				),
				array(
					'value' => 'FR',
					'label' => __( 'France', 'gutena-forms' ),
				),
				array(
					'value' => 'PK',
					'label' => __( 'Pakistan', 'gutena-forms' ),
				),
				array(
					'value' => 'IN',
					'label' => __( 'India', 'gutena-forms' ),
				),
				array(
					'value' => 'BD',
					'label' => __( 'Bangladesh', 'gutena-forms' ),
				),
				array(
					'value' => 'SG',
					'label' => __( 'Singapore', 'gutena-forms' ),
				),
				array(
					'value' => 'AE',
					'label' => __( 'United Arab Emirates', 'gutena-forms' ),
				),
			);
		}

		/**
		 * Resolve effective Stripe settings for a form.
		 *
		 * @param string $form_id Form ID.
		 * @return array
		 */
		private function resolve_form_payment_stripe( $form_id ) {
			$block_stripe = array();

			if ( '' !== $form_id ) {
				$schema = gutena_forms_get_form_schema_option( $form_id, false );
				if ( is_array( $schema ) && ! empty( $schema['form_attrs']['paymentStripe'] ) ) {
					$block_stripe = $schema['form_attrs']['paymentStripe'];
				}
			}

			if ( class_exists( 'Gutena_Forms_Form_Block' ) ) {
				return Gutena_Forms_Form_Block::get_effective_payment_stripe( $block_stripe );
			}

			return is_array( $block_stripe ) ? $block_stripe : array();
		}

		/**
		 * Enqueue Stripe.js once per request.
		 *
		 * @return void
		 */
		private function enqueue_stripe_js() {
			if ( self::$stripe_script_enqueued ) {
				return;
			}

			wp_enqueue_script(
				'stripe-js',
				'https://js.stripe.com/v3/',
				array(),
				null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
				true
			);

			self::$stripe_script_enqueued = true;
		}

		/**
		 * Localize frontend config on the stripe view script handle.
		 *
		 * @return void
		 */
		private function localize_stripe_view_script() {
			if ( self::$view_script_localized || ! function_exists( 'gutena_forms_get_block_editor_config' ) ) {
				return;
			}

			$handles = array(
				'gutena-stripe-field-view-script',
				'gutena-forms-stripe-field-view-script',
			);

			foreach ( $handles as $handle ) {
				if ( wp_script_is( $handle, 'registered' ) || wp_script_is( $handle, 'enqueued' ) ) {
					wp_localize_script(
						$handle,
						'gutenaFormsBlock',
						gutena_forms_get_block_editor_config()
					);
					self::$view_script_localized = true;
					return;
				}
			}
		}
	}
endif;
