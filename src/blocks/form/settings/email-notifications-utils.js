import { __ } from '@wordpress/i18n';
import { getRecipientMergeTags } from './email-notifications-merge-tags';

export const DEFAULT_ADMIN_NOTIFICATION_NAME = __(
	'Admin Notification Email',
	'gutena-forms'
);

export const DEFAULT_ADMIN_NOTIFICATION_SUBJECT = __(
	'New Form Submission - {form_title}',
	'gutena-forms'
);

export const FROM_EMAIL_WARNING = __(
	"Please enter a valid email address. Your notifications won't be sent if the field is not filled in correctly.",
	'gutena-forms'
);

const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export const createNotificationId = () => {
	const random = Math.random().toString( 16 ).slice( 2, 10 );
	return `gutena_notification_${ random }`;
};

export const buildNotificationFromDefaults = ( defaults = {} ) => ( {
	send_email_to: defaults.send_email_to || '',
	subject: defaults.subject || DEFAULT_ADMIN_NOTIFICATION_SUBJECT,
	message: defaults.message || '',
	from_name: defaults.from_name || '',
	from_email: defaults.from_email || '',
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

export const getNotificationDefaults = ( settings, legacyAttrs = {} ) => {
	const stored = settings?.emailNotifications || {};
	const globalDefaults =
		typeof gutenaFormsBlock !== 'undefined' &&
		gutenaFormsBlock?.email_notifications_defaults
			? gutenaFormsBlock.email_notifications_defaults
			: {};

	return buildNotificationFromDefaults( {
		send_email_to:
			legacyAttrs.adminEmails ||
			globalDefaults.send_email_to ||
			stored.send_email_to ||
			'',
		subject:
			legacyAttrs.adminEmailSubject ||
			globalDefaults.subject ||
			stored.subject ||
			DEFAULT_ADMIN_NOTIFICATION_SUBJECT,
		message:
			legacyAttrs.adminEmailTemplate ||
			globalDefaults.message ||
			stored.message ||
			'',
		from_name:
			legacyAttrs.emailFromName ||
			globalDefaults.from_name ||
			stored.from_name ||
			'',
		from_email: globalDefaults.from_email || stored.from_email || '',
		cc: globalDefaults.cc || stored.cc || '',
		bcc: globalDefaults.bcc || stored.bcc || '',
		reply_to: globalDefaults.reply_to || stored.reply_to || '',
		reply_to_name: legacyAttrs.replyToName || '',
		reply_to_last_name: legacyAttrs.replyToLastName || '',
	} );
};

export const isValidFromEmailValue = ( value, formFields = [] ) => {
	const trimmed = String( value || '' ).trim();
	if ( '' === trimmed ) {
		return false;
	}
	if ( EMAIL_REGEX.test( trimmed ) ) {
		return true;
	}
	return getRecipientMergeTags( formFields ).includes( trimmed );
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

	if ( emailNotifications?.hasSavedConfig ) {
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

const buildLegacyAdminNotification = ( legacyAttrs, defaults ) => ( {
	id: 'legacy-admin-notification',
	enabled: false !== legacyAttrs?.emailNotifyAdmin,
	name: DEFAULT_ADMIN_NOTIFICATION_NAME,
	...buildNotificationFromDefaults( {
		...defaults,
		send_email_to: legacyAttrs?.adminEmails || defaults.send_email_to,
		subject: legacyAttrs?.adminEmailSubject || defaults.subject,
		message: legacyAttrs?.adminEmailTemplate || defaults.message,
		from_name: legacyAttrs?.emailFromName || defaults.from_name,
		reply_to_name: legacyAttrs?.replyToName || defaults.reply_to_name,
		reply_to_last_name:
			legacyAttrs?.replyToLastName || defaults.reply_to_last_name,
	} ),
} );

export const resolveEmailNotificationsState = ( settings, legacyAttrs ) => {
	const stored = settings?.emailNotifications || {};
	const defaults = getNotificationDefaults( settings, legacyAttrs );
	const meta = {
		from_email: stored.from_email || defaults.from_email,
		cc: stored.cc || defaults.cc,
		bcc: stored.bcc || defaults.bcc,
		reply_to: stored.reply_to || defaults.reply_to,
	};

	if ( stored.hasSavedConfig ) {
		return {
			enabled: !! stored.enabled,
			hasSavedConfig: true,
			notifications: cloneNotifications( stored.notifications ),
			defaults,
			...meta,
		};
	}

	if ( isLegacyEmailNotificationForm( { settings, formID: legacyAttrs?.formID } ) ) {
		return {
			enabled: false !== legacyAttrs?.emailNotifyAdmin,
			hasSavedConfig: true,
			notifications: [ buildLegacyAdminNotification( legacyAttrs, defaults ) ],
			defaults,
			...meta,
		};
	}

	return {
		enabled: !! stored.enabled,
		hasSavedConfig: false,
		notifications: cloneNotifications( stored.notifications ),
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

	setAttributes( {
		settings: {
			...settings,
			emailNotifications: nextEmailNotifications,
		},
		emailNotifyAdmin:
			'enabled' in partial
				? !! partial.enabled
				: !! nextEmailNotifications.enabled,
	} );
};
