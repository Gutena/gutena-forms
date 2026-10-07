import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, RangeControl } from '@wordpress/components';
import FieldIdControl from '../../../shared/components/FieldIdControl';
import { gfIsEmpty } from '../../../shared/utils/helper';
import { useEnsureFieldNameAttr } from '../../../shared/utils/fieldNameAttr';

export default function Edit( { attributes, setAttributes, clientId } ) {
	const {
		nameAttr,
		fieldName,
		placeholder,
		isRequired,
		defaultValue,
		textAreaRows,
		maxlength,
		description,
	} = attributes;

	useEnsureFieldNameAttr( clientId, nameAttr, setAttributes );

	const blockProps = useBlockProps( {
		className: 'wp-block-gutena-field-group wp-block-gutena-textarea-field field-group-type-textarea standalone-textarea-field',
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
					<RangeControl
						label={ __( 'Textarea rows', 'gutena-forms' ) }
						value={ textAreaRows ?? 5 }
						onChange={ ( nextRows ) => setAttributes( { textAreaRows: nextRows } ) }
						min={ 2 }
						max={ 20 }
						step={ 1 }
					/>
					<RangeControl
						label={ __( 'Maxlength', 'gutena-forms' ) }
						value={ maxlength ?? 0 }
						onChange={ ( nextMaxLength ) => setAttributes( { maxlength: nextMaxLength } ) }
						min={ 0 }
						max={ 500 }
						step={ 25 }
					/>
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
					<textarea
						id={ nameAttr }
						name={ nameAttr }
						className={ `gutena-forms-field textarea-field ${ isRequired ? 'required-field' : '' }` }
						placeholder={ placeholder || __( 'Placeholder...', 'gutena-forms' ) }
						rows={ textAreaRows && textAreaRows > 0 ? textAreaRows : 5 }
						maxLength={ maxlength && maxlength > 0 ? maxlength : undefined }
						defaultValue={ defaultValue }
						readOnly
					/>
				</div>
				{ ! gfIsEmpty( description ) && <p className="gutena-forms-textarea-field-description">{ description }</p> }
				<p className="gutena-forms-field-error-msg" />
			</div>
		</>
	);
}
