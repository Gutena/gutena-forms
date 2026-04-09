import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
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

	const [ showPw, setShowPw ] = useState( false );

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
		className: 'wp-block-gutena-field-group wp-block-gutena-password-field field-group-type-password standalone-password-field',
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
						onChange={ ( v ) => setAttributes( { nameAttr: gfSanitizeName( v ) } ) }
						help={ __( 'Used as input name in form submission.', 'gutena-forms' ) }
					/>
					<TextControl label={ __( 'Placeholder', 'gutena-forms' ) } value={ placeholder ?? '' } onChange={ ( v ) => setAttributes( { placeholder: v } ) } />
					<TextControl label={ __( 'Default value', 'gutena-forms' ) } value={ defaultValue ?? '' } onChange={ ( v ) => setAttributes( { defaultValue: v } ) } />
					<RangeControl label={ __( 'Maxlength', 'gutena-forms' ) } value={ maxlength ?? 0 } onChange={ ( v ) => setAttributes( { maxlength: v } ) } min={ 0 } max={ 200 } step={ 5 } />
					<ToggleControl label={ __( 'Required', 'gutena-forms' ) } checked={ !! isRequired } onChange={ ( v ) => setAttributes( { isRequired: v } ) } />
					<ToggleControl label={ __( 'Autocomplete', 'gutena-forms' ) } checked={ !! autocomplete } onChange={ ( v ) => setAttributes( { autocomplete: v } ) } />
					<ToggleControl label={ __( 'Show characters in editor', 'gutena-forms' ) } checked={ showPw } onChange={ setShowPw } />
					<TextControl label={ __( 'Help text', 'gutena-forms' ) } value={ description ?? '' } onChange={ ( v ) => setAttributes( { description: v } ) } />
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
					type={ showPw ? 'text' : 'password' }
					className={ `gutena-forms-field password-field ${ isRequired ? 'required-field' : '' } ${ autocomplete ? 'autocomplete' : '' }` }
					placeholder={ placeholder || '••••••••' }
					defaultValue={ defaultValue }
					maxLength={ maxlength && maxlength > 0 ? maxlength : undefined }
					readOnly
				/>
				{ ! gfIsEmpty( description ) && <p className="gutena-forms-password-field-description">{ description }</p> }
				<p className="gutena-forms-field-error-msg" />
			</div>
		</>
	);
}
