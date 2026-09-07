const GutenaFormsSelectField = ( {
	onChange,
	label,
	id,
	desc,
	value,
	options,
	disabled = false,
} ) => {
	const handleChange = ( event ) => {
		if ( onChange ) {
			onChange( event.target.value );
		}
	};

	return (
		<div className={ 'gutena-forms__select-control' }>
			{ label && (
				<label
					className="components-base-control__label"
					htmlFor={ id }
				>
					{ label }
				</label>
			) }
			<select
				id={ id }
				className="gutena-forms__select-control-input"
				value={ value ?? '' }
				onChange={ handleChange }
				disabled={ disabled }
			>
				{ options &&
					Object.keys( options ).map( ( optionKey ) => (
						<option key={ optionKey } value={ optionKey }>
							{ options[ optionKey ] }
						</option>
					) ) }
			</select>
			{ desc && (
				<p
					className={ 'gutena-forms__field-description' }
					dangerouslySetInnerHTML={ { __html: desc } }
				/>
			) }
		</div>
	);
};

export default GutenaFormsSelectField;
