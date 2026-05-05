import { useBlockProps } from '@wordpress/block-editor';
import { gfIsEmpty } from '../../../shared/utils/helper';

function getFieldClasses( { isRequired, optionsInline, optionsColumns, autocomplete } ) {
	const parts = [ 'gutena-forms-field', 'radio-field' ];
	if ( isRequired ) {
		parts.push( 'required-field' );
	}
	if ( autocomplete ) {
		parts.push( 'autocomplete' );
	}
	if ( optionsInline ) {
		parts.push( 'inline-options' );
	} else if ( optionsColumns && optionsColumns > 0 ) {
		parts.push( `has-${ optionsColumns }-col` );
	}
	return parts.join( ' ' );
}

export default function Save( { attributes } ) {
	const {
		nameAttr,
		fieldName,
		isRequired,
		selectOptions,
		optionsInline,
		optionsColumns,
		autocomplete,
		description,
	} = attributes;

	const blockProps = useBlockProps.save( {
		className: 'wp-block-gutena-field-group wp-block-gutena-radio-field field-group-type-radio standalone-radio-field',
	} );

	const fieldClasses = getFieldClasses( { isRequired, optionsInline, optionsColumns, autocomplete } );

	return (
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
							<label key={ index } className="radio-container" htmlFor={ optId }>
								<input
									id={ optId }
									type="radio"
									name={ nameAttr }
									value={ item }
								/>
								<span className="checkmark" />
								<div
									style={ { marginLeft: '25px' } }
								>{ item }</div>
							</label>
						);
					} ) }
			</div>
			{ ! gfIsEmpty( description ) && <p className="gutena-forms-radio-field-description">{ description }</p> }
			<p className="gutena-forms-field-error-msg" />
		</div>
	);
}
