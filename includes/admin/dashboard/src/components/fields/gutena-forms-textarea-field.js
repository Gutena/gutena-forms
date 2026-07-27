import { useState, useEffect } from '@wordpress/element';

const GutenaFormsTextareaField = ( { onChange, label, id, desc, value, placeholder, disabled = false, rows = 5, onFocus } ) => {
	const [ fieldValue, setFieldValue ] = useState( '' );

	useEffect( () => {
		setFieldValue( value || '' );
	}, [ value ] );

	const handleChange = ( event ) => {
		const newValue = event.target.value;
		setFieldValue( newValue );
		if ( onChange ) {
			onChange( newValue );
		}
	};

	return (
		<div className={ 'gutena-forms__textarea-control' }>
			<label className="components-base-control__label" htmlFor={ id }>
				{ label }
			</label>
			<textarea
				id={ id }
				className="gutena-forms__textarea-control-input"
				value={ fieldValue }
				onChange={ handleChange }
				onFocus={ onFocus }
				placeholder={ placeholder }
				disabled={ disabled }
				rows={ rows }
			/>
			{ desc && (
				<p
					className={ 'gutena-forms__field-description' }
					dangerouslySetInnerHTML={ { __html: desc } }
				/>
			) }
		</div>
	);
};

export default GutenaFormsTextareaField;
