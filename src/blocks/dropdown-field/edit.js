import { __ } from '@wordpress/i18n';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { select } from '@wordpress/data';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, FormTokenField } from '@wordpress/components';
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
	const parts = [ 'gutena-forms-field', 'select-field' ];
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
		selectOptions,
		autocomplete,
		description,
	} = attributes;

	const [ selectedOption, setSelectedOption ] = useState( selectOptions?.[ 0 ] || '' );

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
		if ( gfIsEmpty( selectedOption ) || ! selectOptions.includes( selectedOption ) ) {
			setSelectedOption( selectOptions?.[ 0 ] || '' );
		}
	}, [ selectOptions ] );

	const fieldClasses = useMemo(
		() => getFieldClasses( { isRequired, autocomplete } ),
		[ isRequired, autocomplete ]
	);

	const blockProps = useBlockProps( {
		className: 'wp-block-gutena-field-group wp-block-gutena-dropdown-field field-group-type-select standalone-dropdown-field',
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
					<FormTokenField
						label={ __( 'Options', 'gutena-forms' ) }
						value={ selectOptions }
						suggestions={ selectOptions }
						onChange={ ( nextOptions ) => setAttributes( { selectOptions: nextOptions } ) }
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
				<select
					id={ nameAttr }
					name={ nameAttr }
					className={ fieldClasses }
					value={ selectedOption }
					onChange={ ( e ) => setSelectedOption( e.target.value ) }
				>
					{ Array.isArray( selectOptions ) && selectOptions.map( ( item, index ) => {
						if ( gfIsEmpty( item ) ) {
							return null;
						}
						return (
							<option key={ index } value={ item }>
								{ item }
							</option>
						);
					} ) }
				</select>
				{ ! gfIsEmpty( description ) && <p className="gutena-forms-dropdown-field-description">{ description }</p> }
				<p className="gutena-forms-field-error-msg" />
			</div>
		</>
	);
}
