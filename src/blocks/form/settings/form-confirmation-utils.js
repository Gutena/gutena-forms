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

export const getConfirmationDefaults = ( settings = {} ) => {
	const stored = settings?.formConfirmation || {};
	const globalDefaults =
		typeof gutenaFormsBlock !== 'undefined' &&
		gutenaFormsBlock?.form_confirmation_defaults
			? gutenaFormsBlock.form_confirmation_defaults
			: {};

	return {
		...buildFormConfirmationFromDefaults( globalDefaults ),
		...extractConfirmationConfig( stored ),
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
	const defaults = getConfirmationDefaults( settings );

	if ( stored.hasSavedConfig ) {
		return {
			enabled: !! stored.enabled,
			hasSavedConfig: true,
			confirmation: extractConfirmationConfig( stored ),
			defaults,
		};
	}

	if ( hasExistingFormConfirmationSettings( settings ) ) {
		return {
			enabled: !! stored.enabled,
			hasSavedConfig: false,
			confirmation: extractConfirmationConfig( stored ),
			defaults,
		};
	}

	if ( isLegacyFormConfirmation( { formID: legacyAttrs.formID, settings } ) ) {
		return {
			enabled: false,
			hasSavedConfig: false,
			confirmation: mapLegacyAttrsToConfirmation( legacyAttrs, defaults ),
			defaults,
		};
	}

	return {
		enabled: !! stored.enabled,
		hasSavedConfig: false,
		confirmation: extractConfirmationConfig( stored ),
		defaults,
	};
};

export const createSeedConfirmation = ( defaults = {}, existing = {} ) =>
	cloneConfirmation( {
		...defaults,
		...existing,
	} );

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
