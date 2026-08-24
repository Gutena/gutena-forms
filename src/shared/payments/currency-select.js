import { __ } from '@wordpress/i18n';
import { PAYMENT_CURRENCIES } from './currencies';

const CurrencySelect = ( { value = 'USD', onChange, disabled = false } ) => (
	<div className="gutena-forms__currency-settings">
		<p className="gutena-forms__currency-settings__label">{ __( 'Currency Settings', 'gutena-forms' ) }</p>
		<div className="gutena-forms__currency-settings__field">
			<label className="gutena-forms__currency-settings__field-label" htmlFor="gutena-forms-stripe-field-currency">
				{ __( 'Select Currency', 'gutena-forms' ) }
			</label>
			<div className="gutena-forms__currency-settings__select-wrap">
				<select
					id="gutena-forms-stripe-field-currency"
					className="gutena-forms__currency-settings__select"
					value={ value }
					onChange={ ( event ) => onChange?.( event.target.value ) }
					disabled={ disabled }
				>
					{ PAYMENT_CURRENCIES.map( ( currency ) => (
						<option key={ currency.value } value={ currency.value }>
							{ currency.label }
						</option>
					) ) }
				</select>
			</div>
			<p className="gutena-forms__currency-settings__help">
				{ __( 'Select the default currency for payment forms.', 'gutena-forms' ) }
			</p>
		</div>
	</div>
);

export default CurrencySelect;
