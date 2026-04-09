import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import { select } from '@wordpress/data';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';
import { gfIsEmpty, gfSanitizeName } from '../../shared/utils/helper';

const isFieldNameAttrReserved = ( nameAttrCheck, clientIdCheck ) => {
	const blocksClientIds =
		select( 'core/block-editor' ).getClientIdsWithDescendants();
	return gfIsEmpty( blocksClientIds )
		? false
		: blocksClientIds.some( ( blockClientId ) => {
				const attrs =
					select( 'core/block-editor' ).getBlockAttributes(
						blockClientId
					);
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
		isRequired,
		accept,
		allowMultiple,
		description,
	} = attributes;

	useEffect( () => {
		if (
			! gfIsEmpty( nameAttr ) &&
			! isFieldNameAttrReserved( nameAttr, clientId )
		) {
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
		className:
			'wp-block-gutena-field-group wp-block-gutena-file-upload-field field-group-type-file standalone-file-upload-field',
	} );

	const inputName = allowMultiple ? `${ nameAttr }[]` : nameAttr;

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Field settings', 'gutena-forms' ) }
					initialOpen={ true }
				>
					<TextControl
						label={ __( 'Label', 'gutena-forms' ) + ' *' }
						value={ fieldName ?? '' }
						onChange={ ( nextLabel ) => {
							const updates = { fieldName: nextLabel };
							if (
								gfIsEmpty( nameAttr ) ||
								0 === nameAttr.indexOf( 'f_' )
							) {
								updates.nameAttr = gfSanitizeName( nextLabel );
							}
							setAttributes( updates );
						} }
					/>
					<TextControl
						label={ __( 'Name attribute', 'gutena-forms' ) + ' *' }
						value={ nameAttr ?? '' }
						onChange={ ( v ) =>
							setAttributes( { nameAttr: gfSanitizeName( v ) } )
						}
					/>
					<TextControl
						label={ __( 'Accepted types', 'gutena-forms' ) }
						value={ accept ?? '' }
						onChange={ ( v ) => setAttributes( { accept: v } ) }
						help={ __( 'e.g. .pdf,image/*', 'gutena-forms' ) }
					/>
					<ToggleControl
						label={ __( 'Allow multiple files', 'gutena-forms' ) }
						checked={ !! allowMultiple }
						onChange={ ( v ) =>
							setAttributes( { allowMultiple: v } )
						}
					/>
					<ToggleControl
						label={ __( 'Required', 'gutena-forms' ) }
						checked={ !! isRequired }
						onChange={ ( v ) => setAttributes( { isRequired: v } ) }
					/>
					<TextControl
						label={ __( 'Help text', 'gutena-forms' ) }
						value={ description ?? '' }
						onChange={ ( v ) =>
							setAttributes( { description: v } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<label
					htmlFor={ nameAttr }
					className="heading-input-label-gutena"
				>
					{ fieldName }
					{ isRequired ? ' *' : '' }
				</label>
				<input
					id={ nameAttr }
					name={ inputName }
					type="file"
					className={ `gutena-forms-field file-upload-field ${
						isRequired ? 'required-field' : ''
					}` }
					disabled
				/>
				{ ! gfIsEmpty( description ) && (
					<p className="gutena-forms-file-upload-field-description">
						{ description }
					</p>
				) }
				<p className="gutena-forms-field-error-msg" />
			</div>
		</>
	);
}
