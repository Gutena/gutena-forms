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
	genericTags = [],
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
				genericTags={ genericTags }
				showFormTagsButton={ false }
			/>
		</div>
	);
};

export default GutenaFormsHtmlEditorField;
