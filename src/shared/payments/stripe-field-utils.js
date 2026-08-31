import { select } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { gfIsEmpty, getInnerBlocksbyNameAttr } from '../utils/helper';

const AMOUNT_FIELD_TYPES = new Set( [ 'number', 'radio', 'select', 'checkbox' ] );

const STANDALONE_FIELD_BLOCKS = {
	email: 'gutena/email-field',
	text: 'gutena/text-field',
	textarea: 'gutena/textarea-field',
	number: 'gutena/number-field',
	radio: 'gutena/radio-field',
	select: 'gutena/dropdown-field',
	checkbox: 'gutena/checkbox-field',
	optin: 'gutena/optin-field',
	range: 'gutena/range-field',
};

export const getFormClientId = ( clientId ) => {
	const parents = select( blockEditorStore ).getBlockParentsByBlockName(
		clientId,
		'gutena/forms',
		true
	);
	return parents?.[ 0 ] || '';
};

export const getFormBlocks = ( formClientId ) => {
	if ( gfIsEmpty( formClientId ) ) {
		return null;
	}
	return select( blockEditorStore ).getBlock( formClientId );
};

export const formHasStripeField = ( formClientId, excludeClientId = '' ) => {
	const formBlock = getFormBlocks( formClientId );
	if ( gfIsEmpty( formBlock ) ) {
		return false;
	}

	const stripeFields = getInnerBlocksbyNameAttr(
		formBlock.innerBlocks,
		'gutena/stripe-field'
	);

	return stripeFields.some( ( block ) => block.clientId !== excludeClientId );
};

export const getFormFieldOptions = ( formClientId, types = [] ) => {
	const options = [];
	const formBlock = getFormBlocks( formClientId );

	if ( gfIsEmpty( formBlock ) ) {
		return options;
	}

	const allowedTypes = types.length ? new Set( types ) : null;

	const pushField = ( block ) => {
		const attrs = block.attributes || {};
		if ( gfIsEmpty( attrs.nameAttr ) ) {
			return;
		}

		const fieldType = attrs.fieldType || block.name?.replace( 'gutena/', '' ).replace( '-field', '' );
		if ( allowedTypes && ! allowedTypes.has( fieldType ) ) {
			return;
		}

		options.push( {
			label: attrs.fieldName || attrs.nameAttr,
			value: attrs.nameAttr,
			fieldType,
		} );
	};

	getInnerBlocksbyNameAttr( formBlock.innerBlocks, 'gutena/form-field' ).forEach( pushField );

	Object.entries( STANDALONE_FIELD_BLOCKS ).forEach( ( [ fieldType, blockName ] ) => {
		if ( allowedTypes && ! allowedTypes.has( fieldType ) ) {
			return;
		}

		getInnerBlocksbyNameAttr( formBlock.innerBlocks, blockName ).forEach( ( block ) => {
			const attrs = block.attributes || {};
			if ( gfIsEmpty( attrs.nameAttr ) ) {
				return;
			}

			options.push( {
				label: attrs.fieldName || attrs.nameAttr,
				value: attrs.nameAttr,
				fieldType,
			} );
		} );
	} );

	return options;
};

export const getEmailFieldOptions = ( formClientId ) =>
	getFormFieldOptions( formClientId, [ 'email' ] );

export const getTextFieldOptions = ( formClientId ) =>
	getFormFieldOptions( formClientId, [ 'text' ] );

export const getAmountFieldOptions = ( formClientId ) =>
	getFormFieldOptions( formClientId, [ ...AMOUNT_FIELD_TYPES ] );

export const getSingleEmailFieldNameAttr = ( formClientId ) => {
	const emailFields = getEmailFieldOptions( formClientId );
	return emailFields.length === 1 ? emailFields[ 0 ].value : '';
};

const STRIPE_CONFIG_KEYS = [
	'enable',
	'payment_mode',
	'currency',
	'currency_sign_position',
];

const isTruthyGatewayFlag = ( value ) =>
	value === true || value === 1 || value === '1' || value === 'true';

export const isStripeGatewayEnabled = () => {
	if ( typeof gutenaFormsBlock === 'undefined' || ! gutenaFormsBlock ) {
		return false;
	}

	if ( ! Object.prototype.hasOwnProperty.call( gutenaFormsBlock, 'stripe_gateway_enabled' ) ) {
		return false;
	}

	return isTruthyGatewayFlag( gutenaFormsBlock.stripe_gateway_enabled );
};

export const isStripeGatewayExplicitlyDisabled = () => ! isStripeGatewayEnabled();

export const getGlobalStripeDefaults = () =>
	typeof gutenaFormsBlock !== 'undefined' && gutenaFormsBlock.payment_stripe
		? gutenaFormsBlock.payment_stripe
		: {
			enable: false,
			payment_mode: 'test',
			currency: 'USD',
			currency_sign_position: 'left',
			connected: false,
			account_name: '',
			webhook_connected: false,
			webhook_slots_exceeded: false,
			defaultSettings: true,
		};

export const resolveEffectiveStripeSettings = ( paymentStripe ) => {
	const globalDefaults = getGlobalStripeDefaults();
	const usesGlobal =
		gfIsEmpty( paymentStripe ) ||
		! Object.prototype.hasOwnProperty.call( paymentStripe, 'defaultSettings' ) ||
		false !== paymentStripe.defaultSettings;

	if ( usesGlobal ) {
		return {
			...globalDefaults,
			defaultSettings: true,
		};
	}

	return {
		...globalDefaults,
		...paymentStripe,
		defaultSettings: false,
	};
};

export const stripeConfigDiffersFromGlobal = ( settings, globalDefaults ) =>
	STRIPE_CONFIG_KEYS.some(
		( key ) =>
			( settings?.[ key ] ?? globalDefaults?.[ key ] ) !== globalDefaults?.[ key ]
	);

export const buildFormStripeOverride = ( currentStripe, partial, globalDefaults ) => {
	const base = resolveEffectiveStripeSettings( currentStripe );
	const merged = {
		...base,
		...partial,
		enable: true,
	};

	const differs = stripeConfigDiffersFromGlobal( merged, globalDefaults );

	return {
		...merged,
		defaultSettings: ! differs,
	};
};

export const validateStripeFieldAttributes = ( attributes, formClientId ) => {
	const errors = [];
	const paymentStripe = select( blockEditorStore ).getBlockAttributes( formClientId )?.paymentStripe;
	const effectiveStripe = resolveEffectiveStripeSettings( paymentStripe );

	if ( ! effectiveStripe.connected ) {
		return errors;
	}

	if ( 'one_time' === attributes.paymentType ) {
		if ( 'variable' === attributes.amountType && gfIsEmpty( attributes.variableAmountField ) ) {
			errors.push( 'variable_amount_field' );
		}
		if ( gfIsEmpty( attributes.customerEmailField ) ) {
			errors.push( 'customer_email_field' );
		}
	}

	if ( 'subscription' === attributes.paymentType ) {
		const fixedAmount = Number( attributes.fixedAmount );
		if ( ! fixedAmount || fixedAmount <= 0 ) {
			errors.push( 'subscription_amount' );
		}
		if ( gfIsEmpty( attributes.customerEmailField ) ) {
			errors.push( 'customer_email_field' );
		}
		if ( gfIsEmpty( attributes.customerNameField ) ) {
			errors.push( 'customer_name_field' );
		}
		if ( gfIsEmpty( attributes.subscriptionPlanName ) ) {
			errors.push( 'subscription_plan_name' );
		}
		if ( 'custom' === attributes.billingCycles ) {
			const cycles = Number( attributes.customBillingCycles );
			if ( ! cycles || cycles < 1 || cycles > 100 ) {
				errors.push( 'custom_billing_cycles' );
			}
		}
	}

	return errors;
};
