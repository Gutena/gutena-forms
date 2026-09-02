import { __ } from '@wordpress/i18n';

const SquareLocationSelect = ( { locations = [], value = '', onChange } ) => {
	if ( ! locations.length ) {
		return null;
	}

	return (
		<div className="gutena-forms__square-location">
			<p className="gutena-forms__square-location__label">{ __( 'Business Location', 'gutena-forms' ) }</p>
			<div className="gutena-forms__currency-settings__select-wrap">
				<select
					className="gutena-forms__currency-settings__select"
					value={ value }
					onChange={ ( event ) => onChange?.( event.target.value ) }
				>
					<option value="">
						{ __( 'Select location', 'gutena-forms' ) }
					</option>
					{ locations.map( ( location ) => (
						<option key={ location.id } value={ location.id }>
							{ location.name }
						</option>
					) ) }
				</select>
			</div>
			<p className="gutena-forms__square-location__help">
				{ __( 'Only active locations that support credit card processing in Square can be chosen.', 'gutena-forms' ) }
			</p>
		</div>
	);
};

export default SquareLocationSelect;
