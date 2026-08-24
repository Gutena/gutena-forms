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

			$currency = sanitize_text_field( $payment_stripe['currency'] ?? 'USD' );
			$mode     = sanitize_key( $payment_stripe['payment_mode'] ?? 'test' );
			$account_name = ! empty( $payment_stripe['account_name'] )
				? $payment_stripe['account_name']
				: __( 'the merchant', 'gutena-forms' );

			$amount_hint = $this->get_amount_hint( $currency, $amount_type, $fixed_amount );
			$show_subscription_notice = ( 'subscription' === $payment_type );
			$subscription_notice      = $show_subscription_notice
				? sprintf(
					/* translators: %s: connected Stripe account name */
					__( 'By subscribing, you authorise %s to charge you according to the terms until you cancel.', 'gutena-forms' ),
					$account_name
				)
				: '';

			ob_start();
			?>
			<div
				class="wp-block-gutena-field-group wp-block-gutena-stripe-field field-group-type-stripe standalone-stripe-field"
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
				data-variable-amount-field="<?php echo esc_attr( $variable_field ); ?>"
				data-customer-email-field="<?php echo esc_attr( $customer_email_field ); ?>"
				data-customer-name-field="<?php echo esc_attr( $customer_name_field ); ?>"
				data-rest-url="<?php echo esc_url( rest_url( 'gutena-forms/v1/' ) ); ?>"
				data-form-nonce="<?php echo esc_attr( wp_create_nonce( 'gutena_Forms' ) ); ?>"
			>
				<label class="heading-input-label-gutena gutena-forms-stripe-payment__heading" for="<?php echo esc_attr( 'gutena-stripe-card-number-' . $field_id ); ?>">
					<?php echo esc_html( $field_name ); ?>
				</label>

				<p class="gutena-forms-stripe-payment__amount-hint"><?php echo esc_html( $amount_hint ); ?></p>

				<div class="gutena-forms-stripe-payment__panel">
					<div class="gutena-forms-stripe-payment__field gutena-forms-stripe-payment__field--number">
						<label class="gutena-forms-stripe-payment__label" for="<?php echo esc_attr( 'gutena-stripe-card-number-' . $field_id ); ?>">
							<?php esc_html_e( 'Card number', 'gutena-forms' ); ?> <span class="gutena-forms-stripe-payment__required">*</span>
						</label>
						<div class="gutena-forms-stripe-payment__input-wrap">
							<div id="<?php echo esc_attr( 'gutena-stripe-card-number-' . $field_id ); ?>" class="gutena-forms-stripe-payment__element gutena-forms-stripe-payment__element--number"></div>
							<span class="gutena-forms-stripe-payment__field-icon" aria-hidden="true">
								<svg width="18" height="14" viewBox="0 0 18 14" fill="none"><rect x="0.5" y="0.5" width="17" height="13" rx="2" stroke="currentColor"/><rect x="1" y="3.5" width="16" height="3" fill="currentColor" opacity="0.35"/></svg>
							</span>
						</div>
					</div>

					<div class="gutena-forms-stripe-payment__row">
						<div class="gutena-forms-stripe-payment__field gutena-forms-stripe-payment__field--expiry">
							<label class="gutena-forms-stripe-payment__label" for="<?php echo esc_attr( 'gutena-stripe-card-expiry-' . $field_id ); ?>">
								<?php esc_html_e( 'Expiry date', 'gutena-forms' ); ?> <span class="gutena-forms-stripe-payment__required">*</span>
							</label>
							<div class="gutena-forms-stripe-payment__input-wrap">
								<div id="<?php echo esc_attr( 'gutena-stripe-card-expiry-' . $field_id ); ?>" class="gutena-forms-stripe-payment__element gutena-forms-stripe-payment__element--expiry"></div>
								<span class="gutena-forms-stripe-payment__field-icon" aria-hidden="true">
									<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1.5" y="2.5" width="13" height="12" rx="1.5" stroke="currentColor"/><path d="M1.5 6.5H14.5" stroke="currentColor"/><path d="M5 1.5V4" stroke="currentColor" stroke-linecap="round"/><path d="M11 1.5V4" stroke="currentColor" stroke-linecap="round"/></svg>
								</span>
							</div>
						</div>

						<div class="gutena-forms-stripe-payment__field gutena-forms-stripe-payment__field--cvc">
							<label class="gutena-forms-stripe-payment__label" for="<?php echo esc_attr( 'gutena-stripe-card-cvc-' . $field_id ); ?>">
								<?php esc_html_e( 'Security code', 'gutena-forms' ); ?> <span class="gutena-forms-stripe-payment__required">*</span>
							</label>
							<div class="gutena-forms-stripe-payment__input-wrap">
								<div id="<?php echo esc_attr( 'gutena-stripe-card-cvc-' . $field_id ); ?>" class="gutena-forms-stripe-payment__element gutena-forms-stripe-payment__element--cvc"></div>
								<span class="gutena-forms-stripe-payment__field-icon" aria-hidden="true">
									<svg width="14" height="16" viewBox="0 0 14 16" fill="none"><path d="M7 0.5C5.067 0.5 3.5 2.067 3.5 4V5.5H3C2.17157 5.5 1.5 6.17157 1.5 7V13.5C1.5 14.3284 2.17157 15 3 15H11C11.8284 15 12.5 14.3284 12.5 13.5V7C12.5 6.17157 11.8284 5.5 11 5.5H10.5V4C10.5 2.067 8.933 0.5 7 0.5ZM7 2C8.10457 2 9 2.89543 9 4V5.5H5V4C5 2.89543 5.89543 2 7 2Z" fill="currentColor"/></svg>
								</span>
							</div>
						</div>
					</div>

					<div class="gutena-forms-stripe-payment__field gutena-forms-stripe-payment__field--country">
						<label class="gutena-forms-stripe-payment__label" for="<?php echo esc_attr( 'gutena-stripe-country-' . $field_id ); ?>">
							<?php esc_html_e( 'Country', 'gutena-forms' ); ?> <span class="gutena-forms-stripe-payment__required">*</span>
						</label>
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

					<?php if ( $show_subscription_notice && '' !== $subscription_notice ) : ?>
						<p class="gutena-forms-stripe-payment__subscription-notice"><?php echo esc_html( $subscription_notice ); ?></p>
					<?php endif; ?>
				</div>

				<p class="gutena-forms-field-error-msg gutena-forms-stripe-payment__error"></p>
				<input type="hidden" name="<?php echo esc_attr( $field_id . '_payment_method' ); ?>" value="" />
				<input type="hidden" name="<?php echo esc_attr( $field_id . '_payment_intent' ); ?>" value="" class="gutena-forms-stripe-payment__intent-input" />
			</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * Initial amount hint text.
		 *
		 * @param string $currency     Currency code.
		 * @param string $amount_type  fixed|variable.
		 * @param float  $fixed_amount Fixed amount.
		 * @return string
		 */
		private function get_amount_hint( $currency, $amount_type, $fixed_amount ) {
			if ( 'fixed' === $amount_type && $fixed_amount > 0 ) {
				return $this->format_amount( $currency, $fixed_amount );
			}

			return __( 'Complete the form to view the amount.', 'gutena-forms' );
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
	}
endif;
