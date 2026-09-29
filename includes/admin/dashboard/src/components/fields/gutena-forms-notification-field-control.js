import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import GutenaFormsNotificationMergeTagPopover from '../../../../../../src/shared/components/GutenaFormsNotificationMergeTagPopover';

const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

const isValidEmail = ( email ) => EMAIL_REGEX.test( email );

const GutenaFormsNotificationFieldControl = ( {
	id,
	label,
	value,
	onChange,
	onFocus,
	placeholder,
	required = false,
	helpText = '',
	mergeTags = [],
	tagItems = [],
	type = 'text',
	disabled = false,
	showValidation = false,
	allowMergeTags = false,
	multiple = false,
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
			return parts.every( ( part ) => isValidEmail( part ) || ( allowMergeTags && mergeTags.includes( part ) ) );
		}

		return isValidEmail( trimmed );
	};

	const handleChange = ( newValue ) => {
		setFieldValue( newValue );
		if ( showValidation ) {
			setShowWarning( ! validateValue( newValue ) );
		}
		if ( onChange ) {
			onChange( newValue );
		}
	};

	const handleInsertTag = ( tag ) => {
		const currentValue = fieldValue || '';
		const element = document.getElementById( id );
		const start =
			element && typeof element.selectionStart === 'number'
				? element.selectionStart
				: currentValue.length;
		const end =
			element && typeof element.selectionEnd === 'number'
				? element.selectionEnd
				: start;
		const nextValue = `${ currentValue.slice( 0, start ) }${ tag }${ currentValue.slice( end ) }`;
		handleChange( nextValue );
		requestAnimationFrame( () => {
			if ( element ) {
				element.focus();
				const cursor = start + tag.length;
				element.setSelectionRange( cursor, cursor );
			}
		} );
	};

	const resolvedTagItems = tagItems.length
		? tagItems
		: mergeTags.map( ( tag ) => ( {
			label: tag
				.replace( /^\{|\}$/g, '' )
				.replace( /[-_:]/g, ' ' )
				.replace( /\b\w/g, ( char ) => char.toUpperCase() ),
			tag,
		} ) );

	return (
		<div className="gutena-forms-notification-field">
			<label className="gutena-forms-notification-field__label" htmlFor={ id }>
				{ label }
				{ required && (
					<span className="gutena-forms-notification-field__required">*</span>
				) }
			</label>

			<div className="gutena-forms-notification-field__row">
				<input
					id={ id }
					className="gutena-forms-notification-field__input"
					type={ type }
					value={ fieldValue }
					onChange={ ( event ) => handleChange( event.target.value ) }
					onFocus={ onFocus }
					placeholder={ placeholder }
					disabled={ disabled }
				/>

				{ mergeTags.length > 0 && (
					<GutenaFormsNotificationMergeTagPopover
						tagItems={ resolvedTagItems }
						onInsert={ handleInsertTag }
						popoverTitle={ __( 'Generic Tags', 'gutena-forms' ) }
						disabled={ disabled }
					/>
				) }
			</div>

			{ helpText && (
				<p className="gutena-forms-notification-field__help">{ helpText }</p>
			) }

			{ showValidation && showWarning && (
				<div className="gutena-forms-notification-field-warning-box">
					<span
						className="gutena-forms-notification-field-warning-box__icon dashicons dashicons-warning"
						aria-hidden="true"
					/>
					<p>
						{ __(
							"Please enter a valid email address. Your notifications won't be sent if the field is not filled in correctly.",
							'gutena-forms'
						) }
					</p>
				</div>
			) }
		</div>
	);
};

export default GutenaFormsNotificationFieldControl;
