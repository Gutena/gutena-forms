import { __ } from '@wordpress/i18n';

const CURRENCY_SYMBOLS = {
	USD: '$',
	EUR: '€',
	GBP: '£',
	AUD: '$',
	CAD: '$',
	INR: '₹',
	BDT: '৳',
	JPY: '¥',
	BRL: 'R$',
	MYR: 'RM',
	SGD: '$',
	HKD: '$',
	NZD: '$',
	MXN: '$',
	TWD: '$',
	CHF: 'CHF',
	TRY: '₺',
	THB: '฿',
	ILS: '₪',
	KRW: '₩',
	AED: 'د.إ',
	SAR: 'ر.س',
	PLN: 'zł',
	CZK: 'Kč',
};

const INTERVAL_PHRASES = {
	daily: __( 'every day', 'gutena-forms' ),
	weekly: __( 'every week', 'gutena-forms' ),
	monthly: __( 'every month', 'gutena-forms' ),
	quarterly: __( 'every 3 months', 'gutena-forms' ),
	yearly: __( 'every year', 'gutena-forms' ),
};

export const formatCurrencyAmount = ( currency, amount ) => {
	const symbol = CURRENCY_SYMBOLS[ currency ] || `${ currency } `;
	return `${ symbol }${ Number( amount ).toFixed( 2 ) }`;
};

/**
 * Build subscription billing summary for editor/frontend display.
 *
 * @param {Object} options
 * @param {string} options.currency
 * @param {number} options.fixedAmount
 * @param {string} options.billingInterval
 * @param {string} options.billingCycles
 * @param {number} options.customBillingCycles
 * @return {string}
 */
export const formatSubscriptionSummary = ( {
	currency = 'USD',
	fixedAmount = 0,
	billingInterval = 'monthly',
	billingCycles = 'never',
	customBillingCycles = 1,
} ) => {
	if ( ! fixedAmount || fixedAmount <= 0 ) {
		return '';
	}

	const amountText = formatCurrencyAmount( currency, fixedAmount );
	const intervalPhrase = INTERVAL_PHRASES[ billingInterval ] || billingInterval;

	if ( 'never' === billingCycles ) {
		return `${ amountText } ${ intervalPhrase } (${ __( 'until cancelled', 'gutena-forms' ) })`;
	}

	let paymentCount = 0;
	if ( [ '2', '3', '4', '5' ].includes( billingCycles ) ) {
		paymentCount = parseInt( billingCycles, 10 );
	} else if ( 'custom' === billingCycles ) {
		paymentCount = parseInt( customBillingCycles, 10 ) || 0;
	}

	if ( paymentCount > 0 ) {
		return `${ amountText } ${ intervalPhrase } · ${ paymentCount } ${ __( 'payments', 'gutena-forms' ) }`;
	}

	return `${ amountText } ${ intervalPhrase }`;
};

export const isGutenaFormsPro = () =>
	typeof gutenaFormsBlock !== 'undefined' && !! gutenaFormsBlock?.is_pro;
