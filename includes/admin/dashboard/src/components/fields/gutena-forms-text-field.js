import { TextControl } from '@wordpress/components';

const GutenaFormsTextField = ( { onChange, label, id, desc, value, placeholder, disabled = false, onFocus } ) => {
	return (
		<div className={ 'gutena-forms__text-control' }>
			<TextControl
				className={ 'gutena-forms__text-control-input' }
				id={ id }
				label={ label }
				value={ value || '' }
				onChange={ onChange }
				onFocus={ onFocus }
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

export default GutenaFormsTextField;
