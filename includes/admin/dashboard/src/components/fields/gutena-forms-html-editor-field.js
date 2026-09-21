import { __ } from '@wordpress/i18n';
import GutenaFormsNotificationMessageField from '../../../../../../src/shared/components/GutenaFormsNotificationMessageField';

const GutenaFormsHtmlEditorField = ( {
	id,
	label,
	value,
	onChange,
	onFocus,
	disabled = false,
	placeholder = '',
	onRegisterInsert,
	tagItems = [],
} ) => {
	return (
		<div className={ `gutena-forms__html-editor-field${ disabled ? ' is-disabled' : '' }` }>
			<GutenaFormsNotificationMessageField
				id={ id }
				label={ label }
				value={ value }
				onChange={ onChange }
				onFocus={ onFocus }
				disabled={ disabled }
				placeholder={ placeholder }
				onRegisterInsert={ onRegisterInsert }
				formTagItems={ tagItems }
				formTagsButtonLabel={ __( 'Form tags', 'gutena-forms' ) }
				formTagsPopoverTitle={ __( 'Form input Tags', 'gutena-forms' ) }
			/>
		</div>
	);
};

export default GutenaFormsHtmlEditorField;
