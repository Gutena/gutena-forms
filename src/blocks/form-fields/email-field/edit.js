import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';
import { gfSanitizeName } from '../../../shared/helper';

const Edit = ( { attributes, setAttributes } ) => {
	const {
		nameAttr,
		fieldLabel,
		fieldLabelContent,
		isRequired,
		placeholder,
		defaultValue,
		autocomplete,
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

	const inputClasses = `gutena-forms-field email-field ${
		isRequired ? 'required-field' : ''
	} ${ autocomplete ? 'autocomplete' : '' }`;

	const blockProps = useBlockProps( {
		className: 'wp-block-gutena-field-group field-group-type-email',
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
					<ToggleControl
						label={ __( 'Autocomplete', 'gutena-forms' ) }
						checked={ autocomplete }
						onChange={ ( value ) => setAttributes( { autocomplete: value } ) }
					/>
					<TextControl
						label={ __( 'Default value', 'gutena-forms' ) }
						value={ defaultValue }
						onChange={ ( value ) => setAttributes( { defaultValue: value } ) }
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
						label={ __( 'Invalid email message', 'gutena-forms' ) }
						value={ errorInvalidInputMsg }
						onChange={ ( value ) => setAttributes( { errorInvalidInputMsg: value } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<label className="heading-input-label-gutena" htmlFor={ nameAttr || '' }>
					{ labelText }
				</label>
				<input
					id={ nameAttr || '' }
					type="email"
					name={ nameAttr || '' }
					className={ inputClasses.trim() }
					placeholder={ placeholder || __( 'example@email.com', 'gutena-forms' ) }
					defaultValue={ defaultValue || '' }
					required={ isRequired }
				/>
				{ description ? <p className="gutena-forms-field-description">{ description }</p> : null }
				<p className="gutena-forms-field-error-msg">{ errorRequiredMsg }</p>
			</div>
		</>
	);
};

export default Edit;
