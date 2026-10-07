import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	PanelRow,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import FieldIdControl from '../../../shared/components/FieldIdControl';
import { gfIsEmpty, gfGetAutocompleteAttr } from '../../../shared/utils/helper';
import { useEnsureFieldNameAttr } from '../../../shared/utils/fieldNameAttr';

function numAttr( v ) {
	if ( gfIsEmpty( v ) && v !== 0 && v !== '0' ) {
		return undefined;
	}
	return v;
}

export default function Edit( { attributes, setAttributes, clientId } ) {
	const {
		nameAttr,
		fieldName,
		placeholder,
		isRequired,
		defaultValue,
		minMaxStep,
		autocomplete,
		description,
	} = attributes;

	useEnsureFieldNameAttr( clientId, nameAttr, setAttributes );

	const blockProps = useBlockProps( {
		className: 'wp-block-gutena-field-group wp-block-gutena-number-field field-group-type-number standalone-number-field',
	} );

	const min = numAttr( minMaxStep?.min );
	const max = numAttr( minMaxStep?.max );
	const step = numAttr( minMaxStep?.step );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Field settings', 'gutena-forms' ) } initialOpen={ true }>
					<TextControl
						label={ __( 'Label', 'gutena-forms' ) + ' *' }
						value={ fieldName ?? '' }
						onChange={ ( nextLabel ) =>
							setAttributes( { fieldName: nextLabel } )
						}
					/>
					<FieldIdControl nameAttr={ nameAttr } />
					<PanelRow className="gf-child-mb-0 gf-mb-24">
						<TextControl
							label={ __( 'Minimum', 'gutena-forms' ) }
							value={ minMaxStep?.min }
							type="number"
							onChange={ ( min ) =>
								setAttributes( {
									minMaxStep: {
										...minMaxStep,
										min,
									},
								} )
							}
						/>
						<TextControl
							label={ __( 'Maximum', 'gutena-forms' ) }
							value={ minMaxStep?.max }
							type="number"
							onChange={ ( max ) =>
								setAttributes( {
									minMaxStep: {
										...minMaxStep,
										max,
									},
								} )
							}
						/>
						<TextControl
							label={ __( 'Step', 'gutena-forms' ) }
							value={ minMaxStep?.step }
							type="number"
							onChange={ ( step ) =>
								setAttributes( {
									minMaxStep: {
										...minMaxStep,
										step,
									},
								} )
							}
						/>
					</PanelRow>
					<TextControl
						label={ __( 'Placeholder', 'gutena-forms' ) }
						value={ placeholder ?? '' }
						onChange={ ( nextPlaceholder ) => setAttributes( { placeholder: nextPlaceholder } ) }
					/>
					<TextControl
						label={ __( 'Default value', 'gutena-forms' ) }
						value={ defaultValue ?? '' }
						onChange={ ( nextDefaultValue ) => setAttributes( { defaultValue: nextDefaultValue } ) }
					/>
					<ToggleControl
						label={ __( 'Required', 'gutena-forms' ) }
						checked={ !! isRequired }
						onChange={ ( nextRequired ) => setAttributes( { isRequired: nextRequired } ) }
					/>
					<ToggleControl
						label={ __( 'Autocomplete', 'gutena-forms' ) }
						checked={ !! autocomplete }
						onChange={ ( nextAutocomplete ) => setAttributes( { autocomplete: nextAutocomplete } ) }
					/>
					<TextControl
						label={ __( 'Help text', 'gutena-forms' ) }
						value={ description ?? '' }
						onChange={ ( nextDescription ) => setAttributes( { description: nextDescription } ) }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<label htmlFor={ nameAttr } className="heading-input-label-gutena">
					{ fieldName }
					{ isRequired ? ' *' : '' }
				</label>
				<div
					className={ 'wp-block-gutena-form-field' }
				>
					<input
						id={ nameAttr }
						name={ nameAttr }
						type="number"
						className={ `gutena-forms-field number-field ${ isRequired ? 'required-field' : '' } ${ autocomplete ? 'autocomplete' : '' }` }
						placeholder={ placeholder || __( 'Placeholder...', 'gutena-forms' ) }
						defaultValue={ defaultValue }
						min={ min }
						max={ max }
						step={ step }
						autoComplete={ gfGetAutocompleteAttr( autocomplete, 'on' ) }
						readOnly
					/>
				</div>
				{ ! gfIsEmpty( description ) && <p className="gutena-forms-number-field-description">{ description }</p> }
				<p className="gutena-forms-field-error-msg" />
			</div>
		</>
	);
}
