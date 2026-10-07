import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, RangeControl } from '@wordpress/components';
import FieldIdControl from '../../../shared/components/FieldIdControl';
import { gfIsEmpty, gfGetAutocompleteAttr } from '../../../shared/utils/helper';
import { useEnsureFieldNameAttr } from '../../../shared/utils/fieldNameAttr';

export default function Edit( { attributes, setAttributes, clientId } ) {
	const {
		nameAttr,
		fieldName,
		placeholder,
		isRequired,
		defaultValue,
		maxlength,
		autocomplete,
		description,
	} = attributes;

	useEnsureFieldNameAttr( clientId, nameAttr, setAttributes );

	const blockProps = useBlockProps( {
		className: 'wp-block-gutena-field-group wp-block-gutena-email-field field-group-type-email standalone-email-field',
	} );

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
					<RangeControl
						label={ __( 'Maxlength', 'gutena-forms' ) }
						value={ maxlength ?? 0 }
						onChange={ ( nextMaxLength ) => setAttributes( { maxlength: nextMaxLength } ) }
						min={ 0 }
						max={ 500 }
						step={ 25 }
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
						type="email"
						className={ `gutena-forms-field email-field ${ isRequired ? 'required-field' : '' } ${ autocomplete ? 'autocomplete' : '' }` }
						placeholder={ placeholder || __( 'Placeholder...', 'gutena-forms' ) }
						defaultValue={ defaultValue }
						maxLength={ maxlength && maxlength > 0 ? maxlength : undefined }
						autoComplete={ gfGetAutocompleteAttr( autocomplete, 'email' ) }
						readOnly
					/>
				</div>
				{ ! gfIsEmpty( description ) && <p className="gutena-forms-email-field-description">{ description }</p> }
				<p className="gutena-forms-field-error-msg" />
			</div>
		</>
	);
}
