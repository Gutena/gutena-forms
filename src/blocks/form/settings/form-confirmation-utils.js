/**
 * Form confirmation settings helpers for block editor inheritance and modal state.
 */

import { __ } from '@wordpress/i18n';
import { sanitizeNotificationHtml } from './email-notifications-utils';

export const buildFormConfirmationFromDefaults = ( defaults = {} ) => ( {
	confirmationType: defaults.confirmation_type || 'message',
	successMessage: defaults.success_message || '',
	errorMessage: defaults.error_message || '',
	afterSubmit: defaults.after_submit || 'hide',
	redirectType: defaults.redirect_type || 'page',
	redirectPageId: parseInt( defaults.redirect_page_id, 10 ) || 0,
	redirectUrl: defaults.redirect_url || '',
	resolvedRedirectUrl: defaults.resolved_redirect_url || '',
	defaultSettings: true,
} );

export const getNeutralLegacyConfirmation = () => ( {
	confirmationType: 'message',
	afterSubmit: 'reset',
	redirectType: 'page',
	redirectPageId: 0,
	redirectUrl: '',
} );

export const mapFormConfirmationToLegacyAttrs = ( confirmation = {} ) => {
	const legacyAttrs = {
		afterSubmitAction:
			'redirect' === confirmation.confirmationType
				? 'redirect_url'
				: 'message',
		afterSubmitHide: 'hide' === confirmation.afterSubmit,
		redirectUrl: '',
	};

	if ( 'redirect' !== confirmation.confirmationType ) {
		return legacyAttrs;
	}

	if ( 'custom_url' === confirmation.redirectType ) {
		legacyAttrs.redirectUrl = confirmation.redirectUrl || '';
		return legacyAttrs;
	}

	legacyAttrs.redirectUrl = confirmation.resolvedRedirectUrl || '';
	return legacyAttrs;
};

export const hasExistingFormConfirmationSettings = ( settings = {} ) => {
	const stored = settings?.formConfirmation;

	if ( ! stored || 'object' !== typeof stored ) {
		return false;
	}

	if ( stored.hasSavedConfig ) {
		return true;
	}

	if ( false === stored.defaultSettings ) {
		return true;
	}

	return ! (
		undefined === stored.confirmationType &&
		undefined === stored.successMessage &&
		undefined === stored.errorMessage
	);
};

export const cloneConfirmation = ( confirmation = {} ) => ( {
	...confirmation,
} );

export const extractConfirmationConfig = ( stored = {} ) => ( {
	confirmationType: stored.confirmationType || 'message',
	successMessage: stored.successMessage || '',
	errorMessage: stored.errorMessage || '',
	afterSubmit: stored.afterSubmit || 'hide',
	redirectType: stored.redirectType || 'page',
	redirectPageId: parseInt( stored.redirectPageId, 10 ) || 0,
	redirectUrl: stored.redirectUrl || '',
	resolvedRedirectUrl: stored.resolvedRedirectUrl || '',
} );

export const getGlobalConfirmationDefaults = () =>
	typeof gutenaFormsBlock !== 'undefined' &&
	gutenaFormsBlock?.form_confirmation_defaults
		? gutenaFormsBlock.form_confirmation_defaults
		: {};

const getSuccessMessageIconUrl = () => {
	const globalBuilt = buildFormConfirmationFromDefaults(
		getGlobalConfirmationDefaults()
	);
	const iconMatch = String( globalBuilt.successMessage || '' ).match(
		/src="([^"]+)"/i
	);

	return iconMatch ? iconMatch[ 1 ] : '';
};

const getErrorMessageIconUrl = () => {
	const globalBuilt = buildFormConfirmationFromDefaults(
		getGlobalConfirmationDefaults()
	);
	const iconMatch = String( globalBuilt.errorMessage || '' ).match(
		/src="([^"]+)"/i
	);

	return iconMatch ? iconMatch[ 1 ] : '';
};

const getSuccessMessageIconImg = () => {
	const iconUrl = getSuccessMessageIconUrl();

	if ( ! iconUrl ) {
		return '';
	}

	return `<img src="${ iconUrl }" alt="Success" class="form-message-icon" width="16" height="16" />`;
};

const replaceSuccessIconMergeTag = ( html ) => {
	const value = String( html || '' );

	if ( ! value.includes( '{icon}' ) ) {
		return value;
	}

	const icon = getSuccessMessageIconImg();

	return value
		.replace( /<p>\s*\{icon\}\s*<\/p>/gi, icon )
		.replace( /\{icon\}/g, icon );
};

const successMessageUsesStackedLayout = ( html ) => {
	const value = String( html || '' );

	if ( value.includes( '{icon}' ) ) {
		return true;
	}

	if ( /<p>\s*<img[^>]*form-message-icon[^>]*>\s*<\/p>/i.test( value ) ) {
		return true;
	}

	return ( value.match( /<p\b/gi ) || [] ).length > 1;
};

const ensureSuccessBannerStackedClass = ( html ) => {
	const value = String( html || '' );

	if (
		value.includes( 'gutena-forms-success-banner--stacked' ) ||
		value.includes( 'gutena-forms-success-banner--rich' )
	) {
		return value;
	}

	return value.replace(
		'gutena-forms-success-banner"',
		'gutena-forms-success-banner gutena-forms-success-banner--stacked"'
	);
};

export const normalizeSuccessMessageHtml = ( html, { allowFallback = true } = {} ) => {
	const trimmed = String( html || '' ).trim();
	const hadIconToken = trimmed.includes( '{icon}' );

	if ( ! trimmed ) {
		if ( ! allowFallback ) {
			return '';
		}

		const built = buildFormConfirmationFromDefaults(
			getGlobalConfirmationDefaults()
		);

		return normalizeSuccessMessageHtml( built.successMessage, {
			allowFallback: false,
		} );
	}

	if ( trimmed.includes( 'gutena-forms-success-banner' ) ) {
		return ensureSuccessBannerStackedClass(
			replaceSuccessIconMergeTag( trimmed )
		);
	}

	let inner = replaceSuccessIconMergeTag( trimmed );

	if (
		/<div[^>]*class="[^"]*gutena-forms-confirmation-message[^"]*"[^>]*>/i.test(
			inner
		)
	) {
		inner = inner
			.replace(
				/^[\s\S]*<div[^>]*class="[^"]*gutena-forms-confirmation-message[^"]*"[^>]*>/i,
				''
			)
			.replace( /<\/div>\s*$/i, '' );
	}

	inner = inner
		.replace( /^\s*<img[^>]*class="[^"]*form-message-icon[^"]*"[^>]*>\s*/i, '' )
		.trim();

	if ( ! inner ) {
		return normalizeSuccessMessageHtml( '', { allowFallback } );
	}

	const hasHeading = /<h[1-6][^>]*>/i.test( inner );
	const usesStacked =
		hadIconToken || successMessageUsesStackedLayout( inner );
	let bannerClass = 'gutena-forms-success-banner';
	let content = inner;

	if ( hasHeading ) {
		bannerClass += ' gutena-forms-success-banner--rich';
		content = `<div class="gutena-forms-success-banner__content">${ inner }</div>`;
	} else {
		if ( usesStacked ) {
			bannerClass += ' gutena-forms-success-banner--stacked';
		}

		content = inner.replace(
			/<p(?![^>]*class=)/gi,
			'<p class="gutena-forms-success-text"'
		);
	}

	const hasIconInContent = content.includes( 'form-message-icon' );
	const iconMarkup = getSuccessMessageIconImg();
	return hasIconInContent
		? `<div class="${ bannerClass }">${ content }</div>`
		: `<div class="${ bannerClass }">${ iconMarkup }${ content }</div>`;
};

export const normalizeErrorMessageHtml = ( html, { allowFallback = true } = {} ) => {
	const trimmed = String( html || '' ).trim();

	if ( ! trimmed ) {
		if ( ! allowFallback ) {
			return '';
		}

		const built = buildFormConfirmationFromDefaults(
			getGlobalConfirmationDefaults()
		);

		return normalizeErrorMessageHtml( built.errorMessage, {
			allowFallback: false,
		} );
	}

	if ( trimmed.includes( 'gutena-forms-error-message' ) ) {
		return trimmed;
	}

	let inner = trimmed
		.replace( /^\s*<img[^>]*class="[^"]*form-message-icon[^"]*"[^>]*>\s*/i, '' )
		.trim();

	if ( ! inner ) {
		return normalizeErrorMessageHtml( '', { allowFallback } );
	}

	if ( ! /<h[1-6][^>]*>/i.test( inner ) ) {
		inner = `<h3>${ __( 'Something went wrong', 'gutena-forms' ) }</h3><p class="gutena-forms-error-text">${ inner }</p>`;
	} else if ( ! /class="[^"]*gutena-forms-error-text[^"]*"/i.test( inner ) ) {
		inner = inner.replace( /<p(?![^>]*class=)/gi, '<p class="gutena-forms-error-text"' );
	}

	const iconUrl = getErrorMessageIconUrl();

	return `<div class="gutena-forms-error-message"><img src="${ iconUrl }" alt="Error" class="form-message-icon" width="16" height="16" />${ inner }</div>`;
};

export const getGlobalConfirmationDefaultsForEditor = () => {
	const built = buildFormConfirmationFromDefaults( getGlobalConfirmationDefaults() );

	return {
		...built,
		successMessage: normalizeSuccessMessageHtml( built.successMessage, {
			allowFallback: false,
		} ),
		errorMessage: normalizeErrorMessageHtml( built.errorMessage, {
			allowFallback: false,
		} ),
	};
};

const hasMessageContent = ( message ) =>
	String( message || '' ).trim().length > 0;

export const usesGlobalConfirmationDefaults = ( stored = {} ) => {
	if ( stored?.hasSavedConfig && false === stored?.defaultSettings ) {
		return false;
	}

	if ( stored?.hasSavedConfig && undefined === stored?.defaultSettings ) {
		return false;
	}

	return false !== stored?.defaultSettings;
};

const normalizeConfirmationValue = ( value ) => String( value ?? '' ).trim();

export const confirmationMatchesGlobalDefaults = (
	confirmation = {},
	globalDefaults = {}
) => {
	const built = buildFormConfirmationFromDefaults( globalDefaults );

	return (
		normalizeConfirmationValue( confirmation.confirmationType ) ===
			normalizeConfirmationValue( built.confirmationType ) &&
		normalizeConfirmationValue( confirmation.afterSubmit ) ===
			normalizeConfirmationValue( built.afterSubmit ) &&
		normalizeConfirmationValue( confirmation.redirectType ) ===
			normalizeConfirmationValue( built.redirectType ) &&
		( parseInt( confirmation.redirectPageId, 10 ) || 0 ) ===
			( parseInt( built.redirectPageId, 10 ) || 0 ) &&
		normalizeConfirmationValue( confirmation.redirectUrl ) ===
			normalizeConfirmationValue( built.redirectUrl ) &&
		normalizeConfirmationValue(
			normalizeSuccessMessageHtml( confirmation.successMessage )
		) ===
			normalizeConfirmationValue(
				normalizeSuccessMessageHtml( built.successMessage )
			) &&
		normalizeConfirmationValue(
			normalizeErrorMessageHtml( confirmation.errorMessage )
		) ===
			normalizeConfirmationValue(
				normalizeErrorMessageHtml( built.errorMessage )
			)
	);
};

export const resolveConfirmationMessages = ( stored = {}, globalDefaults = {} ) => {
	const built = getGlobalConfirmationDefaultsForEditor();
	const extracted = extractConfirmationConfig( stored );
	let successMessage = built.successMessage;
	let errorMessage = built.errorMessage;

	if ( ! usesGlobalConfirmationDefaults( stored ) ) {
		successMessage = hasMessageContent( extracted.successMessage )
			? extracted.successMessage
			: built.successMessage;
		errorMessage = hasMessageContent( extracted.errorMessage )
			? extracted.errorMessage
			: built.errorMessage;
	}

	return {
		...extracted,
		successMessage: normalizeSuccessMessageHtml( successMessage ),
		errorMessage: normalizeErrorMessageHtml( errorMessage ),
	};
};

export const getConfirmationDefaults = ( settings = {} ) => {
	const stored = settings?.formConfirmation || {};
	const globalDefaults = getGlobalConfirmationDefaults();
	const built = buildFormConfirmationFromDefaults( globalDefaults );
	const resolved = resolveConfirmationMessages( stored, globalDefaults );

	return {
		...built,
		...resolved,
		defaultSettings:
			stored.defaultSettings !== undefined
				? stored.defaultSettings
				: built.defaultSettings,
	};
};

export const mapLegacyAttrsToConfirmation = ( legacyAttrs = {}, defaults = {} ) => {
	const confirmationType =
		'redirect_url' === legacyAttrs.afterSubmitAction ? 'redirect' : 'message';

	return {
		confirmationType,
		successMessage: defaults.successMessage || '',
		errorMessage: defaults.errorMessage || '',
		afterSubmit: legacyAttrs.afterSubmitHide ? 'hide' : 'reset',
		redirectType: legacyAttrs.redirectUrl ? 'custom_url' : 'page',
		redirectPageId: 0,
		redirectUrl: legacyAttrs.redirectUrl || '',
		resolvedRedirectUrl: legacyAttrs.redirectUrl || '',
	};
};

export const isFormConfirmationExplicitlyDisabled = ( stored = {} ) =>
	stored.hasSavedConfig === true && stored.enabled === false;

export const resolveFormConfirmationEnabled = ( stored = {} ) =>
	! isFormConfirmationExplicitlyDisabled( stored );

export const isLegacyFormConfirmation = ( { formID, settings } ) => {
	const stored = settings?.formConfirmation;

	if ( stored?.hasSavedConfig ) {
		return false;
	}

	if ( ! formID ) {
		return false;
	}

	if ( true === stored?.defaultSettings ) {
		return false;
	}

	return ! hasExistingFormConfirmationSettings( settings );
};

export const resolveFormConfirmationState = ( settings, legacyAttrs = {} ) => {
	const stored = settings?.formConfirmation || {};
	const globalDefaults = getGlobalConfirmationDefaults();
	const defaults = getConfirmationDefaults( settings );
	const enabled = resolveFormConfirmationEnabled( stored );
	const resolvedMessages = resolveConfirmationMessages(
		stored,
		globalDefaults
	);

	if ( stored.hasSavedConfig ) {
		return {
			enabled,
			hasSavedConfig: true,
			confirmation: resolvedMessages,
			defaults,
		};
	}

	if ( hasExistingFormConfirmationSettings( settings ) ) {
		return {
			enabled,
			hasSavedConfig: false,
			confirmation: resolvedMessages,
			defaults,
		};
	}

	if ( isLegacyFormConfirmation( { formID: legacyAttrs.formID, settings } ) ) {
		return {
			enabled,
			hasSavedConfig: false,
			confirmation: {
				...mapLegacyAttrsToConfirmation( legacyAttrs, defaults ),
				...resolvedMessages,
			},
			defaults,
		};
	}

	return {
		enabled,
		hasSavedConfig: false,
		confirmation: resolvedMessages,
		defaults,
	};
};

export const createSeedConfirmation = ( defaults = {}, existing = {} ) => {
	const resolved = resolveConfirmationMessages( existing, getGlobalConfirmationDefaults() );

	return cloneConfirmation( {
		...defaults,
		...existing,
		successMessage: resolved.successMessage,
		errorMessage: resolved.errorMessage,
	} );
};

export const sanitizeRedirectUrl = ( url ) => {
	const trimmed = String( url || '' ).trim();

	if ( ! trimmed ) {
		return '';
	}

	try {
		const parsed = new URL( trimmed );
		if ( ! [ 'http:', 'https:' ].includes( parsed.protocol ) ) {
			return '';
		}
		return trimmed;
	} catch ( error ) {
		return '';
	}
};

export const isValidRedirectUrl = ( url ) => {
	const trimmed = String( url || '' ).trim();
	if ( ! trimmed ) {
		return true;
	}

	return '' !== sanitizeRedirectUrl( trimmed );
};

export const validateConfirmationForSave = ( confirmation = {} ) => {
	if ( 'redirect' !== confirmation.confirmationType ) {
		return { valid: true };
	}

	if ( 'custom_url' === confirmation.redirectType ) {
		const trimmed = String( confirmation.redirectUrl || '' ).trim();
		if ( ! trimmed ) {
			return {
				valid: false,
				message: __(
					'Please enter a redirect URL.',
					'gutena-forms'
				),
			};
		}

		if ( ! isValidRedirectUrl( trimmed ) ) {
			return {
				valid: false,
				message: __(
					'Please enter a valid redirect URL using http:// or https://.',
					'gutena-forms'
				),
			};
		}
	} else if ( ( parseInt( confirmation.redirectPageId, 10 ) || 0 ) <= 0 ) {
		return {
			valid: false,
			message: __( 'Please select a page to redirect to.', 'gutena-forms' ),
		};
	}

	return { valid: true };
};

export const sanitizeConfirmation = ( confirmation = {}, pageRecords = [] ) => {
	const sanitized = cloneConfirmation( confirmation );

	sanitized.confirmationType =
		'redirect' === sanitized.confirmationType ? 'redirect' : 'message';
	sanitized.afterSubmit =
		'reset' === sanitized.afterSubmit ? 'reset' : 'hide';
	sanitized.redirectType =
		'custom_url' === sanitized.redirectType ? 'custom_url' : 'page';
	sanitized.successMessage = sanitizeNotificationHtml(
		sanitized.successMessage || ''
	);
	sanitized.errorMessage = sanitizeNotificationHtml(
		sanitized.errorMessage || ''
	);
	sanitized.redirectPageId = parseInt( sanitized.redirectPageId, 10 ) || 0;

	const rawRedirectUrl = String( sanitized.redirectUrl || '' ).trim();
	sanitized.redirectUrl = rawRedirectUrl
		? sanitizeRedirectUrl( rawRedirectUrl )
		: '';

	if ( 'page' === sanitized.redirectType && sanitized.redirectPageId > 0 ) {
		const page = pageRecords.find(
			( record ) => record.id === sanitized.redirectPageId
		);
		sanitized.resolvedRedirectUrl = page?.link
			? sanitizeRedirectUrl( page.link )
			: '';
	} else if (
		'custom_url' === sanitized.redirectType &&
		sanitized.redirectUrl
	) {
		sanitized.resolvedRedirectUrl = '';
	}

	return sanitized;
};

export const persistFormConfirmation = (
	setAttributes,
	settings,
	partial,
	{ syncLegacy = false, resetLegacy = false } = {}
) => {
	const current = settings?.formConfirmation || {};
	const next = {
		...current,
		...partial,
	};

	if ( usesGlobalConfirmationDefaults( next ) ) {
		delete next.successMessage;
		delete next.errorMessage;
	}

	const attrs = {
		settings: {
			...settings,
			formConfirmation: next,
		},
	};

	if ( resetLegacy ) {
		Object.assign(
			attrs,
			mapFormConfirmationToLegacyAttrs( getNeutralLegacyConfirmation() )
		);
	} else if ( syncLegacy ) {
		Object.assign(
			attrs,
			mapFormConfirmationToLegacyAttrs( extractConfirmationConfig( next ) )
		);
	}

	setAttributes( attrs );
};
