import { select } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { gfIsEmpty } from '../utils/helper';
import {
	getAmountFieldOptions,
	getEmailFieldOptions,
	getFormClientId,
	getSingleEmailFieldNameAttr,
	getTextFieldOptions,
} from './stripe-field-utils';

const SQUARE_CONFIG_KEYS = [
	'enable',
	'payment_mode',
	'location_id',
];

const isTruthyGatewayFlag = ( value ) =>
	value === true || value === 1 || value === '1' || value === 'true';

export {
	getAmountFieldOptions,
	getEmailFieldOptions,
	getFormClientId,
	getSingleEmailFieldNameAttr,
	getTextFieldOptions,
};

export const isSquareGatewayEnabled = () => {
	if ( typeof gutenaFormsBlock === 'undefined' || ! gutenaFormsBlock ) {
		return false;
	}

	if ( ! Object.prototype.hasOwnProperty.call( gutenaFormsBlock, 'square_gateway_enabled' ) ) {
		return false;
	}

	return isTruthyGatewayFlag( gutenaFormsBlock.square_gateway_enabled );
};

export const isSquareGatewayExplicitlyDisabled = () => ! isSquareGatewayEnabled();

export const getGlobalSquareDefaults = () =>
	typeof gutenaFormsBlock !== 'undefined' && gutenaFormsBlock.payment_square
		? gutenaFormsBlock.payment_square
		: {
			enable: false,
			payment_mode: 'test',
			connected: false,
			connected_payment_mode: 'test',
			account_name: '',
			merchant_currency: '',
			location_id: '',
			business_locations: [],
			defaultSettings: true,
		};

export const resolveEffectiveSquareSettings = ( paymentSquare ) => {
	const globalDefaults = getGlobalSquareDefaults();
	const usesGlobal =
		gfIsEmpty( paymentSquare ) ||
		! Object.prototype.hasOwnProperty.call( paymentSquare, 'defaultSettings' ) ||
		false !== paymentSquare.defaultSettings;

	if ( usesGlobal ) {
		return {
			...globalDefaults,
			defaultSettings: true,
		};
	}

	return {
		...globalDefaults,
		...paymentSquare,
		defaultSettings: false,
	};
};

export const squareConfigDiffersFromGlobal = ( settings, globalDefaults ) =>
	SQUARE_CONFIG_KEYS.some(
		( key ) =>
			( settings?.[ key ] ?? globalDefaults?.[ key ] ) !== globalDefaults?.[ key ]
	);

export const buildFormSquareOverride = ( currentSquare, partial, globalDefaults ) => {
	const base = resolveEffectiveSquareSettings( currentSquare );
	const merged = {
		...base,
		...partial,
		enable: true,
	};

	const differs = squareConfigDiffersFromGlobal( merged, globalDefaults );

	return {
		...merged,
		defaultSettings: ! differs,
	};
};

export const validateSquareFieldAttributes = ( attributes, formClientId ) => {
	const errors = [];
	const paymentSquare = select( blockEditorStore ).getBlockAttributes( formClientId )?.paymentSquare;
	const effectiveSquare = resolveEffectiveSquareSettings( paymentSquare );

	if ( ! effectiveSquare.connected ) {
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
