import { SelectControl } from '@wordpress/components';

const GutenaFormsSelectField = ( {
	id,
	label,
	desc,
	value,
	options = {},
	onChange,
	disabled = false,
} ) => {
	const selectOptions = Object.keys( options ).map( ( optionKey ) => ( {
		label: options[ optionKey ],
		value: optionKey,
	} ) );

	return (
		<div className={ 'gutena-forms__select-control' }>
			<SelectControl
				className={ 'gutena-forms__select-control-input' }
				id={ id }
				label={ label }
				value={ value || '0' }
				options={ selectOptions }
				onChange={ onChange }
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

export default GutenaFormsSelectField;
