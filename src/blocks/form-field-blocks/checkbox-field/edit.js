import { __ } from '@wordpress/i18n';
import { useMemo, useState } from '@wordpress/element';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	ToggleControl,
	RangeControl,
	FormTokenField,
} from '@wordpress/components';
import FieldIdControl from '../../../shared/components/FieldIdControl';
import { gfIsEmpty } from '../../../shared/utils/helper';
import { useEnsureFieldNameAttr } from '../../../shared/utils/fieldNameAttr';

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

	useEnsureFieldNameAttr( clientId, nameAttr, setAttributes );

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
						onChange={ ( nextLabel ) =>
							setAttributes( { fieldName: nextLabel } )
						}
					/>
					<FieldIdControl nameAttr={ nameAttr } />
					<FormTokenField
						label={ __( 'Options', 'gutena-forms' ) }
						value={ selectOptions }
						suggestions={ selectOptions }
						onChange={ ( nextOptions ) => setAttributes( { selectOptions: nextOptions } ) }
					/>
					<ToggleControl
						label={ __( 'Show inline', 'gutena-forms' ) }
						className="gf-mt-1"
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
