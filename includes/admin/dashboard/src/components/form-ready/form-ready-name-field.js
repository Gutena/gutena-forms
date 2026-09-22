import { __ } from '@wordpress/i18n';
import { TextControl } from '@wordpress/components';

const FormReadyNameField = ( { value, onChange, disabled = false } ) => (
	<div className="gutena-forms-form-ready__name-field">
		<TextControl
			label={ __( 'Form name', 'gutena-forms' ) }
			value={ value }
			onChange={ onChange }
			disabled={ disabled }
			help={ __( 'This name will be used when your form is created.', 'gutena-forms' ) }
		/>
	</div>
);

export default FormReadyNameField;
