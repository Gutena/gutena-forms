import { TextControl } from '@wordpress/components';

const GutenaFormsUrlField = ( {
	id,
	label,
	desc,
	value,
	placeholder,
	onChange,
	disabled = false,
} ) => {
	return (
		<div className={ 'gutena-forms__url-control' }>
			<TextControl
				className={ 'gutena-forms__url-control-input' }
				id={ id }
				label={ label }
				type="url"
				value={ value || '' }
				onChange={ onChange }
				placeholder={ placeholder }
				disabled={ disabled }
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

export default GutenaFormsUrlField;
