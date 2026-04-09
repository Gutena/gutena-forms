import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import { select } from '@wordpress/data';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, RangeControl } from '@wordpress/components';
import { gfIsEmpty, gfSanitizeName } from '../../shared/utils/helper';

const isFieldNameAttrReserved = ( nameAttrCheck, clientIdCheck ) => {
	const blocksClientIds = select( 'core/block-editor' ).getClientIdsWithDescendants();
	return gfIsEmpty( blocksClientIds )
		? false
		: blocksClientIds.some( ( blockClientId ) => {
				const attrs = select( 'core/block-editor' ).getBlockAttributes( blockClientId );
				return (
					clientIdCheck !== blockClientId &&
					! gfIsEmpty( attrs?.nameAttr ) &&
					attrs.nameAttr === nameAttrCheck
				);
		  } );
};

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

	useEffect( () => {
		if ( ! gfIsEmpty( nameAttr ) && ! isFieldNameAttrReserved( nameAttr, clientId ) ) {
			return;
		}

		for ( let index = 0; index < 5000; index++ ) {
			const nextName = `f_${ index }`;
			if ( ! isFieldNameAttrReserved( nextName, clientId ) ) {
				setAttributes( { nameAttr: nextName } );
				break;
			}
		}
	}, [] );

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
						onChange={ ( nextLabel ) => {
							const updates = { fieldName: nextLabel };
							if ( gfIsEmpty( nameAttr ) || 0 === nameAttr.indexOf( 'f_' ) ) {
								updates.nameAttr = gfSanitizeName( nextLabel );
							}
							setAttributes( updates );
						} }
					/>
					<TextControl
						label={ __( 'Name attribute', 'gutena-forms' ) + ' *' }
						value={ nameAttr ?? '' }
						onChange={ ( nextNameAttr ) => setAttributes( { nameAttr: gfSanitizeName( nextNameAttr ) } ) }
						help={ __( 'Used as input name in form submission.', 'gutena-forms' ) }
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
				<input
					id={ nameAttr }
					name={ nameAttr }
					type="email"
					className={ `gutena-forms-field email-field ${ isRequired ? 'required-field' : '' } ${ autocomplete ? 'autocomplete' : '' }` }
					placeholder={ placeholder || __( 'Placeholder...', 'gutena-forms' ) }
					defaultValue={ defaultValue }
					maxLength={ maxlength && maxlength > 0 ? maxlength : undefined }
					readOnly
				/>
				{ ! gfIsEmpty( description ) && <p className="gutena-forms-email-field-description">{ description }</p> }
				<p className="gutena-forms-field-error-msg" />
			</div>
		</>
	);
}
