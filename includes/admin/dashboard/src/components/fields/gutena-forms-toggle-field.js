import { ToggleControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

const GutenaFormsToggleField = ( { id, label, desc, checked, onChange } ) => {
	const [ isChecked, setIsChecked ] = useState( checked );

	const handleChange = ( newValue ) => {
		setIsChecked( newValue );
		if ( onChange ) {
			onChange( newValue );
		}
	}

	return (
		<div className={ 'gutena-forms__toggle-control' }>
			<ToggleControl
				className="gutena-forms__toggle-control-input"
				id={ id }
				label={ label }
				checked={ isChecked }
				help={ desc }
				onChange={ handleChange }
			/>
		</div>
	);
};

export default GutenaFormsToggleField;
