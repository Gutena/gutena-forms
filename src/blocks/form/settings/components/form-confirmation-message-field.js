import { useCallback, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import GutenaFormsNotificationMergeTagPopover from '../../../../shared/components/GutenaFormsNotificationMergeTagPopover';

const FormConfirmationMessageField = ( {
	id,
	label,
	value,
	onChange,
	placeholder = '',
	formTagItems = [],
	disabled = false,
} ) => {
	const [ viewMode, setViewMode ] = useState( 'visual' );

	const insertAtCursor = useCallback(
		( text ) => {
			if ( disabled ) {
				return;
			}

			const element = document.getElementById( id );
			const currentValue = value || '';
			const start =
				element && typeof element.selectionStart === 'number'
					? element.selectionStart
					: currentValue.length;
			const end =
				element && typeof element.selectionEnd === 'number'
					? element.selectionEnd
					: start;
			const nextValue = `${ currentValue.slice( 0, start ) }${ text }${ currentValue.slice( end ) }`;
			onChange( nextValue );
			requestAnimationFrame( () => {
				if ( element ) {
					element.focus();
					const cursor = start + text.length;
					element.setSelectionRange( cursor, cursor );
				}
			} );
		},
		[ disabled, id, onChange, value ]
	);

	return (
		<div
			className={ `gutena-forms-form-confirmation-message-field${
				disabled ? ' is-disabled' : ''
			}` }
		>
			<div className="gutena-forms-form-confirmation-message-field__header">
				<label
					className="gutena-forms-form-confirmation-message-field__label"
					htmlFor={ id }
				>
					{ label }
				</label>

				<div className="gutena-forms-form-confirmation-message-field__toolbar">
					<button
						type="button"
						className={ `gutena-forms-form-confirmation-message-field__mode${
							'visual' === viewMode ? ' is-active' : ''
						}` }
						onClick={ () => setViewMode( 'visual' ) }
						disabled={ disabled }
					>
						{ __( 'Visual', 'gutena-forms' ) }
					</button>
					<button
						type="button"
						className={ `gutena-forms-form-confirmation-message-field__mode${
							'code' === viewMode ? ' is-active' : ''
						}` }
						onClick={ () => setViewMode( 'code' ) }
						disabled={ disabled }
					>
						{ __( 'Code', 'gutena-forms' ) }
					</button>
					<GutenaFormsNotificationMergeTagPopover
						variant="form-tags"
						tagItems={ formTagItems }
						onInsert={ insertAtCursor }
						buttonLabel={ __( 'Form tags', 'gutena-forms' ) }
						popoverTitle={ __( 'Form input Tags', 'gutena-forms' ) }
						disabled={ disabled }
					/>
				</div>
			</div>

			<div className="gutena-forms-form-confirmation-message-field__editor">
				{ 'visual' === viewMode && (
					<div
						className="gutena-forms-form-confirmation-message-field__rich-toolbar"
						aria-hidden="true"
					>
						<span>{ __( 'Normal', 'gutena-forms' ) }</span>
					</div>
				) }

				<textarea
					id={ id }
					className={ `gutena-forms-form-confirmation-message-field__textarea${
						'code' === viewMode ? ' is-code' : ''
					}` }
					value={ value || '' }
					onChange={ ( event ) => onChange( event.target.value ) }
					placeholder={ placeholder }
					disabled={ disabled }
					rows={ 6 }
				/>
			</div>
		</div>
	);
};

export default FormConfirmationMessageField;
