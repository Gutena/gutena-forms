import { useState } from '@wordpress/element';
import { TextControl } from '@wordpress/components';

const GutenaFormsEmailField = ( { desc, id, label, value, onChange } ) => {
	const [ fieldValue, setFieldValue ] = useState( value || '' );

	const validateEmail = ( email ) => {
		const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		return emailRegex.test( email );
	};
	const handleChange = ( newValue ) => {
		setFieldValue( newValue );
		if ( onChange ) {
			var emails = String( newValue ).split( ',' );
			emails = emails.map( email => email.trim() ).filter( email => validateEmail( email ) ).join( ', ' );
			onChange( emails );
		}
	}

	return (
		<div className={ 'gutena-forms__email-control' }>
			<TextControl
				className="gutena-forms__email-control-input"
				id={ id }
				label={ label }
				value={ fieldValue }
				onChange={ handleChange }
			/>
			{ desc && (
				<p className="gutena-forms__field-description">{ desc }</p>
			) }
		</div>
	);
}

export default GutenaFormsEmailField;
