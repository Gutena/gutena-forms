import { __experimentalNumberControl as NumberControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

const GutenaFormsNumberField = ( { id, onChange, value, desc, label, min, max, step } ) => {
	const [ numberValue, setNumberValue ] = useState( value || 0 );

	const handleChange = ( newValue ) => {
		setNumberValue( newValue );
		if ( onChange ) {
			onChange( newValue );
		}
	}

	return (
		<div className={ 'gutena-forms__number-control' }>
			<NumberControl
				className="gutena-forms__number-control-input"
				id={ id }
				label={ label }
				value={ numberValue }
				onChange={ handleChange }
				min={ min }
				max={ max }
				step={ step }
			/>
			{ desc && (
				<p className="gutena-forms__field-description">{ desc }</p>
			) }
		</div>
	);
}

export default GutenaFormsNumberField;
