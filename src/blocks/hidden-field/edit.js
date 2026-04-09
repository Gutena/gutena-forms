import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import { select } from '@wordpress/data';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
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
	const { nameAttr, fieldName, defaultValue, description } = attributes;

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
			'wp-block-gutena-field-group wp-block-gutena-hidden-field field-group-type-hidden standalone-hidden-field',
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Field settings', 'gutena-forms' ) }
					initialOpen={ true }
				>
					<TextControl
						label={ __( 'Admin label', 'gutena-forms' ) }
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
						help={ __(
							'Shown in editor only; uses screen reader text on the front.',
							'gutena-forms'
						) }
					/>
					<TextControl
						label={ __( 'Name attribute', 'gutena-forms' ) + ' *' }
						value={ nameAttr ?? '' }
						onChange={ ( v ) =>
							setAttributes( { nameAttr: gfSanitizeName( v ) } )
						}
					/>
					<TextControl
						label={ __( 'Value', 'gutena-forms' ) }
						value={ defaultValue ?? '' }
						onChange={ ( v ) =>
							setAttributes( { defaultValue: v } )
						}
					/>
					<TextControl
						label={ __( 'Notes', 'gutena-forms' ) }
						value={ description ?? '' }
						onChange={ ( v ) =>
							setAttributes( { description: v } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<p
					className="heading-input-label-gutena"
					style={ { fontSize: '12px', opacity: 0.85 } }
				>
					{ __( 'Hidden field', 'gutena-forms' ) }: { fieldName } (
					{ nameAttr }) = { defaultValue || '—' }
				</p>
				<input
					type="hidden"
					name={ nameAttr }
					value={ defaultValue }
					readOnly
					className="gutena-forms-field hidden-field"
				/>
				<p className="gutena-forms-field-error-msg" />
			</div>
		</>
	);
}
