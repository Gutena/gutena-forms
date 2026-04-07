import { ToggleControl } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';

const GutenaFormsToggleField = ( { id, label, desc, checked, onChange, disabled = false } ) => {
	const [ isChecked, setIsChecked ] = useState( checked );

	useEffect( () => {
		setIsChecked( checked );
	}, [ checked ] );

	const handleChange = ( newValue ) => {
		if ( disabled ) {
			return;
		}
		setIsChecked( newValue );
		if ( onChange ) {
			onChange( newValue );
		}
	};

	return (
		<div className={ 'gutena-forms__toggle-control' }>
			<ToggleControl
				className="gutena-forms__toggle-control-input"
				id={ id }
				label={ label }
				checked={ isChecked }
				help={ desc }
				onChange={ handleChange }
				disabled={ disabled }
			/>
		</div>
	);
};

export default GutenaFormsToggleField;
