const GutenaFormsTextareaField = ( { onChange, label, id, desc, value, placeholder, disabled = false, rows = 5, onFocus } ) => {
	const handleChange = ( event ) => {
		if ( onChange ) {
			onChange( event.target.value );
		}
	};

	return (
		<div className={ 'gutena-forms__textarea-control gutena-forms-notification-field' }>
			<label className="gutena-forms-notification-field__label" htmlFor={ id }>
				{ label }
			</label>
			<textarea
				id={ id }
				className="gutena-forms__textarea-control-input"
				value={ value || '' }
				onChange={ handleChange }
				onFocus={ onFocus }
				placeholder={ placeholder }
				disabled={ disabled }
				rows={ rows }
			/>
			{ desc && (
				<p
					className={ 'gutena-forms-notification-field__help' }
					dangerouslySetInnerHTML={ { __html: desc } }
				/>
			) }
		</div>
	);
};

export default GutenaFormsTextareaField;
