import { __ } from '@wordpress/i18n';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { select } from '@wordpress/data';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	ToggleControl,
	RangeControl,
	FormTokenField,
} from '@wordpress/components';
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

function getFieldClasses( { isRequired, optionsInline, optionsColumns } ) {
	const parts = [ 'gutena-forms-field', 'checkbox-field' ];
	if ( isRequired ) {
		parts.push( 'required-field' );
	}
	if ( optionsInline ) {
		parts.push( 'inline-options' );
	} else if ( optionsColumns && optionsColumns > 0 ) {
		parts.push( `has-${ optionsColumns }-col` );
	}
	return parts.join( ' ' );
}

export default function Edit( { attributes, setAttributes, clientId } ) {
	const {
		nameAttr,
		fieldName,
		isRequired,
		selectOptions,
		optionsInline,
		optionsColumns,
		description,
	} = attributes;

	const [ checked, setChecked ] = useState( {} );

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

	const fieldClasses = useMemo(
		() => getFieldClasses( { isRequired, optionsInline, optionsColumns } ),
		[ isRequired, optionsInline, optionsColumns ]
	);

	const blockProps = useBlockProps( {
		className: 'wp-block-gutena-field-group wp-block-gutena-checkbox-field field-group-type-checkbox standalone-checkbox-field',
	} );

	const nameWithBrackets = `${ nameAttr }[]`;

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
						label={ __( 'Show inline', 'gutena-forms' ) }
						checked={ !! optionsInline }
						onChange={ ( v ) => setAttributes( { optionsInline: v } ) }
					/>
					{ ! optionsInline && (
						<RangeControl
							label={ __( 'Columns', 'gutena-forms' ) }
							value={ optionsColumns ?? 1 }
							onChange={ ( v ) => setAttributes( { optionsColumns: v } ) }
							min={ 1 }
							max={ 6 }
							step={ 1 }
						/>
					) }
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
				<span className="heading-input-label-gutena">
					{ fieldName }
					{ isRequired ? ' *' : '' }
				</span>
				<div className={ fieldClasses }>
					{ Array.isArray( selectOptions ) &&
						selectOptions.map( ( item, index ) => {
							if ( gfIsEmpty( item ) ) {
								return null;
							}
							const optId = `${ nameAttr }_${ index }`;
							return (
								<label key={ index } className="checkbox-container" htmlFor={ optId }>
									{ item }
									<input
										id={ optId }
										type="checkbox"
										name={ nameWithBrackets }
										value={ item }
										checked={ !! checked[ item ] }
										onChange={ ( e ) =>
											setChecked( {
												...checked,
												[ item ]: e.target.checked,
											} )
										}
									/>
									<span className="checkmark" />
								</label>
							);
						} ) }
				</div>
				{ ! gfIsEmpty( description ) && <p className="gutena-forms-checkbox-field-description">{ description }</p> }
				<p className="gutena-forms-field-error-msg" />
			</div>
		</>
	);
}
