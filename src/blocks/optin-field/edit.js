import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import { select } from '@wordpress/data';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';
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

function getFieldClasses( { isRequired, autocomplete } ) {
	const parts = [ 'gutena-forms-field', 'optin-field' ];
	if ( isRequired ) {
		parts.push( 'required-field' );
	}
	if ( autocomplete ) {
		parts.push( 'autocomplete' );
	}
	return parts.join( ' ' );
}

export default function Edit( { attributes, setAttributes, clientId } ) {
	const {
		nameAttr,
		fieldName,
		isRequired,
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

	useEffect( () => {
		if ( ! isRequired ) {
			setAttributes( { isRequired: true } );
		}
	}, [ isRequired ] );

	const blockProps = useBlockProps( {
		className: 'wp-block-gutena-field-group wp-block-gutena-optin-field field-group-type-optin standalone-optin-field',
	} );

	const fieldClasses = getFieldClasses( { isRequired, autocomplete } );

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
					<ToggleControl
						label={ __( 'Required', 'gutena-forms' ) }
						checked={ true }
						disabled
						help={ __( 'Opt-in fields are always required.', 'gutena-forms' ) }
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
				<div className={ fieldClasses }>
					<label className="optin-container" htmlFor={ nameAttr }>
						{ fieldName }
						<input
							id={ nameAttr }
							type="checkbox"
							name={ nameAttr }
							value="1"
							readOnly
						/>
						<span className="checkmark" />
					</label>
				</div>
				{ ! gfIsEmpty( description ) && <p className="gutena-forms-optin-field-description">{ description }</p> }
				<p className="gutena-forms-field-error-msg" />
			</div>
		</>
	);
}
