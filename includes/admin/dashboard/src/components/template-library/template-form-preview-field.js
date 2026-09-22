import { __ } from '@wordpress/i18n';
import { normalizeTemplateField } from '../../utils/template-field-utils';

const getFieldGroupClass = ( field ) => {
	switch ( field.type ) {
		case 'email':
			return 'wp-block-gutena-email-field field-group-type-email standalone-email-field';
		case 'textarea':
			return 'wp-block-gutena-textarea-field field-group-type-textarea standalone-textarea-field';
		case 'number':
			return 'wp-block-gutena-number-field field-group-type-number standalone-number-field';
		case 'select':
			return 'wp-block-gutena-dropdown-field field-group-type-select standalone-dropdown-field';
		case 'radio':
			return 'wp-block-gutena-radio-field field-group-type-radio standalone-radio-field';
		case 'checkbox':
			return 'wp-block-gutena-checkbox-field field-group-type-checkbox standalone-checkbox-field';
		case 'range':
			return 'wp-block-gutena-range-field field-group-type-range standalone-range-field';
		case 'optin':
			return 'wp-block-gutena-optin-field field-group-type-optin standalone-optin-field';
		case 'file':
			return 'wp-block-gutena-file-upload-field field-group-type-file standalone-file-upload-field';
		default:
			return 'wp-block-gutena-text-field field-group-type-text standalone-text-field';
	}
};

const getFieldClasses = ( field, baseClass ) => {
	const parts = [ 'gutena-forms-field', baseClass ];

	if ( field.required ) {
		parts.push( 'required-field' );
	}

	return parts.join( ' ' );
};

const FieldError = ( { error, errorId } ) => {
	if ( ! error ) {
		return null;
	}

	return (
		<p
			id={ errorId }
			className="gutena-forms-field-error-msg has-error"
			role="alert"
		>
			{ error }
		</p>
	);
};

const getFieldAriaProps = ( inputId, error ) => {
	if ( ! error ) {
		return {
			inputAriaProps: {},
			errorId: undefined,
		};
	}

	const errorId = `${ inputId }-error`;

	return {
		inputAriaProps: {
			'aria-invalid': true,
			'aria-describedby': errorId,
		},
		errorId,
	};
};

const TemplateFormPreviewField = ( {
	field: rawField,
	index,
	value,
	onChange,
	error,
} ) => {
	const field = normalizeTemplateField( rawField, index );
	const { attributes } = field;
	const inputId = attributes.nameAttr || field.id;
	const { inputAriaProps, errorId } = getFieldAriaProps( inputId, error );

	const handleInputChange = ( event ) => {
		onChange( field.id, event.target.value );
	};

	const handleCheckboxChange = ( optionValue, checked ) => {
		const current = Array.isArray( value ) ? value : [];

		if ( checked ) {
			onChange( field.id, [ ...current, optionValue ] );
			return;
		}

		onChange( field.id, current.filter( ( item ) => item !== optionValue ) );
	};

	const handleFileChange = ( event ) => {
		const file = event.target.files?.[ 0 ] || null;
		onChange( field.id, file );
	};

	if ( 'radio' === field.type ) {
		const fieldClasses = getFieldClasses( field, 'radio-field' );

		return (
			<div className={ `wp-block-gutena-field-group ${ getFieldGroupClass( field ) }` }>
				<fieldset>
					<legend>
						<span className="heading-input-label-gutena">
							{ field.label }
							{ field.required ? ' *' : '' }
						</span>
					</legend>
					<div className={ fieldClasses }>
						{ field.options.map( ( option, optionIndex ) => {
							const optId = `${ inputId }_${ optionIndex }`;
							return (
								<label key={ optId } className="radio-container" htmlFor={ optId }>
									<div>{ option }</div>
									<div>
										<input
											id={ optId }
											type="radio"
											name={ inputId }
											value={ option }
											checked={ value === option }
											onChange={ () => onChange( field.id, option ) }
										/>
										<span className="checkmark" />
									</div>
								</label>
							);
						} ) }
					</div>
				</fieldset>
				{ field.description && (
					<p className="gutena-forms-radio-field-description">{ field.description }</p>
				) }
				<FieldError error={ error } errorId={ errorId } />
			</div>
		);
	}

	if ( 'checkbox' === field.type ) {
		const fieldClasses = getFieldClasses( field, 'checkbox-field' );
		const selected = Array.isArray( value ) ? value : [];

		return (
			<div className={ `wp-block-gutena-field-group ${ getFieldGroupClass( field ) }` }>
				<fieldset>
					<legend>
						<span className="heading-input-label-gutena">
							{ field.label }
							{ field.required ? ' *' : '' }
						</span>
					</legend>
					<div className={ fieldClasses }>
						{ field.options.map( ( option, optionIndex ) => {
							const optId = `${ inputId }_${ optionIndex }`;
							return (
								<label key={ optId } className="checkbox-container" htmlFor={ optId }>
									{ option }
									<input
										id={ optId }
										type="checkbox"
										name={ `${ inputId }[]` }
										value={ option }
										checked={ selected.includes( option ) }
										onChange={ ( event ) => handleCheckboxChange( option, event.target.checked ) }
									/>
									<span className="checkmark" />
								</label>
							);
						} ) }
					</div>
				</fieldset>
				{ field.description && (
					<p className="gutena-forms-checkbox-field-description">{ field.description }</p>
				) }
				<FieldError error={ error } errorId={ errorId } />
			</div>
		);
	}

	if ( 'optin' === field.type ) {
		const fieldClasses = getFieldClasses( field, 'optin-field' );

		return (
			<div className={ `wp-block-gutena-field-group ${ getFieldGroupClass( field ) }` }>
				<div className={ fieldClasses }>
					<label className="optin-container" htmlFor={ inputId }>
						{ field.label }
						<input
							id={ inputId }
							type="checkbox"
							name={ inputId }
							value="1"
							checked={ !! value }
							onChange={ ( event ) => onChange( field.id, event.target.checked ) }
							aria-invalid={ error ? true : undefined }
							aria-describedby={ errorId }
						/>
						<span className="checkmark" />
					</label>
				</div>
				{ field.description && (
					<p className="gutena-forms-optin-field-description">{ field.description }</p>
				) }
				<FieldError error={ error } errorId={ errorId } />
			</div>
		);
	}

	return (
		<div className={ `wp-block-gutena-field-group ${ getFieldGroupClass( field ) }` }>
			{ 'file' !== field.type && (
				<label className="heading-input-label-gutena" htmlFor={ inputId }>
					{ field.label }
					{ field.required ? ' *' : '' }
				</label>
			) }

			<div className="wp-block-gutena-form-field">
				{ 'textarea' === field.type && (
					<textarea
						id={ inputId }
						name={ inputId }
						className={ getFieldClasses( field, 'textarea-field' ) }
						placeholder={ field.placeholder }
						rows={ attributes.textAreaRows || 5 }
						value={ value || '' }
						onChange={ handleInputChange }
						{ ...inputAriaProps }
					/>
				) }

				{ 'select' === field.type && (
					<select
						id={ inputId }
						name={ inputId }
						className={ getFieldClasses( field, 'select-field' ) }
						value={ value || '' }
						onChange={ handleInputChange }
						{ ...inputAriaProps }
					>
						{ field.required && (
							<option value="">{ __( 'Select an Option', 'gutena-forms' ) }</option>
						) }
						{ field.options.map( ( option, optionIndex ) => (
							<option key={ optionIndex } value={ option }>{ option }</option>
						) ) }
					</select>
				) }

				{ 'range' === field.type && (
					<input
						id={ inputId }
						name={ inputId }
						type="range"
						className={ getFieldClasses( field, 'range-field' ) }
						min={ attributes.min ?? 0 }
						max={ attributes.max ?? 100 }
						value={ value ?? attributes.min ?? 0 }
						onChange={ handleInputChange }
						{ ...inputAriaProps }
					/>
				) }

				{ 'file' === field.type && (
					<div className="gutena-forms-file-upload-preview">
						<label className="heading-input-label-gutena" htmlFor={ inputId }>
							{ field.label }
							{ field.required ? ' *' : '' }
						</label>
						<input
							id={ inputId }
							name={ inputId }
							type="file"
							className={ getFieldClasses( field, 'file-field' ) }
							onChange={ handleFileChange }
							{ ...inputAriaProps }
						/>
						{ value?.name && (
							<p className="gutena-forms-file-upload-preview__name">{ value.name }</p>
						) }
						<p className="gutena-forms-file-upload-preview__note">
							{ __( 'Files selected here are for preview only and are not uploaded.', 'gutena-forms' ) }
						</p>
					</div>
				) }

				{ [ 'text', 'email', 'number' ].includes( field.type ) && (
					<input
						id={ inputId }
						name={ inputId }
						type={ field.type }
						className={ getFieldClasses( field, `${ field.type }-field` ) }
						placeholder={ field.placeholder }
						value={ value || '' }
						onChange={ handleInputChange }
						{ ...inputAriaProps }
					/>
				) }
			</div>

			{ field.description && 'file' !== field.type && (
				<p className="gutena-forms-text-field-description">{ field.description }</p>
			) }
			<FieldError error={ error } errorId={ errorId } />
		</div>
	);
};

export default TemplateFormPreviewField;
