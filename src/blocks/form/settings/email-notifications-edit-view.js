import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { SelectControl } from '@wordpress/components';
import NotificationFieldControl from './components/notification-field-control';
import GutenaFormsNotificationMessageField from '../../../shared/components/GutenaFormsNotificationMessageField';
import {
	DEFAULT_ADMIN_NOTIFICATION_SUBJECT,
	sanitizeNotification,
	shouldShowFromEmailWarning,
} from './email-notifications-utils';
import {
	getContentMergeTags,
	getFormInputTagItems,
	getRecipientMergeTags,
} from './email-notifications-merge-tags';

const FROM_EMAIL_HELP = __(
	'Notifications can use only one From Email so please enter a single address.',
	'gutena-forms'
);

const FROM_EMAIL_DOMAIN_WARNING = __(
	"The current 'From Email' address may not match your website domain name. This can cause your notification emails to be blocked or marked as spam. Alternately, try using a From Address that matches your website domain.",
	'gutena-forms'
);

const MULTI_VALUE_HELP = __(
	'Comma separated values are also accepted.',
	'gutena-forms'
);

const EmailNotificationsEditView = ( {
	notification,
	notificationDefaults,
	formFields,
	textFieldOptions,
	onSave,
} ) => {
	const [ draft, setDraft ] = useState( null );
	const [ showFromEmailWarning, setShowFromEmailWarning ] = useState( false );

	useEffect( () => {
		if ( ! notification ) {
			return;
		}

		setDraft( { ...notification } );
		setShowFromEmailWarning(
			shouldShowFromEmailWarning( notification.from_email, formFields )
		);
	}, [ notification, formFields ] );

	if ( ! draft ) {
		return null;
	}

	const recipientTags = getRecipientMergeTags( formFields );
	const contentTags = getContentMergeTags( formFields );
	const formTagItems = getFormInputTagItems( formFields );

	const updateDraft = ( key, value ) => {
		setDraft( ( current ) => ( {
			...current,
			[ key ]: value,
		} ) );

		if ( 'from_email' === key ) {
			setShowFromEmailWarning( shouldShowFromEmailWarning( value, formFields ) );
		}
	};

	const handleSubmit = ( event ) => {
		event.preventDefault();

		const trimmedSendTo = String( draft.send_email_to || '' ).trim();
		const trimmedSubject = String( draft.subject || '' ).trim();

		if ( ! trimmedSendTo || ! trimmedSubject ) {
			return;
		}

		onSave(
			sanitizeNotification( {
				...draft,
				name: String( draft.name || '' ).trim(),
				send_email_to: trimmedSendTo,
				subject: trimmedSubject,
			} )
		);
	};

	return (
		<form
			id="gutena-email-notification-edit-form"
			className="gutena-forms-email-notifications-edit"
			onSubmit={ handleSubmit }
		>
			<NotificationFieldControl
				id="gutena-notification-name"
				label={ __( 'Name', 'gutena-forms' ) }
				value={ draft.name }
				onChange={ ( value ) => updateDraft( 'name', value ) }
				placeholder={ __( 'New Notification', 'gutena-forms' ) }
			/>

			<NotificationFieldControl
				id="gutena-notification-send_email_to"
				label={ __( 'Send Email To', 'gutena-forms' ) }
				value={ draft.send_email_to }
				onChange={ ( value ) => updateDraft( 'send_email_to', value ) }
				required
				mergeTags={ recipientTags }
				helpText={ MULTI_VALUE_HELP }
			/>

			<NotificationFieldControl
				id="gutena-notification-subject"
				label={ __( 'Subject', 'gutena-forms' ) }
				value={ draft.subject }
				onChange={ ( value ) => updateDraft( 'subject', value ) }
				placeholder={ DEFAULT_ADMIN_NOTIFICATION_SUBJECT }
				required
				mergeTags={ contentTags }
			/>

				<GutenaFormsNotificationMessageField
				id="gutena-notification-message"
				label={ __( 'Email Message', 'gutena-forms' ) }
				value={ draft.message }
				onChange={ ( value ) => updateDraft( 'message', value ) }
				placeholder={ notificationDefaults?.message || '' }
				formTagItems={ formTagItems }
			/>

			<NotificationFieldControl
				id="gutena-notification-from_name"
				label={ __( 'From Name', 'gutena-forms' ) }
				value={ draft.from_name }
				onChange={ ( value ) => updateDraft( 'from_name', value ) }
				required
				mergeTags={ contentTags }
			/>

			<div className="gutena-forms-notification-field-group">
				<NotificationFieldControl
					id="gutena-notification-from_email"
					label={ __( 'From Email', 'gutena-forms' ) }
					value={ draft.from_email }
					onChange={ ( value ) => updateDraft( 'from_email', value ) }
					required
					mergeTags={ recipientTags }
					helpText={ FROM_EMAIL_HELP }
				/>

				{ showFromEmailWarning && (
					<div className="gutena-forms-notification-field-warning-box">
						<span
							className="gutena-forms-notification-field-warning-box__icon dashicons dashicons-warning"
							aria-hidden="true"
						/>
						<p>{ FROM_EMAIL_DOMAIN_WARNING }</p>
					</div>
				) }
			</div>

			<NotificationFieldControl
				id="gutena-notification-cc"
				label={ __( 'CC', 'gutena-forms' ) }
				value={ draft.cc }
				onChange={ ( value ) => updateDraft( 'cc', value ) }
				mergeTags={ recipientTags }
				helpText={ MULTI_VALUE_HELP }
			/>

			<NotificationFieldControl
				id="gutena-notification-bcc"
				label={ __( 'BCC', 'gutena-forms' ) }
				value={ draft.bcc }
				onChange={ ( value ) => updateDraft( 'bcc', value ) }
				mergeTags={ recipientTags }
				helpText={ MULTI_VALUE_HELP }
			/>

			<NotificationFieldControl
				id="gutena-notification-reply_to"
				label={ __( 'Reply To', 'gutena-forms' ) }
				value={ draft.reply_to }
				onChange={ ( value ) => updateDraft( 'reply_to', value ) }
				mergeTags={ recipientTags }
				helpText={ MULTI_VALUE_HELP }
			/>

			<div className="gutena-forms-notification-reply-name-panel">
				<div className="gutena-forms-notification-field gutena-forms-notification-field--select">
					<SelectControl
						label={ __( 'Reply To Name ( First Name )', 'gutena-forms' ) }
						value={ draft.reply_to_name }
						options={ textFieldOptions }
						onChange={ ( value ) => updateDraft( 'reply_to_name', value ) }
						__nextHasNoMarginBottom
					/>
					<p className="gutena-forms-notification-field__help">
						{ __(
							'Select first or full name field for reply to address.',
							'gutena-forms'
						) }
					</p>
				</div>

				<div className="gutena-forms-notification-field gutena-forms-notification-field--select">
					<SelectControl
						label={ __( 'Reply To Name ( Last Name )', 'gutena-forms' ) }
						value={ draft.reply_to_last_name }
						options={ textFieldOptions }
						onChange={ ( value ) => updateDraft( 'reply_to_last_name', value ) }
						__nextHasNoMarginBottom
					/>
					<p className="gutena-forms-notification-field__help">
						{ __(
							'Select last name field for reply to address.',
							'gutena-forms'
						) }
					</p>
				</div>
			</div>
		</form>
	);
};

export default EmailNotificationsEditView;
