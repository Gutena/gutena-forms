import { __ } from '@wordpress/i18n';
import { getAllowedMergeTagsForField } from './email-notifications-merge-tags';

export const DEFAULT_ADMIN_NOTIFICATION_NAME = __(
	'Admin Notification Email',
	'gutena-forms'
);

export const DEFAULT_ADMIN_NOTIFICATION_SUBJECT = __(
	'New Form Submission - {form_title}',
	'gutena-forms'
);

export const DEFAULT_SEND_EMAIL_TO = '{admin_email}';
export const DEFAULT_FROM_NAME = '{site_title}';
export const DEFAULT_FROM_EMAIL = '{admin_email}';
export const DEFAULT_MESSAGE = '{all_data}';

export const FROM_EMAIL_WARNING = __(
	"Please enter a valid email address. Your notifications won't be sent if the field is not filled in correctly.",
	'gutena-forms'
);

const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export const createNotificationId = () => {
	const random = Math.random().toString( 16 ).slice( 2, 10 );
	return `gutena_notification_${ random }`;
};

const resolveTagDefault = ( value, fallback ) => {
	const trimmed = String( value ?? '' ).trim();
	return trimmed || fallback;
};

export const buildNotificationFromDefaults = ( defaults = {} ) => ( {
	send_email_to: resolveTagDefault(
		defaults.send_email_to,
		DEFAULT_SEND_EMAIL_TO
	),
	subject: defaults.subject || DEFAULT_ADMIN_NOTIFICATION_SUBJECT,
	message: resolveTagDefault( defaults.message, DEFAULT_MESSAGE ),
	from_name: resolveTagDefault( defaults.from_name, DEFAULT_FROM_NAME ),
	from_email: resolveTagDefault( defaults.from_email, DEFAULT_FROM_EMAIL ),
	cc: defaults.cc || '',
	bcc: defaults.bcc || '',
	reply_to: defaults.reply_to || '',
	reply_to_name: defaults.reply_to_name || '',
	reply_to_last_name: defaults.reply_to_last_name || '',
} );

export const createSeedNotification = ( defaults = {} ) => ( {
	id: createNotificationId(),
	enabled: true,
	name: DEFAULT_ADMIN_NOTIFICATION_NAME,
	...buildNotificationFromDefaults( defaults ),
} );

export const createEmptyNotification = ( defaults = {} ) => ( {
	id: createNotificationId(),
	enabled: true,
	name: __( 'Notification', 'gutena-forms' ),
	...buildNotificationFromDefaults( defaults ),
} );

export const cloneNotifications = ( notifications ) => {
	if ( ! Array.isArray( notifications ) ) {
		return [];
	}

	return notifications.map( ( notification ) => ( { ...notification } ) );
};

export const getGlobalNotificationDefaults = () => {
	if (
		typeof gutenaFormsBlock !== 'undefined' &&
		gutenaFormsBlock?.email_notifications_defaults
	) {
		return gutenaFormsBlock.email_notifications_defaults;
	}

	return {};
};

export const usesGlobalEmailDefaults = ( stored = {} ) => {
	if ( stored?.hasSavedConfig && false === stored?.defaultSettings ) {
		return false;
	}

	if ( stored?.hasSavedConfig && undefined === stored?.defaultSettings ) {
		return false;
	}

	return false !== stored?.defaultSettings;
};

export const hasCustomEmailNotifications = ( stored = {} ) => {
	return ! usesGlobalEmailDefaults( stored );
};

export const getNotificationDefaults = (
	settings,
	legacyAttrs = {},
	isCustomized = null
) => {
	const stored = settings?.emailNotifications || {};
	const customized =
		null === isCustomized ? hasCustomEmailNotifications( stored ) : isCustomized;
	const globalDefaults = getGlobalNotificationDefaults();
	const hasExplicitCustomConfig =
		stored.hasSavedConfig && false === stored.defaultSettings;

	if ( ! customized ) {
		return buildNotificationFromDefaults( {
			send_email_to: globalDefaults.send_email_to,
			subject: globalDefaults.subject,
			message: globalDefaults.message,
			from_name: globalDefaults.from_name,
			from_email: stored.from_email || globalDefaults.from_email,
			cc: stored.cc || globalDefaults.cc,
			bcc: stored.bcc || globalDefaults.bcc,
			reply_to: stored.reply_to || globalDefaults.reply_to,
			reply_to_name: legacyAttrs.replyToName || '',
			reply_to_last_name: legacyAttrs.replyToLastName || '',
		} );
	}

	return buildNotificationFromDefaults( {
		send_email_to: hasExplicitCustomConfig
			? legacyAttrs.adminEmails ||
				stored.send_email_to ||
				globalDefaults.send_email_to
			: globalDefaults.send_email_to || stored.send_email_to,
		subject: hasExplicitCustomConfig
			? legacyAttrs.adminEmailSubject ||
				stored.subject ||
				globalDefaults.subject
			: globalDefaults.subject || stored.subject,
		message: hasExplicitCustomConfig
			? legacyAttrs.adminEmailTemplate ||
				stored.message ||
				globalDefaults.message
			: globalDefaults.message || stored.message,
		from_name: hasExplicitCustomConfig
			? legacyAttrs.emailFromName ||
				stored.from_name ||
				globalDefaults.from_name
			: globalDefaults.from_name || stored.from_name,
		from_email: stored.from_email || globalDefaults.from_email,
		cc: stored.cc || globalDefaults.cc,
		bcc: stored.bcc || globalDefaults.bcc,
		reply_to: stored.reply_to || globalDefaults.reply_to,
		reply_to_name: legacyAttrs.replyToName || '',
		reply_to_last_name: legacyAttrs.replyToLastName || '',
	} );
};

export const mergeNotificationWithDefaults = (
	notification = {},
	defaults = {}
) => {
	const merged = {
		...notification,
	};

	Object.keys( buildNotificationFromDefaults() ).forEach( ( key ) => {
		const value = merged[ key ];
		if ( null === value || undefined === value || '' === String( value ).trim() ) {
			merged[ key ] = defaults[ key ] || '';
		}
	} );

	if ( ! merged.name ) {
		merged.name = defaults.name || DEFAULT_ADMIN_NOTIFICATION_NAME;
	}

	return merged;
};

export const buildInheritedNotifications = ( stored = {}, defaults = {} ) => {
	const metaDefaults = buildNotificationFromDefaults( {
		...defaults,
		from_email: stored.from_email || defaults.from_email || '',
		cc: stored.cc || defaults.cc || '',
		bcc: stored.bcc || defaults.bcc || '',
		reply_to: stored.reply_to || defaults.reply_to || '',
	} );

	const storedNotifications = Array.isArray( stored.notifications )
		? stored.notifications
		: [];

	if ( storedNotifications.length > 0 ) {
		return storedNotifications.map( ( notification ) => ( {
			...notification,
			send_email_to: metaDefaults.send_email_to,
			subject: metaDefaults.subject,
			message: metaDefaults.message,
			from_name: metaDefaults.from_name,
			from_email: metaDefaults.from_email,
			cc: metaDefaults.cc,
			bcc: metaDefaults.bcc,
			reply_to: metaDefaults.reply_to,
		} ) );
	}

	return [ createSeedNotification( metaDefaults ) ];
};

export const isValidFromEmailValue = ( value, formFields = [] ) => {
	const trimmed = String( value || '' ).trim();
	if ( '' === trimmed ) {
		return false;
	}
	if ( EMAIL_REGEX.test( trimmed ) ) {
		return true;
	}
	return getAllowedMergeTagsForField( 'from_email', formFields, 'form' ).includes(
		trimmed
	);
};

export const shouldShowFromEmailWarning = ( value, formFields = [] ) => {
	return ! isValidFromEmailValue( value, formFields );
};

export const sanitizeNotificationHtml = ( html ) => {
	if ( ! html ) {
		return '';
	}

	if ( 'undefined' === typeof document ) {
		return html;
	}

	const template = document.createElement( 'template' );
	template.innerHTML = html;
	template.content
		.querySelectorAll( 'script,style,iframe,object,embed,link,meta' )
		.forEach( ( element ) => element.remove() );

	template.content.querySelectorAll( '*' ).forEach( ( element ) => {
		[ ...element.attributes ].forEach( ( attribute ) => {
			const name = attribute.name.toLowerCase();
			if ( name.startsWith( 'on' ) || 'javascript:' === attribute.value?.toLowerCase?.() ) {
				element.removeAttribute( attribute.name );
			}
		} );
	} );

	return template.innerHTML;
};

export const sanitizeNotification = ( notification ) => {
	const sanitized = { ...notification };
	sanitized.name = String( notification.name || '' ).trim();
	sanitized.send_email_to = String( notification.send_email_to || '' ).trim();
	sanitized.subject = String( notification.subject || '' ).trim();
	sanitized.from_name = String( notification.from_name || '' ).trim();
	sanitized.from_email = String( notification.from_email || '' ).trim();
	sanitized.cc = String( notification.cc || '' ).trim();
	sanitized.bcc = String( notification.bcc || '' ).trim();
	sanitized.reply_to = String( notification.reply_to || '' ).trim();
	sanitized.reply_to_name = String( notification.reply_to_name || '' ).trim();
	sanitized.reply_to_last_name = String( notification.reply_to_last_name || '' ).trim();
	sanitized.message = sanitizeNotificationHtml( notification.message || '' );
	return sanitized;
};

export const isLegacyEmailNotificationForm = ( attributes ) => {
	const emailNotifications = attributes?.settings?.emailNotifications;

	if (
		emailNotifications?.hasSavedConfig &&
		false === emailNotifications?.defaultSettings
	) {
		return false;
	}

	if (
		emailNotifications?.hasSavedConfig &&
		undefined === emailNotifications?.defaultSettings
	) {
		return false;
	}

	if ( ! attributes?.formID ) {
		return false;
	}

	if (
		emailNotifications &&
		( 'from_email' in emailNotifications ||
			'cc' in emailNotifications ||
			'bcc' in emailNotifications ||
			'reply_to' in emailNotifications )
	) {
		return false;
	}

	return true;
};

export const resolveEmailNotificationsState = ( settings, legacyAttrs ) => {
	const stored = settings?.emailNotifications || {};
	const customized = hasCustomEmailNotifications( stored );
	const defaults = getNotificationDefaults( settings, legacyAttrs, customized );
	const meta = {
		from_email: stored.from_email || defaults.from_email,
		cc: stored.cc || defaults.cc,
		bcc: stored.bcc || defaults.bcc,
		reply_to: stored.reply_to || defaults.reply_to,
	};

	if ( customized ) {
		return {
			enabled: !! stored.enabled,
			hasSavedConfig: true,
			defaultSettings: false,
			notifications: cloneNotifications( stored.notifications ),
			defaults,
			...meta,
		};
	}

	const enabled =
		'enabled' in stored
			? !! stored.enabled
			: false !== legacyAttrs?.emailNotifyAdmin;

	return {
		enabled,
		hasSavedConfig: !! stored.hasSavedConfig,
		defaultSettings: true,
		notifications: buildInheritedNotifications( stored, defaults ),
		defaults,
		...meta,
	};
};

export const persistEmailNotifications = ( setAttributes, settings, partial ) => {
	const current = settings?.emailNotifications || {};
	const nextNotifications = partial.notifications
		? cloneNotifications( partial.notifications )
		: cloneNotifications( current.notifications );

	const nextEmailNotifications = {
		...current,
		...partial,
		notifications: nextNotifications,
	};

	const attrs = {
		settings: {
			...settings,
			emailNotifications: nextEmailNotifications,
		},
		emailNotifyAdmin:
			'enabled' in partial
				? !! partial.enabled
				: !! nextEmailNotifications.enabled,
	};

	if (
		false === partial.defaultSettings ||
		false === nextEmailNotifications.defaultSettings
	) {
		const primary = nextNotifications[ 0 ] || {};
		attrs.adminEmails = primary.send_email_to || '';
		attrs.adminEmailSubject = primary.subject || '';
		attrs.adminEmailTemplate = primary.message || '';
		attrs.emailFromName = primary.from_name || '';
	}

	setAttributes( attrs );
};
