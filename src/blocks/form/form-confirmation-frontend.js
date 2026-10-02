/**
 * Frontend form confirmation handling.
 */

const SKIPPED_FORM_DATA_KEYS = new Set( [
	'nonce',
	'action',
	'formid',
	'redirect_url',
	'recaptcha_enable',
	'turnstile_enable',
	'g-recaptcha-response',
	'cf-turnstile-response',
] );

const isEmpty = ( value ) =>
	'undefined' === typeof value || null === value || '' === value;

const hasClass = ( element, className ) =>
	( ' ' + element.className + ' ' ).indexOf( ' ' + className + ' ' ) > -1;

const escapeHtml = ( value ) => {
	const element = document.createElement( 'div' );
	element.textContent = String( value );
	return element.innerHTML;
};

export const getFormConfirmationConfig = ( form ) => {
	if ( ! form ) {
		return null;
	}

	const raw = form.getAttribute( 'data-form-confirmation' );
	if ( isEmpty( raw ) ) {
		return null;
	}

	try {
		const config = JSON.parse( raw );
		return config && true === config.enabled ? config : null;
	} catch ( error ) {
		return null;
	}
};

export const isFormConfirmationEnabled = ( config ) =>
	! isEmpty( config ) && true === config.enabled;

const buildFieldValueMap = ( formData ) => {
	const fields = {};

	if ( ! formData ) {
		return fields;
	}

	formData.forEach( ( value, key ) => {
		if ( SKIPPED_FORM_DATA_KEYS.has( key ) ) {
			return;
		}

		if ( key.startsWith( 'gf_hp_' ) || key.startsWith( 'gf_time_check_' ) ) {
			return;
		}

		fields[ key ] = value;
		fields[ `{${ key }}` ] = value;
		fields[ `{field:${ key }}` ] = value;
	} );

	return fields;
};

const getSuccessIconImg = ( sourceHtml = '' ) => {
	const existingIcon = String( sourceHtml ).match(
		/<img[^>]*class="[^"]*form-message-icon[^"]*"[^>]*>/i
	);

	if ( existingIcon ) {
		return existingIcon[ 0 ];
	}

	const iconSrc = String( sourceHtml ).match(
		/src="([^"]*success-tick[^"]*)"/i
	);

	if ( ! iconSrc ) {
		return '';
	}

	return `<img src="${ iconSrc[ 1 ] }" alt="Success" class="form-message-icon" width="16" height="16" />`;
};

const getStaticMergeReplacements = ( form ) => {
	const block = typeof gutenaFormsBlock !== 'undefined' ? gutenaFormsBlock : {};
	const formName = form?.getAttribute( 'data-form-name' ) || '';
	const now = new Date();

	return {
		'{site_name}': block.site_name || '',
		'{site_title}': block.site_name || '',
		'{site_url}': block.site_url || window.location.origin,
		'{submission_date}': now.toLocaleString(),
		'{form-title}': formName,
		'{form_title}': formName,
		'{admin_email}': block.admin_email || '',
	};
};

export const replaceConfirmationMergeTags = ( html, form, formData ) => {
	if ( isEmpty( html ) ) {
		return '';
	}

	let resolvedHtml = String( html );

	if ( resolvedHtml.includes( '{icon}' ) ) {
		const iconMarkup = getSuccessIconImg( resolvedHtml );
		resolvedHtml = resolvedHtml
			.replace( /<p>\s*\{icon\}\s*<\/p>/gi, iconMarkup )
			.replace( /\{icon\}/g, iconMarkup );
	}

	const replacements = {
		...getStaticMergeReplacements( form ),
		...buildFieldValueMap( formData ),
	};

	return resolvedHtml.replace( /\{[^}]+\}/g, ( tag ) => {
		if ( Object.prototype.hasOwnProperty.call( replacements, tag ) ) {
			const value = replacements[ tag ];
			return null === value || 'undefined' === typeof value
				? ''
				: escapeHtml( value );
		}

		return '';
	} );
};

export const sanitizeRedirectUrl = ( url ) => {
	const trimmed = String( url || '' ).trim();

	if ( ! trimmed ) {
		return '';
	}

	try {
		const parsed = new URL( trimmed, window.location.origin );
		if ( ! [ 'http:', 'https:' ].includes( parsed.protocol ) ) {
			return '';
		}

		if ( 'javascript:' === parsed.protocol || 'data:' === parsed.protocol ) {
			return '';
		}

		return parsed.href;
	} catch ( error ) {
		return '';
	}
};

export const resolveSafeRedirectUrl = ( config ) => {
	if ( ! config || 'redirect' !== config.confirmationType ) {
		return '';
	}

	let candidate = '';

	if ( 'custom_url' === config.redirectType ) {
		candidate = config.redirectUrl || '';
	} else {
		candidate = config.resolvedRedirectUrl || '';
	}

	return sanitizeRedirectUrl( candidate );
};

const getConfirmMessageElement = ( form ) =>
	form.querySelector( '.wp-block-gutena-form-confirm-msg' );

const getErrorMessageElement = ( form ) =>
	form.querySelector( '.wp-block-gutena-form-error-msg' );

const showSuccessMessage = ( form, formData, config ) => {
	const messageHtml = replaceConfirmationMergeTags(
		config.successMessage,
		form,
		formData
	);
	const confirmElement = getConfirmMessageElement( form );

	form.classList.add( 'display-success-message' );

	if ( confirmElement && messageHtml ) {
		confirmElement.innerHTML = messageHtml;
	}
};

export const handleConfirmationError = ( form, formData, config ) => {
	form.classList.remove( 'display-success-message' );
	form.classList.remove( 'hide-form-now' );
	form.classList.add( 'display-error-message' );

	const errorElement = getErrorMessageElement( form );
	const errorHtml = replaceConfirmationMergeTags(
		config.errorMessage,
		form,
		formData
	);

	if ( errorElement && errorHtml ) {
		errorElement.innerHTML = errorHtml;
	}
};

export const handleConfirmationSuccess = ( form, formData, config ) => {
	form.classList.remove( 'display-error-message' );

	if ( 'redirect' === config.confirmationType ) {
		const redirectUrl = resolveSafeRedirectUrl( config );

		if ( redirectUrl ) {
			form.reset();
			window.setTimeout( () => {
				window.location.assign( redirectUrl );
			}, 500 );
			return;
		}

		if ( typeof console !== 'undefined' && console.warn ) {
			console.warn(
				'Gutena Forms: redirect URL could not be resolved.',
				config
			);
		}

		handleConfirmationError( form, formData, config );
		return;
	}

	showSuccessMessage( form, formData, config );

	if ( 'reset' === config.afterSubmit ) {
		form.reset();
		return;
	}

	form.reset();
	form.classList.add( 'hide-form-now' );
};

export const handleLegacyConfirmationSuccess = ( form ) => {
	form.reset();
	form.classList.add( 'display-success-message' );

	if (
		hasClass( form, 'hide-form-after-submit' ) ||
		form.querySelector( '[data-gutena-hide-form-after-submit]' )
	) {
		form.classList.add( 'hide-form-now' );
	}

	if ( hasClass( form, 'after_submit_redirect_url' ) ) {
		const redirectInput = form.querySelector( 'input[name="redirect_url"]' );

		if ( ! isEmpty( redirectInput ) && ! isEmpty( redirectInput.value ) ) {
			const redirectUrl = sanitizeRedirectUrl( redirectInput.value );

			if ( redirectUrl ) {
				window.setTimeout( () => {
					window.location.assign( redirectUrl );
				}, 2000 );
			}
		}
	}
};

export const handleLegacyConfirmationError = ( form, response ) => {
	form.classList.add( 'display-error-message' );

	const errorElement = getErrorMessageElement( form );

	if ( isEmpty( errorElement ) ) {
		return;
	}

	if (
		! isEmpty( response ) &&
		! isEmpty( response.message ) &&
		'error' === response.status
	) {
		const message = String( response.message );
		if ( message.includes( '<' ) ) {
			errorElement.innerHTML = message;
			return;
		}

		const errorText = errorElement.querySelector( '.gutena-forms-error-text' );
		if ( errorText ) {
			errorText.textContent = message;
			return;
		}

		errorElement.innerHTML = `<p class="gutena-forms-error-text">${ escapeHtml( message ) }</p>`;
	}
};
