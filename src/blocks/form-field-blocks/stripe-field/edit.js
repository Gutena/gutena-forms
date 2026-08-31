import { __ } from '@wordpress/i18n';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	SelectControl,
	RadioControl,
	Notice,
} from '@wordpress/components';
import PaymentModeToggle from '../../../shared/payments/payment-mode-toggle';
import CurrencySelect from '../../../shared/payments/currency-select';
import StripeConnectionPanel from '../../../shared/payments/stripe-connection-panel';
import { fetchStripeSettings } from '../../../shared/payments/api';
import { formatCurrencyAmount, formatSubscriptionSummary, isGutenaFormsPro } from '../../../shared/payments/subscription-summary';
import { gfIsEmpty } from '../../../shared/utils/helper';
import {
	buildFormStripeOverride,
	getAmountFieldOptions,
	getEmailFieldOptions,
	getFormClientId,
	getGlobalStripeDefaults,
	getSingleEmailFieldNameAttr,
	getTextFieldOptions,
	isStripeGatewayExplicitlyDisabled,
	resolveEffectiveStripeSettings,
	validateStripeFieldAttributes,
} from '../../../shared/payments/stripe-field-utils';

const BILLING_INTERVALS = [
	{ label: __( 'Daily', 'gutena-forms' ), value: 'daily' },
	{ label: __( 'Weekly', 'gutena-forms' ), value: 'weekly' },
	{ label: __( 'Monthly', 'gutena-forms' ), value: 'monthly' },
	{ label: __( 'Quarterly', 'gutena-forms' ), value: 'quarterly' },
	{ label: __( 'Yearly', 'gutena-forms' ), value: 'yearly' },
];

const BILLING_CYCLES = [
	{ label: __( '2 Payments', 'gutena-forms' ), value: '2' },
	{ label: __( '3 Payments', 'gutena-forms' ), value: '3' },
	{ label: __( '4 Payments', 'gutena-forms' ), value: '4' },
	{ label: __( '5 Payments', 'gutena-forms' ), value: '5' },
	{ label: __( 'Never', 'gutena-forms' ), value: 'never' },
	{ label: __( 'Custom', 'gutena-forms' ), value: 'custom' },
];

const FieldError = ( { show, message } ) =>
	show ? (
		<p className="gutena-forms-stripe-field__error">{ message }</p>
	) : null;

export default function Edit( { attributes, setAttributes, clientId, isSelected } ) {
	const {
		fieldName,
		paymentType,
		amountType,
		fixedAmount,
		variableAmountField,
		minimumAmount,
		customerEmailField,
		customerNameField,
		subscriptionPlanName,
		billingInterval,
		billingCycles,
		customBillingCycles,
	} = attributes;

	const formClientId = getFormClientId( clientId );
	const globalDefaults = getGlobalStripeDefaults();

	const paymentStripe = useSelect(
		( select ) =>
			formClientId
				? select( blockEditorStore ).getBlockAttributes( formClientId )?.paymentStripe
				: null,
		[ formClientId ]
	);

	const { updateBlockAttributes } = useDispatch( blockEditorStore );
	const effectiveStripe = useMemo(
		() => resolveEffectiveStripeSettings( paymentStripe ),
		[ paymentStripe ]
	);

	const [ validationErrors, setValidationErrors ] = useState( [] );

	useEffect( () => {
		if ( ! formClientId ) {
			return;
		}

		if ( ! paymentStripe?.enable ) {
			updateStripeSettings( { enable: true } );
		}
	}, [ formClientId ] );

	useEffect( () => {
		if ( ! formClientId ) {
			return;
		}

		fetchStripeSettings()
			.then( ( values ) => {
				if ( ! values || Object.keys( values ).length === 0 ) {
					return;
				}

				const usesGlobal =
					! paymentStripe ||
					false !== paymentStripe.defaultSettings;

				if ( ! usesGlobal ) {
					return;
				}

				updateBlockAttributes( formClientId, {
					paymentStripe: {
						enable: true,
						payment_mode: values.payment_mode || 'test',
						currency: values.currency || 'USD',
						currency_sign_position: values.currency_sign_position || 'left',
						connected: !! values.connected,
						account_name: values.account_name || '',
						webhook_connected: !! values.webhook_connected,
						webhook_slots_exceeded: !! values.webhook_slots_exceeded,
						defaultSettings: true,
					},
				} );
			} )
			.catch( () => {} );
	}, [ formClientId ] );

	useEffect( () => {
		if ( ! formClientId || ! effectiveStripe.connected ) {
			return;
		}

		const autoEmailField = getSingleEmailFieldNameAttr( formClientId );
		if ( autoEmailField && gfIsEmpty( customerEmailField ) ) {
			setAttributes( { customerEmailField: autoEmailField } );
		}
	}, [ formClientId, effectiveStripe.connected, customerEmailField ] );

	useEffect( () => {
		if ( ! formClientId ) {
			setValidationErrors( [] );
			return;
		}
		setValidationErrors( validateStripeFieldAttributes( attributes, formClientId ) );
	}, [ attributes, formClientId, effectiveStripe.connected ] );

	const updateStripeSettings = ( partial ) => {
		if ( ! formClientId ) {
			return;
		}

		updateBlockAttributes( formClientId, {
			paymentStripe: buildFormStripeOverride( paymentStripe, partial, globalDefaults ),
		} );
	};

	const handleConnectionChange = ( partial ) => {
		if ( ! formClientId ) {
			return;
		}

		updateBlockAttributes( formClientId, {
			paymentStripe: {
				...resolveEffectiveStripeSettings( paymentStripe ),
				...partial,
				enable: true,
				defaultSettings: true,
			},
		} );
	};

	const emailOptions = useMemo(
		() => [
			{ label: __( 'Select', 'gutena-forms' ), value: '' },
			...getEmailFieldOptions( formClientId ),
		],
		[ formClientId, isSelected ]
	);

	const textOptions = useMemo(
		() => [
			{ label: __( 'Select', 'gutena-forms' ), value: '' },
			...getTextFieldOptions( formClientId ),
		],
		[ formClientId, isSelected ]
	);

	const amountOptions = useMemo(
		() => [
			{ label: __( 'Select', 'gutena-forms' ), value: '' },
			...getAmountFieldOptions( formClientId ),
		],
		[ formClientId, isSelected ]
	);

	const blockProps = useBlockProps( {
		className: 'wp-block-gutena-field-group wp-block-gutena-stripe-field field-group-type-stripe standalone-stripe-field',
	} );

	const connected = !! effectiveStripe.connected;
	const hasPro = isGutenaFormsPro();
	const currency = effectiveStripe.currency || 'USD';

	const handlePaymentTypeChange = ( value ) => {
		if ( 'subscription' === value && ! hasPro ) {
			return;
		}
		setAttributes( { paymentType: value } );
	};

	const subscriptionSummary = useMemo(
		() =>
			'subscription' === paymentType
				? formatSubscriptionSummary( {
					currency,
					fixedAmount,
					billingInterval,
					billingCycles,
					customBillingCycles,
				} )
				: '',
		[ paymentType, currency, fixedAmount, billingInterval, billingCycles, customBillingCycles ]
	);

	if ( isStripeGatewayExplicitlyDisabled() ) {
		return null;
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Field Settings', 'gutena-forms' ) } initialOpen={ true }>
					<TextControl
						label={ __( 'Label', 'gutena-forms' ) }
						value={ fieldName }
						onChange={ ( value ) => setAttributes( { fieldName: value } ) }
					/>

					<PaymentModeToggle
						value={ effectiveStripe.payment_mode || 'test' }
						onChange={ ( value ) => updateStripeSettings( { payment_mode: value } ) }
					/>

					<CurrencySelect
						value={ effectiveStripe.currency || 'USD' }
						onChange={ ( value ) => updateStripeSettings( { currency: value } ) }
					/>

					<StripeConnectionPanel
						paymentMode={ effectiveStripe.payment_mode || 'test' }
						connected={ connected }
						accountName={ effectiveStripe.account_name }
						webhookConnected={ effectiveStripe.webhook_connected }
						webhookSlotsExceeded={ effectiveStripe.webhook_slots_exceeded }
						onConnectionChange={ handleConnectionChange }
					/>

					{ connected && (
						<>
							<div className="gutena-forms-stripe-field__section">
								<p className="gutena-forms-stripe-field__section-label">
									{ __( 'Payment Type', 'gutena-forms' ) }
								</p>
								<RadioControl
									selected={ paymentType }
									options={ [
										{ label: __( 'One-time', 'gutena-forms' ), value: 'one_time' },
										{
											label: hasPro
												? __( 'Subscription', 'gutena-forms' )
												: `${ __( 'Subscription', 'gutena-forms' ) } (${ __( 'Pro', 'gutena-forms' ) })`,
											value: 'subscription',
										},
									] }
									onChange={ handlePaymentTypeChange }
								/>
								{ ! hasPro && (
									<p className="gutena-forms-stripe-field__help">
										{ __( 'Subscription payments are available in Gutena Forms Pro.', 'gutena-forms' ) }
									</p>
								) }
							</div>

							{ 'one_time' === paymentType && (
								<>
									<div className="gutena-forms-stripe-field__section">
										<p className="gutena-forms-stripe-field__section-label">
											{ __( 'Amount Type', 'gutena-forms' ) }
										</p>
										<RadioControl
											selected={ amountType }
											options={ [
												{ label: __( 'Fixed', 'gutena-forms' ), value: 'fixed' },
												{ label: __( 'Variable', 'gutena-forms' ), value: 'variable' },
											] }
											onChange={ ( value ) => setAttributes( { amountType: value } ) }
										/>
										<p className="gutena-forms-stripe-field__help">
											{ __( 'Select a fixed amount or dynamically calculate the amount from other fields.', 'gutena-forms' ) }
										</p>
									</div>

									{ 'fixed' === amountType ? (
										<>
											<TextControl
												label={ __( 'Amount', 'gutena-forms' ) }
												type="number"
												min="0"
												step="0.01"
												value={ fixedAmount }
												onChange={ ( value ) =>
													setAttributes( { fixedAmount: parseFloat( value ) || 0 } )
												}
												help={ __( 'Set the exact amount you want to charge. Users won\'t be able to change it.', 'gutena-forms' ) }
											/>
										</>
									) : (
										<>
											<SelectControl
												label={ __( 'Choose Amount Field', 'gutena-forms' ) }
												value={ variableAmountField }
												options={ amountOptions }
												onChange={ ( value ) =>
													setAttributes( { variableAmountField: value } )
												}
												help={ __( 'Pick a field from your form, like a number, radio, dropdown, or checkbox, whose value should decide the payment amount.', 'gutena-forms' ) }
											/>
											<FieldError
												show={ validationErrors.includes( 'variable_amount_field' ) }
												message={ __( 'Please select a field for the variable amount.', 'gutena-forms' ) }
											/>
											<TextControl
												label={ __( 'Minimum Amount', 'gutena-forms' ) }
												type="number"
												min="0"
												step="0.01"
												value={ minimumAmount }
												onChange={ ( value ) =>
													setAttributes( { minimumAmount: parseFloat( value ) || 0 } )
												}
												help={ __( 'Set the minimum amount users can enter (0 for no minimum)', 'gutena-forms' ) }
											/>
										</>
									) }

									<SelectControl
										label={ __( 'Customer Email Mapping', 'gutena-forms' ) + ' *' }
										value={ customerEmailField }
										options={ emailOptions }
										onChange={ ( value ) =>
											setAttributes( { customerEmailField: value } )
										}
										help={ __( 'Select the email field that contains the customer\'s email', 'gutena-forms' ) }
									/>
									<FieldError
										show={ validationErrors.includes( 'customer_email_field' ) }
										message={ __( 'Please select a customer email field.', 'gutena-forms' ) }
									/>

									<SelectControl
										label={ __( 'Customer Name Mapping', 'gutena-forms' ) }
										value={ customerNameField }
										options={ textOptions }
										onChange={ ( value ) =>
											setAttributes( { customerNameField: value } )
										}
										help={ __( 'Select the input field that contains the customer name', 'gutena-forms' ) }
									/>
								</>
							) }

							{ 'subscription' === paymentType && hasPro && (
								<>
									<TextControl
										label={ __( 'Subscription Plan Name', 'gutena-forms' ) + ' *' }
										value={ subscriptionPlanName }
										onChange={ ( value ) =>
											setAttributes( { subscriptionPlanName: value } )
										}
									/>
									<FieldError
										show={ validationErrors.includes( 'subscription_plan_name' ) }
										message={ __( 'Please enter a subscription plan name.', 'gutena-forms' ) }
									/>

									<TextControl
										label={ __( 'Amount', 'gutena-forms' ) + ' *' }
										type="number"
										min="0"
										step="0.01"
										value={ fixedAmount }
										onChange={ ( value ) =>
											setAttributes( { fixedAmount: parseFloat( value ) || 0 } )
										}
										help={ __( 'Set the recurring amount charged for each billing cycle.', 'gutena-forms' ) }
									/>
									<FieldError
										show={ validationErrors.includes( 'subscription_amount' ) }
										message={ __( 'Please enter a subscription amount greater than zero.', 'gutena-forms' ) }
									/>

									<SelectControl
										label={ __( 'Billing Interval', 'gutena-forms' ) }
										value={ billingInterval }
										options={ BILLING_INTERVALS }
										onChange={ ( value ) =>
											setAttributes( { billingInterval: value } )
										}
									/>

									<SelectControl
										label={ __( 'Billing Cycles', 'gutena-forms' ) }
										value={ billingCycles }
										options={ BILLING_CYCLES }
										onChange={ ( value ) =>
											setAttributes( { billingCycles: value } )
										}
										help={ __( 'Choose when the subscription will stop automatically.', 'gutena-forms' ) }
									/>

									{ 'custom' === billingCycles && (
										<>
											<TextControl
												label={ __( 'Number of Payments', 'gutena-forms' ) }
												type="number"
												min="1"
												max="100"
												value={ customBillingCycles }
												onChange={ ( value ) =>
													setAttributes( {
														customBillingCycles: parseInt( value, 10 ) || 1,
													} )
												}
												help={ __( 'Enter a number between 1 and 100', 'gutena-forms' ) }
											/>
											<FieldError
												show={ validationErrors.includes( 'custom_billing_cycles' ) }
												message={ __( 'Enter a number between 1 and 100.', 'gutena-forms' ) }
											/>
										</>
									) }

									<SelectControl
										label={ __( 'Customer Email Mapping', 'gutena-forms' ) + ' *' }
										value={ customerEmailField }
										options={ emailOptions }
										onChange={ ( value ) =>
											setAttributes( { customerEmailField: value } )
										}
										help={ __( 'Select the email field that contains the customer\'s email', 'gutena-forms' ) }
									/>
									<FieldError
										show={ validationErrors.includes( 'customer_email_field' ) }
										message={ __( 'Please select a customer email field.', 'gutena-forms' ) }
									/>

									<SelectControl
										label={ __( 'Customer Name Mapping', 'gutena-forms' ) + ' *' }
										value={ customerNameField }
										options={ textOptions }
										onChange={ ( value ) =>
											setAttributes( { customerNameField: value } )
										}
										help={ __( 'Select the input field that contains the customer name', 'gutena-forms' ) }
									/>
									<FieldError
										show={ validationErrors.includes( 'customer_name_field' ) }
										message={ __( 'Please select a customer name field.', 'gutena-forms' ) }
									/>
								</>
							) }
						</>
					) }
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<h3 className="gutena-forms-stripe-field__preview-title">
					{ fieldName || __( 'Credit Card', 'gutena-forms' ) }
				</h3>
				{ connected ? (
					<div className="gutena-forms-stripe-field__preview-connected">
						<p className="gutena-forms-stripe-field__preview-status">
							{ __( 'Connected!', 'gutena-forms' ) }
						</p>
						<p className="gutena-forms-stripe-field__preview-description">
							{ __( 'Stripe account is connected. The payment field will appear when the form is published.', 'gutena-forms' ) }
						</p>
						{ 'subscription' === paymentType && hasPro && subscriptionPlanName && (
							<p className="gutena-forms-stripe-field__preview-plan">
								{ subscriptionPlanName }
							</p>
						) }
						{ 'subscription' === paymentType && hasPro && subscriptionSummary && (
							<p className="gutena-forms-stripe-field__preview-summary">
								{ subscriptionSummary }
							</p>
						) }
						{ 'one_time' === paymentType && 'fixed' === amountType && fixedAmount > 0 && (
							<p className="gutena-forms-stripe-field__preview-summary">
								{ formatCurrencyAmount( currency, fixedAmount ) }
							</p>
						) }
					</div>
				) : (
					<p className="gutena-forms-stripe-field__preview-description">
						{ __( 'You need to configure a Stripe account to collect payments from this form.', 'gutena-forms' ) }
					</p>
				) }

				{ validationErrors.length > 0 && isSelected && (
					<Notice status="warning" isDismissible={ false }>
						{ __( 'Some required Stripe payment settings are incomplete. Map the customer email field in Field Settings to collect payments.', 'gutena-forms' ) }
					</Notice>
				) }
			</div>
		</>
	);
}
