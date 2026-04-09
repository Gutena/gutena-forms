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
		defaultValue,
		minAttr,
		maxAttr,
		stepAttr,
		autocomplete,
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
			'wp-block-gutena-field-group wp-block-gutena-time-field field-group-type-time standalone-time-field',
	} );

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
						label={ __( 'Default value', 'gutena-forms' ) }
						value={ defaultValue ?? '' }
						onChange={ ( v ) =>
							setAttributes( { defaultValue: v } )
						}
						help={ __( 'HH:MM or HH:MM:SS', 'gutena-forms' ) }
					/>
					<TextControl
						label={ __( 'Min', 'gutena-forms' ) }
						value={ minAttr ?? '' }
						onChange={ ( v ) => setAttributes( { minAttr: v } ) }
					/>
					<TextControl
						label={ __( 'Max', 'gutena-forms' ) }
						value={ maxAttr ?? '' }
						onChange={ ( v ) => setAttributes( { maxAttr: v } ) }
					/>
					<TextControl
						label={ __( 'Step', 'gutena-forms' ) }
						value={ stepAttr ?? '' }
						onChange={ ( v ) => setAttributes( { stepAttr: v } ) }
					/>
					<ToggleControl
						label={ __( 'Required', 'gutena-forms' ) }
						checked={ !! isRequired }
						onChange={ ( v ) => setAttributes( { isRequired: v } ) }
					/>
					<ToggleControl
						label={ __( 'Autocomplete', 'gutena-forms' ) }
						checked={ !! autocomplete }
						onChange={ ( v ) =>
							setAttributes( { autocomplete: v } )
						}
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
					name={ nameAttr }
					type="time"
					className={ `gutena-forms-field time-field ${
						isRequired ? 'required-field' : ''
					} ${ autocomplete ? 'autocomplete' : '' }` }
					defaultValue={ defaultValue }
					min={ ! gfIsEmpty( minAttr ) ? minAttr : undefined }
					max={ ! gfIsEmpty( maxAttr ) ? maxAttr : undefined }
					step={ ! gfIsEmpty( stepAttr ) ? stepAttr : undefined }
					readOnly
				/>
				{ ! gfIsEmpty( description ) && (
					<p className="gutena-forms-time-field-description">
						{ description }
					</p>
				) }
				<p className="gutena-forms-field-error-msg" />
			</div>
		</>
	);
}
