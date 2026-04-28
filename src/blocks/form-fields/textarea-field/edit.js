import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, RangeControl } from '@wordpress/components';
import { gfSanitizeName } from '../../../shared/helper';

const Edit = ( { attributes, setAttributes } ) => {
	const {
		nameAttr,
		fieldLabel,
		fieldLabelContent,
		isRequired,
		placeholder,
		defaultValue,
		textAreaRows,
		description,
		errorRequiredMsg,
		errorInvalidInputMsg,
	} = attributes;

	const labelText = fieldLabelContent || fieldLabel;

	useEffect( () => {
		if ( fieldLabelContent ) {
			const nextNameAttr = gfSanitizeName( fieldLabelContent );
			if ( nextNameAttr && nextNameAttr !== nameAttr ) {
				setAttributes( { nameAttr: nextNameAttr } );
			}
		}
	}, [ fieldLabelContent ] );

	const inputClasses = `gutena-forms-field textarea-field ${
		isRequired ? 'required-field' : ''
	}`;

	const blockProps = useBlockProps( {
		className: 'wp-block-gutena-field-group field-group-type-textarea',
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Field settings', 'gutena-forms' ) } initialOpen={ true }>
					<TextControl
						label={ __( 'Label', 'gutena-forms' ) }
						value={ fieldLabelContent ?? '' }
						onChange={ ( value ) => setAttributes( { fieldLabelContent: value } ) }
					/>
					<TextControl
						label={ __( 'Name attribute', 'gutena-forms' ) }
						value={ nameAttr }
						onChange={ ( value ) => setAttributes( { nameAttr: gfSanitizeName( value ) } ) }
					/>
					<TextControl
						label={ __( 'Placeholder', 'gutena-forms' ) }
						value={ placeholder }
						onChange={ ( value ) => setAttributes( { placeholder: value } ) }
					/>
					<ToggleControl
						label={ __( 'Required', 'gutena-forms' ) }
						checked={ isRequired }
						onChange={ ( value ) => setAttributes( { isRequired: value } ) }
					/>
					<TextControl
						label={ __( 'Default value', 'gutena-forms' ) }
						value={ defaultValue }
						onChange={ ( value ) => setAttributes( { defaultValue: value } ) }
					/>
					<RangeControl
						label={ __( 'Textarea rows', 'gutena-forms' ) }
						value={ textAreaRows || 2 }
						onChange={ ( value ) => setAttributes( { textAreaRows: value || 2 } ) }
						min={ 2 }
						max={ 20 }
						step={ 1 }
					/>
					<TextControl
						label={ __( 'Description', 'gutena-forms' ) }
						value={ description }
						onChange={ ( value ) => setAttributes( { description: value } ) }
					/>
					<TextControl
						label={ __( 'Required error message', 'gutena-forms' ) }
						value={ errorRequiredMsg }
						onChange={ ( value ) => setAttributes( { errorRequiredMsg: value } ) }
					/>
					<TextControl
						label={ __( 'Invalid input message', 'gutena-forms' ) }
						value={ errorInvalidInputMsg }
						onChange={ ( value ) => setAttributes( { errorInvalidInputMsg: value } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<label className="heading-input-label-gutena" htmlFor={ nameAttr || '' }>
					{ labelText }
				</label>
				<textarea
					id={ nameAttr || '' }
					name={ nameAttr || '' }
					className={ inputClasses.trim() }
					placeholder={ placeholder || __( 'Type here', 'gutena-forms' ) }
					defaultValue={ defaultValue || '' }
					required={ isRequired }
					rows={ textAreaRows || 2 }
				/>
				{ description ? <p className="gutena-forms-field-description">{ description }</p> : null }
				<p className="gutena-forms-field-error-msg">{ errorRequiredMsg }</p>
			</div>
		</>
	);
};

export default Edit;
