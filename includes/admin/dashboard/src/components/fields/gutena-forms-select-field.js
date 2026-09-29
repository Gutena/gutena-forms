const GutenaFormsSelectField = ( {
	id,
	label,
	desc,
	value,
	options = {},
	onChange,
	disabled = false,
} ) => {
	const selectedValue = value || '0';
	const isPlaceholder = '0' === selectedValue;

	return (
		<div className={ 'gutena-forms__select-control gutena-forms-notification-field' }>
			{ label && (
				<label
					className={ 'gutena-forms-notification-field__label' }
					htmlFor={ id }
				>
					{ label }
				</label>
			) }

			<div className={ 'gutena-forms-notification-field__row' }>
				<select
					id={ id }
					className={ `gutena-forms-notification-field__select${
						isPlaceholder ? ' is-placeholder' : ''
					}` }
					value={ selectedValue }
					onChange={ ( event ) => onChange( event.target.value ) }
					disabled={ disabled }
				>
					{ Object.keys( options ).map( ( optionKey ) => (
						<option key={ optionKey } value={ optionKey }>
							{ options[ optionKey ] }
						</option>
					) ) }
				</select>
			</div>

			{ desc && (
				<p
					className={ 'gutena-forms-notification-field__help' }
					dangerouslySetInnerHTML={ { __html: desc } }
				/>
			) }
		</div>
	);
};

export default GutenaFormsSelectField;
