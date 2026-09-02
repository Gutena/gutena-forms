import { __ } from '@wordpress/i18n';
import { PAYMENT_CURRENCIES } from './currencies';

const getCurrencyLabel = ( currencyCode ) => {
	if ( ! currencyCode ) {
		return '';
	}

	const match = PAYMENT_CURRENCIES.find( ( item ) => item.value === currencyCode );

	return match ? match.label : currencyCode;
};

const SquareMerchantCurrency = ( { connected, merchantCurrency } ) => {
	const displayValue = connected && merchantCurrency
		? getCurrencyLabel( merchantCurrency )
		: __( 'Use Square Merchant Currency', 'gutena-forms' );

	return (
		<div className="gutena-forms__currency-settings">
			<p className="gutena-forms__currency-settings__label">{ __( 'Currency Settings', 'gutena-forms' ) }</p>
			<div className="gutena-forms__currency-settings__field">
				<label className="gutena-forms__currency-settings__field-label">
					{ __( 'Selected Currency', 'gutena-forms' ) }
				</label>
				<div className={ `gutena-forms__square-merchant-currency${ connected && merchantCurrency ? ' is-connected' : '' }` }>
					<span>{ displayValue }</span>
				</div>
				<p className="gutena-forms__currency-settings__help">
					{ __( 'The merchant currency will be selected automatically based on your connected Square account. Only one currency is supported per merchant.', 'gutena-forms' ) }
				</p>
			</div>
		</div>
	);
};

export default SquareMerchantCurrency;
