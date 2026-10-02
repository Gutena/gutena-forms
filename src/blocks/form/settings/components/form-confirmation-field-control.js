const FormConfirmationFieldControl = ( {
	id,
	label,
	type = 'text',
	value,
	onChange,
	options = [],
	placeholder = '',
	helpText = '',
	hasError = false,
} ) => {
	const isSelect = 'select' === type;

	return (
		<div
			className={ `gutena-forms-form-confirmation-field${
				isSelect ? ' gutena-forms-form-confirmation-field--select' : ''
			}${ hasError ? ' has-error' : '' }` }
		>
			{ label && (
				<label
					className="gutena-forms-form-confirmation-field__label"
					htmlFor={ id }
				>
					{ label }
				</label>
			) }

			{ isSelect ? (
				<select
					id={ id }
					className="gutena-forms-form-confirmation-field__select"
					value={ value }
					onChange={ ( event ) => onChange( event.target.value ) }
				>
					{ options.map( ( option ) => (
						<option key={ option.value } value={ option.value }>
							{ option.label }
						</option>
					) ) }
				</select>
			) : (
				<input
					id={ id }
					className="gutena-forms-form-confirmation-field__input"
					type={ type }
					value={ value || '' }
					onChange={ ( event ) => onChange( event.target.value ) }
					placeholder={ placeholder }
				/>
			) }

			{ helpText && (
				<p className="gutena-forms-form-confirmation-field__help">
					{ helpText }
				</p>
			) }
		</div>
	);
};

export default FormConfirmationFieldControl;
