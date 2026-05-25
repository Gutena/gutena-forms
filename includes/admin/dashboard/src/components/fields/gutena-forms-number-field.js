import { __experimentalNumberControl as NumberControl } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';

const GutenaFormsNumberField = ( { id, onChange, value, desc, label, min, max, step, disabled = false } ) => {
	const [ numberValue, setNumberValue ] = useState( value || 0 );

	useEffect( () => {
		setNumberValue( value || 0 );
	}, [ value ] );

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
				disabled={ disabled }
			/>
			{ desc && (
				<p className="gutena-forms__field-description">{ desc }</p>
			) }
		</div>
	);
}

export default GutenaFormsNumberField;
