import { __ } from '@wordpress/i18n';
import { CURRENCY_SIGN_POSITIONS } from './currencies';

const CurrencySignPosition = ( { value = 'left', onChange, disabled = false } ) => {
	return (
		<div className="gutena-forms__currency-sign-position">
			<p className="gutena-forms__currency-sign-position__label">
				{ __( 'Currency Sign Position', 'gutena-forms' ) }
			</p>
			<div className="gutena-forms__currency-sign-position__field">
				<label className="gutena-forms__currency-sign-position__field-label" htmlFor="gutena-forms-currency-sign-position">
					{ __( 'Select Position', 'gutena-forms' ) }
				</label>
				<div className="gutena-forms__currency-sign-position__select-wrap">
					<select
						id="gutena-forms-currency-sign-position"
						className="gutena-forms__currency-sign-position__select"
						value={ value }
						onChange={ ( event ) => onChange?.( event.target.value ) }
						disabled={ disabled }
					>
						{ CURRENCY_SIGN_POSITIONS.map( ( position ) => (
							<option key={ position.value } value={ position.value }>
								{ position.label }
							</option>
						) ) }
					</select>
				</div>
			</div>
		</div>
	);
};

export default CurrencySignPosition;
