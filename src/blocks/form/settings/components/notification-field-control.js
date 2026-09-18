import GutenaFormsNotificationMergeTagPopover from '../../../../shared/components/GutenaFormsNotificationMergeTagPopover';

const NotificationFieldControl = ( {
	id,
	label,
	value,
	onChange,
	onFocus,
	placeholder,
	required = false,
	helpText = '',
	mergeTags = [],
} ) => {
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
					type="text"
					value={ value || '' }
					onChange={ ( event ) => onChange( event.target.value ) }
					onFocus={ onFocus }
					placeholder={ placeholder }
				/>

				{ mergeTags.length > 0 && (
					<GutenaFormsNotificationMergeTagPopover
						tags={ mergeTags }
						onInsert={ ( tag ) => {
							const currentValue = value || '';
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
							onChange( nextValue );
							requestAnimationFrame( () => {
								if ( element ) {
									element.focus();
									const cursor = start + tag.length;
									element.setSelectionRange( cursor, cursor );
								}
							} );
						} }
					/>
				) }
			</div>

			{ helpText && (
				<p className="gutena-forms-notification-field__help">{ helpText }</p>
			) }
		</div>
	);
};

export default NotificationFieldControl;
