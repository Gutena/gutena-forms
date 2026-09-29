const GutenaFormsTextField = ( {
	onChange,
	label,
	id,
	desc,
	value,
	placeholder,
	disabled = false,
	onFocus,
} ) => {
	return (
		<div className={ 'gutena-forms__text-control gutena-forms-notification-field' }>
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
					type="text"
					value={ value || '' }
					onChange={ ( event ) => onChange( event.target.value ) }
					onFocus={ onFocus }
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

export default GutenaFormsTextField;
