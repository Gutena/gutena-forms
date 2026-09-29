const GutenaFormsUrlField = ( {
	id,
	label,
	desc,
	value,
	placeholder,
	onChange,
	disabled = false,
} ) => {
	return (
		<div className={ 'gutena-forms__url-control gutena-forms-notification-field' }>
			{ label && (
				<label
					className={ 'gutena-forms-notification-field__label' }
					htmlFor={ id }
				>
					{ label }
				</label>
			) }

			<div className={ 'gutena-forms-notification-field__row' }>
				<input
					id={ id }
					className={ 'gutena-forms-notification-field__input' }
					type="url"
					value={ value || '' }
					onChange={ ( event ) => onChange( event.target.value ) }
					placeholder={ placeholder }
					disabled={ disabled }
				/>
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

export default GutenaFormsUrlField;
