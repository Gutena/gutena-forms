import { ToggleControl } from '@wordpress/components';

const GutenaFormsToggleField = ( { id, label, desc, checked, onChange } ) => {
	return (
		<div className={ 'gutena-forms__toggle-control' }>
			<ToggleControl
				className="gutena-forms__toggle-control-input"
				id={ id }
				label={ label }
				checked={ !! checked }
				help={ desc }
				onChange={ onChange }
			/>
		</div>
	);
};

export default GutenaFormsToggleField;
