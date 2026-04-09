import { useBlockProps } from '@wordpress/block-editor';
import { gfIsEmpty } from '../../shared/utils/helper';

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

export default function Save( { attributes } ) {
	const {
		nameAttr,
		fieldName,
		isRequired,
		selectOptions,
		autocomplete,
		description,
	} = attributes;

	const blockProps = useBlockProps.save( {
		className: 'wp-block-gutena-field-group wp-block-gutena-dropdown-field field-group-type-select standalone-dropdown-field',
	} );

	const fieldClasses = getFieldClasses( { isRequired, autocomplete } );

	return (
		<div { ...blockProps }>
			<label htmlFor={ nameAttr } className="heading-input-label-gutena">
				{ fieldName }
				{ isRequired ? ' *' : '' }
			</label>
			<select
				id={ nameAttr }
				name={ nameAttr }
				className={ fieldClasses }
				required={ isRequired ? 'required' : undefined }
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
	);
}
