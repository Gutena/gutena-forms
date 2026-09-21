import { useEffect, useState } from '@wordpress/element';
import { TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

const isValidEmail = ( email ) => EMAIL_REGEX.test( email );

const GutenaFormsEmailField = ( {
	desc,
	id,
	label,
	value,
	onChange,
	disabled = false,
	onFocus,
	multiple = false,
	allowMergeTags = false,
	mergeTags = [],
	showValidation = false,
} ) => {
	const [ fieldValue, setFieldValue ] = useState( value || '' );
	const [ showWarning, setShowWarning ] = useState( false );

	useEffect( () => {
		setFieldValue( value || '' );
	}, [ value ] );

	const isAllowedMergeTag = ( emailValue ) => {
		if ( ! allowMergeTags || ! mergeTags.length ) {
			return false;
		}
		return mergeTags.includes( emailValue.trim() );
	};

	const validateValue = ( emailValue ) => {
		const trimmed = String( emailValue || '' ).trim();
		if ( '' === trimmed ) {
			return true;
		}

		if ( ! multiple && allowMergeTags && isAllowedMergeTag( trimmed ) ) {
			return true;
		}

		if ( multiple ) {
			const parts = trimmed.split( ',' ).map( ( part ) => part.trim() ).filter( Boolean );
			return parts.every( ( part ) => isValidEmail( part ) );
		}

		return isValidEmail( trimmed );
	};

	const handleChange = ( newValue ) => {
		setFieldValue( newValue );
		setShowWarning( showValidation && ! validateValue( newValue ) );

		if ( onChange ) {
			onChange( newValue );
		}
	};

	return (
		<div className={ 'gutena-forms__email-control' }>
			<TextControl
				className="gutena-forms__email-control-input"
				id={ id }
				label={ label }
				value={ fieldValue }
				onChange={ handleChange }
				onFocus={ onFocus }
				disabled={ disabled }
			/>
			{ desc && (
				<p className="gutena-forms__field-description">{ desc }</p>
			) }
			{ showWarning && (
				<p className="gutena-forms__field-validation-warning">
					{ __( 'Please enter a valid email address.', 'gutena-forms' ) }
				</p>
			) }
		</div>
	);
};

export default GutenaFormsEmailField;
