import { useBlockProps } from '@wordpress/block-editor';
import { gfIsEmpty } from '../../shared/utils/helper';

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

export default function Save( { attributes } ) {
	const {
		nameAttr,
		fieldName,
		isRequired,
		selectOptions,
		optionsInline,
		optionsColumns,
		description,
	} = attributes;

	const blockProps = useBlockProps.save( {
		className: 'wp-block-gutena-field-group wp-block-gutena-checkbox-field field-group-type-checkbox standalone-checkbox-field',
	} );

	const fieldClasses = getFieldClasses( { isRequired, optionsInline, optionsColumns } );
	const nameWithBrackets = `${ nameAttr }[]`;

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
							<label key={ index } className="checkbox-container" htmlFor={ optId }>
								{ item }
								<input
									id={ optId }
									type="checkbox"
									name={ nameWithBrackets }
									value={ item }
								/>
								<span className="checkmark" />
							</label>
						);
					} ) }
			</div>
			{ ! gfIsEmpty( description ) && <p className="gutena-forms-checkbox-field-description">{ description }</p> }
			<p className="gutena-forms-field-error-msg" />
		</div>
	);
}
