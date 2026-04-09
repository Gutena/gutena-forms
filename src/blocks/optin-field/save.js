import { useBlockProps } from '@wordpress/block-editor';
import { gfIsEmpty } from '../../shared/utils/helper';

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

export default function Save( { attributes } ) {
	const {
		nameAttr,
		fieldName,
		isRequired,
		autocomplete,
		description,
	} = attributes;

	const blockProps = useBlockProps.save( {
		className: 'wp-block-gutena-field-group wp-block-gutena-optin-field field-group-type-optin standalone-optin-field',
	} );

	const fieldClasses = getFieldClasses( {
		isRequired: true === isRequired || undefined === isRequired,
		autocomplete,
	} );

	return (
		<div { ...blockProps }>
			<div className={ fieldClasses }>
				<label className="optin-container" htmlFor={ nameAttr }>
					{ fieldName }
					<input id={ nameAttr } type="checkbox" name={ nameAttr } value="1" />
					<span className="checkmark" />
				</label>
			</div>
			{ ! gfIsEmpty( description ) && <p className="gutena-forms-optin-field-description">{ description }</p> }
			<p className="gutena-forms-field-error-msg" />
		</div>
	);
}
