import { useState, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Modal, Button, TextControl, TextareaControl, SelectControl } from '@wordpress/components';

const MERGE_TAGS_EMAIL = [
	'{admin_email}',
	'{user_email}',
];

const MERGE_TAGS_CONTENT = [
	'{site_name}',
	'{site_url}',
	'{submission_date}',
	'{form_title}',
	'{admin_email}',
	'{user_email}',
	'{user_name}',
	'{First Name}',
	'{Last Name}',
	'{Email}',
	'{all_data}',
];

const MERGE_TAGS_NAME = [
	'{site_title}',
];

const MERGE_TAGS_FROM_EMAIL = [
	'{admin_email}',
];

const MergeTagButton = ( { tag, onClick } ) => (
	<button
		type="button"
		className="gf-notification-merge-tag-btn"
		onClick={ () => onClick( tag ) }
	>
		{ tag }
	</button>
);

const FieldRow = ( { label, required, children, mergeTags, onMergeTag, value, description } ) => (
	<div className="gf-notification-field-row">
		<div className="gf-notification-field-main">
			<div className="gf-notification-field-input">
				<label className="gf-notification-field-label">
					{ label }
					{ required && <span className="gf-notification-required">*</span> }
				</label>
				{ children }
				{ description && <p className="gf-notification-field-desc">{ description }</p> }
			</div>
			{ mergeTags && mergeTags.length > 0 && (
				<div className="gf-notification-merge-tags">
					{ mergeTags.map( ( tag ) => (
						<MergeTagButton key={ tag } tag={ tag } onClick={ onMergeTag } />
					) ) }
				</div>
			) }
		</div>
	</div>
);

const NotificationDetailModal = ( { notification: existingNotification, onSave, onClose } ) => {
	const defaults = {
		id: 'notif_' + Date.now(),
		enabled: true,
		name: __( 'Admin Notification Email', 'gutena-forms' ),
		send_to: '',
		subject: '',
		message: '',
		from_name: '',
		from_email: '',
		cc: '',
		bcc: '',
		reply_to: '',
		reply_to_first_name: '',
		reply_to_last_name: '',
	};

	const [ notification, setNotification ] = useState(
		existingNotification ? { ...defaults, ...existingNotification } : defaults
	);

	const [ errors, setErrors ] = useState( {} );

	const handleChange = useCallback( ( key, value ) => {
		setNotification( ( prev ) => ( { ...prev, [ key ]: value } ) );
		setErrors( ( prev ) => ( { ...prev, [ key ]: '' } ) );
	}, [] );

	const validate = useCallback( () => {
		const newErrors = {};
		if ( ! notification.send_to || '' === notification.send_to.trim() ) {
			newErrors.send_to = __(
				'Please enter a valid email address. Your notifications won\'t be sent if the field is not filled in correctly.',
				'gutena-forms'
			);
		}
		if ( ! notification.subject || '' === notification.subject.trim() ) {
			newErrors.subject = __( 'Subject is required.', 'gutena-forms' );
		}
		setErrors( newErrors );
		return Object.keys( newErrors ).length === 0;
	}, [ notification ] );

	const handleSave = useCallback( () => {
		console.log( 'handleSave called', notification );
		if ( validate() ) {
			console.log( 'validation passed, calling onSave' );
			onSave( notification );
		} else {
			console.log( 'validation failed', errors );
		}
	}, [ notification, validate, onSave ] );

	return (
		<Modal
			title={
				existingNotification
					? __( 'Edit Notification', 'gutena-forms' )
					: __( 'Add Notification', 'gutena-forms' )
			}
			onRequestClose={ onClose }
			className="gf-notification-detail-modal"
			__experimentalShowHeader
			style={ { maxWidth: '750px', display: 'flex', flexDirection: 'column', maxHeight: '80vh' } }
		>
			<div className="gf-notification-detail-fields" style={ { flex: '1', overflowY: 'auto' } }>
				<div className="gf-notification-field-row">
					<div className="gf-notification-field-main">
						<div className="gf-notification-field-input">
							<label className="gf-notification-field-label">
								{ __( 'Name', 'gutena-forms' ) }
							</label>
							<TextControl
								value={ notification.name }
								onChange={ ( value ) => handleChange( 'name', value ) }
								__nextHasNoMarginBottom
							/>
						</div>
					</div>
				</div>

				<FieldRow
					label={ __( 'Send Email To', 'gutena-forms' ) }
					required
					mergeTags={ MERGE_TAGS_EMAIL }
					onMergeTag={ ( tag ) =>
						handleChange(
							'send_to',
							notification.send_to
								? notification.send_to + ', ' + tag
								: tag
						)
					}
				>
					<TextControl
						value={ notification.send_to }
						onChange={ ( value ) => handleChange( 'send_to', value ) }
						placeholder={ __( 'admin@example.com, {admin_email}', 'gutena-forms' ) }
						__nextHasNoMarginBottom
					/>
				</FieldRow>
				{ errors.send_to && (
					<p className="gf-notification-error">{ errors.send_to }</p>
				) }

				<FieldRow
					label={ __( 'Subject', 'gutena-forms' ) }
					required
					mergeTags={ MERGE_TAGS_CONTENT.filter( ( t ) => t !== '{all_data}' ) }
					onMergeTag={ ( tag ) =>
						handleChange( 'subject', notification.subject + tag )
					}
				>
					<TextControl
						value={ notification.subject }
						onChange={ ( value ) => handleChange( 'subject', value ) }
						placeholder={ __( 'New Form Submission - {form_title}', 'gutena-forms' ) }
						__nextHasNoMarginBottom
					/>
				</FieldRow>
				{ errors.subject && (
					<p className="gf-notification-error">{ errors.subject }</p>
				) }

				<div className="gf-notification-field-row">
					<div className="gf-notification-field-main">
						<div className="gf-notification-field-input gf-notification-field-full">
							<label className="gf-notification-field-label">
								{ __( 'Email Message', 'gutena-forms' ) }
							</label>
							<TextareaControl
								value={ notification.message }
								onChange={ ( value ) => handleChange( 'message', value ) }
								placeholder={ __(
									'Thank you for your submission!',
									'gutena-forms'
								) }
								rows={ 6 }
								__nextHasNoMarginBottom
							/>
						</div>
						<div className="gf-notification-merge-tags">
							{ MERGE_TAGS_CONTENT.map( ( tag ) => (
								<MergeTagButton
									key={ tag }
									tag={ tag }
									onClick={ () =>
										handleChange(
											'message',
											notification.message
												? notification.message + '\n' + tag
												: tag
										)
									}
								/>
							) ) }
						</div>
					</div>
				</div>

				<FieldRow
					label={ __( 'From Name', 'gutena-forms' ) }
					mergeTags={ MERGE_TAGS_NAME }
					onMergeTag={ ( tag ) => handleChange( 'from_name', tag ) }
				>
					<TextControl
						value={ notification.from_name }
						onChange={ ( value ) => handleChange( 'from_name', value ) }
						placeholder={ __( 'Leave empty to use site name', 'gutena-forms' ) }
						__nextHasNoMarginBottom
					/>
				</FieldRow>

				<FieldRow
					label={ __( 'From Email', 'gutena-forms' ) }
					mergeTags={ MERGE_TAGS_FROM_EMAIL }
					onMergeTag={ ( tag ) => handleChange( 'from_email', tag ) }
					description={ __( 'This email must be unique, otherwise it will override the previous one. This is to prevent the email from being marked as spam.', 'gutena-forms' ) }
				>
					<TextControl
						value={ notification.from_email }
						onChange={ ( value ) => handleChange( 'from_email', value ) }
						placeholder={ __( 'Leave empty to use admin email', 'gutena-forms' ) }
						__nextHasNoMarginBottom
					/>
				</FieldRow>

				<FieldRow
					label={ __( 'CC (Carbon Copy)', 'gutena-forms' ) }
					mergeTags={ MERGE_TAGS_EMAIL }
					onMergeTag={ ( tag ) =>
						handleChange(
							'cc',
							notification.cc
								? notification.cc + ', ' + tag
								: tag
						)
					}
				>
					<TextControl
						value={ notification.cc }
						onChange={ ( value ) => handleChange( 'cc', value ) }
						placeholder={ __( 'Comma separated email addresses', 'gutena-forms' ) }
						__nextHasNoMarginBottom
					/>
				</FieldRow>

				<FieldRow
					label={ __( 'BCC (Blind Carbon Copy)', 'gutena-forms' ) }
					mergeTags={ MERGE_TAGS_EMAIL }
					onMergeTag={ ( tag ) =>
						handleChange(
							'bcc',
							notification.bcc
								? notification.bcc + ', ' + tag
								: tag
						)
					}
				>
					<TextControl
						value={ notification.bcc }
						onChange={ ( value ) => handleChange( 'bcc', value ) }
						placeholder={ __( 'Comma separated email addresses', 'gutena-forms' ) }
						__nextHasNoMarginBottom
					/>
				</FieldRow>

				<FieldRow
					label={ __( 'Reply To', 'gutena-forms' ) }
					mergeTags={ MERGE_TAGS_EMAIL }
					onMergeTag={ ( tag ) => handleChange( 'reply_to', tag ) }
				>
					<TextControl
						value={ notification.reply_to }
						onChange={ ( value ) => handleChange( 'reply_to', value ) }
						placeholder={ __( 'Email address or merge tag', 'gutena-forms' ) }
						__nextHasNoMarginBottom
					/>
				</FieldRow>

			<div className="gf-notification-reply-to-section">
				<div className="gf-notification-field-row">
					<div className="gf-notification-field-main">
						<div className="gf-notification-field-input">
							<label className="gf-notification-field-label">
								{ __( 'Reply To Name ( First Name )', 'gutena-forms' ) }
							</label>
							<SelectControl
								value={ notification.reply_to_first_name }
								onChange={ ( value ) => handleChange( 'reply_to_first_name', value ) }
								options={ [
									{ label: __( 'First Name', 'gutena-forms' ), value: '' },
									{ label: '{first_name}', value: '{first_name}' },
									{ label: '{last_name}', value: '{last_name}' },
									{ label: '{email}', value: '{email}' },
									{ label: '{phone}', value: '{phone}' },
									{ label: '{message}', value: '{message}' },
								] }
								__nextHasNoMarginBottom
							/>
							<p className="gf-notification-field-desc">
								{ __( 'Select first or full name field for reply to address.', 'gutena-forms' ) }
							</p>
						</div>
					</div>
				</div>
				<div className="gf-notification-field-row">
					<div className="gf-notification-field-main">
						<div className="gf-notification-field-input">
							<label className="gf-notification-field-label">
								{ __( 'Reply To Name ( Last Name )', 'gutena-forms' ) }
							</label>
							<SelectControl
								value={ notification.reply_to_last_name }
								onChange={ ( value ) => handleChange( 'reply_to_last_name', value ) }
								options={ [
									{ label: __( 'Last Name', 'gutena-forms' ), value: '' },
									{ label: '{first_name}', value: '{first_name}' },
									{ label: '{last_name}', value: '{last_name}' },
									{ label: '{email}', value: '{email}' },
									{ label: '{phone}', value: '{phone}' },
									{ label: '{message}', value: '{message}' },
								] }
								__nextHasNoMarginBottom
							/>
							<p className="gf-notification-field-desc">
								{ __( 'Select last name field for reply to address.', 'gutena-forms' ) }
							</p>
						</div>
					</div>
				</div>
			</div>
			</div>
			<div className="gf-notification-detail-footer">
				<Button variant="secondary" onClick={ onClose }>
					{ __( 'Cancel', 'gutena-forms' ) }
				</Button>
				<Button variant="primary" onClick={ handleSave }>
					{ __( 'Save', 'gutena-forms' ) }
				</Button>
			</div>
		</Modal>
	);
};

export default NotificationDetailModal;
