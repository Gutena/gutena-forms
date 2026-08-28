import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { toast } from 'react-toastify';
import { gutenaFormsFetchSettings, gutenaFormsUpdateSettings } from '../api';
import SettingsLoading from '../skeletons/settings-loading';

const MERGE_TAGS_EMAIL = [ '{admin_email}', '{user_email}' ];

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

const MERGE_TAGS_NAME = [ '{site_title}' ];

const FIELD_OPTIONS_FIRST_NAME = [
	{ label: __( 'First Name', 'gutena-forms' ), value: '' },
	{ label: '{first_name}', value: '{first_name}' },
	{ label: '{last_name}', value: '{last_name}' },
	{ label: '{email}', value: '{email}' },
	{ label: '{phone}', value: '{phone}' },
	{ label: '{message}', value: '{message}' },
];

const FIELD_OPTIONS_LAST_NAME = [
	{ label: __( 'Last Name', 'gutena-forms' ), value: '' },
	{ label: '{first_name}', value: '{first_name}' },
	{ label: '{last_name}', value: '{last_name}' },
	{ label: '{email}', value: '{email}' },
	{ label: '{phone}', value: '{phone}' },
	{ label: '{message}', value: '{message}' },
];

const GutenaFormEmailNotifications = () => {
	const [ loading, setLoading ] = useState( true );
	const [ saving, setSaving ] = useState( false );
	const [ sendTo, setSendTo ] = useState( '' );
	const [ subject, setSubject ] = useState( '' );
	const [ message, setMessage ] = useState( '' );
	const [ fromName, setFromName ] = useState( '' );
	const [ fromEmail, setFromEmail ] = useState( '' );
	const [ cc, setCc ] = useState( '' );
	const [ bcc, setBcc ] = useState( '' );
	const [ replyTo, setReplyTo ] = useState( '' );
	const [ replyToFirstName, setReplyToFirstName ] = useState( '' );
	const [ replyToLastName, setReplyToLastName ] = useState( '' );
	const [ activeField, setActiveField ] = useState( 'subject' );

	const subjectRef = useRef( null );
	const messageRef = useRef( null );

	useEffect( () => {
		setLoading( true );
		gutenaFormsFetchSettings( 'email-notifications' )
			.then( ( response ) => {
				const values = response?.values || {};
				setSendTo( values.send_to || '' );
				setSubject( values.subject || '' );
				setMessage( values.message || '' );
				setFromName( values.from_name || '' );
				setFromEmail( values.from_email || '' );
				setCc( values.cc || '' );
				setBcc( values.bcc || '' );
				setReplyTo( values.reply_to || '' );
				setReplyToFirstName( values.reply_to_first_name || '' );
				setReplyToLastName( values.reply_to_last_name || '' );
			} )
			.finally( () => setLoading( false ) );
	}, [] );

	const insertMergeTag = ( tag ) => {
		const fieldMap = {
			subject: [ subject, setSubject, subjectRef ],
			message: [ message, setMessage, messageRef ],
			send_to: [ sendTo, setSendTo, null ],
			from_name: [ fromName, setFromName, null ],
			from_email: [ fromEmail, setFromEmail, null ],
			cc: [ cc, setCc, null ],
			bcc: [ bcc, setBcc, null ],
			reply_to: [ replyTo, setReplyTo, null ],
		};

		const field = fieldMap[ activeField ] || fieldMap.subject;
		const [ currentValue, setter, ref ] = field;

		if ( ref?.current && typeof ref.current.selectionStart === 'number' ) {
			const element = ref.current;
			const start = element.selectionStart;
			const end = element.selectionEnd;
			const nextValue = currentValue.slice( 0, start ) + tag + currentValue.slice( end );
			setter( nextValue );

			requestAnimationFrame( () => {
				element.focus();
				const cursor = start + tag.length;
				element.setSelectionRange( cursor, cursor );
			} );
			return;
		}

		setter( currentValue + tag );
	};

	const handleSave = () => {
		setSaving( true );
		gutenaFormsUpdateSettings( 'email-notifications', {
			send_to: sendTo,
			subject,
			message,
			from_name: fromName,
			from_email: fromEmail,
			cc,
			bcc,
			reply_to: replyTo,
			reply_to_first_name: replyToFirstName,
			reply_to_last_name: replyToLastName,
		} )
			.then( () => {
				toast.success( __( 'Settings updated successfully.', 'gutena-forms' ) );
			} )
			.finally( () => setSaving( false ) );
	};

	if ( loading ) {
		return <SettingsLoading />;
	}

	return (
		<div className="gf-email-notifications-screen">
			<div className="gf-screen-header">
				<h1 className="gf-screen-title">
					{ __( 'Email Notifications', 'gutena-forms' ) }
				</h1>
				<p className="gf-screen-subtitle">
					{ __( 'Configure default settings that apply to newly created forms.', 'gutena-forms' ) }
				</p>
			</div>

			<div className="gutena-forms__settings-meta-box">
				<div className="gutena-forms__field-container">
					<label htmlFor="gf-en-send-to">
						{ __( 'Send Email To', 'gutena-forms' ) }
						<span className="gf-required">*</span>
					</label>
					<input
						id="gf-en-send-to"
						type="text"
						value={ sendTo }
						onChange={ ( event ) => setSendTo( event.target.value ) }
						onFocus={ () => setActiveField( 'send_to' ) }
						placeholder={ __( 'admin@example.com, {admin_email}', 'gutena-forms' ) }
					/>
					<div className="gf-merge-tags-display">
						{ MERGE_TAGS_EMAIL.map( ( tag ) => (
							<button
								key={ tag }
								type="button"
								className="gf-merge-tag-item"
								onClick={ () => insertMergeTag( tag ) }
							>
								{ tag }
							</button>
						) ) }
					</div>
				</div>

				<div className="gutena-forms__field-container">
					<label htmlFor="gf-en-subject">
						{ __( 'Subject', 'gutena-forms' ) }
						<span className="gf-required">*</span>
					</label>
					<input
						ref={ subjectRef }
						id="gf-en-subject"
						type="text"
						value={ subject }
						onChange={ ( event ) => setSubject( event.target.value ) }
						onFocus={ () => setActiveField( 'subject' ) }
						placeholder={ __( 'New Form Submission - {form_title}', 'gutena-forms' ) }
					/>
					<div className="gf-merge-tags-display">
						{ MERGE_TAGS_CONTENT.filter( ( t ) => t !== '{all_data}' ).map( ( tag ) => (
							<button
								key={ tag }
								type="button"
								className="gf-merge-tag-item"
								onClick={ () => insertMergeTag( tag ) }
							>
								{ tag }
							</button>
						) ) }
					</div>
				</div>

				<div className="gutena-forms__field-container">
					<label htmlFor="gf-en-message">
						{ __( 'Email Message', 'gutena-forms' ) }
					</label>
					<textarea
						ref={ messageRef }
						id="gf-en-message"
						value={ message }
						onChange={ ( event ) => setMessage( event.target.value ) }
						onFocus={ () => setActiveField( 'message' ) }
						rows={ 6 }
						placeholder={ __( 'Thank you for your submission!', 'gutena-forms' ) }
					/>
					<div className="gf-merge-tags-display">
						{ MERGE_TAGS_CONTENT.map( ( tag ) => (
							<button
								key={ tag }
								type="button"
								className="gf-merge-tag-item"
								onClick={ () => insertMergeTag( tag ) }
							>
								{ tag }
							</button>
						) ) }
					</div>
				</div>

				<div className="gutena-forms__field-container">
					<label htmlFor="gf-en-from-name">
						{ __( 'From Name', 'gutena-forms' ) }
					</label>
					<input
						id="gf-en-from-name"
						type="text"
						value={ fromName }
						onChange={ ( event ) => setFromName( event.target.value ) }
						onFocus={ () => setActiveField( 'from_name' ) }
						placeholder={ __( 'Leave empty to use site name', 'gutena-forms' ) }
					/>
					<div className="gf-merge-tags-display">
						{ MERGE_TAGS_NAME.map( ( tag ) => (
							<button
								key={ tag }
								type="button"
								className="gf-merge-tag-item"
								onClick={ () => insertMergeTag( tag ) }
							>
								{ tag }
							</button>
						) ) }
					</div>
				</div>

				<div className="gutena-forms__field-container">
					<label htmlFor="gf-en-from-email">
						{ __( 'From Email', 'gutena-forms' ) }
						<span className="gf-required">*</span>
					</label>
					<input
						id="gf-en-from-email"
						type="text"
						value={ fromEmail }
						onChange={ ( event ) => setFromEmail( event.target.value ) }
						onFocus={ () => setActiveField( 'from_email' ) }
						placeholder={ __( 'Leave empty to use admin email', 'gutena-forms' ) }
					/>
					<p className="gutena-forms__field-description">
						{ __( 'This email must be unique, otherwise it will override the previous one. This is to prevent the email from being marked as spam.', 'gutena-forms' ) }
					</p>
					<div className="gf-merge-tags-display">
						{ MERGE_TAGS_EMAIL.map( ( tag ) => (
							<button
								key={ tag }
								type="button"
								className="gf-merge-tag-item"
								onClick={ () => insertMergeTag( tag ) }
							>
								{ tag }
							</button>
						) ) }
					</div>
				</div>

				<div className="gutena-forms__field-container">
					<label htmlFor="gf-en-cc">
						{ __( 'CC (Carbon Copy)', 'gutena-forms' ) }
					</label>
					<input
						id="gf-en-cc"
						type="text"
						value={ cc }
						onChange={ ( event ) => setCc( event.target.value ) }
						onFocus={ () => setActiveField( 'cc' ) }
						placeholder={ __( 'Comma separated email addresses', 'gutena-forms' ) }
					/>
					<div className="gf-merge-tags-display">
						{ MERGE_TAGS_EMAIL.map( ( tag ) => (
							<button
								key={ tag }
								type="button"
								className="gf-merge-tag-item"
								onClick={ () => insertMergeTag( tag ) }
							>
								{ tag }
							</button>
						) ) }
					</div>
				</div>

				<div className="gutena-forms__field-container">
					<label htmlFor="gf-en-bcc">
						{ __( 'BCC (Blind Carbon Copy)', 'gutena-forms' ) }
					</label>
					<input
						id="gf-en-bcc"
						type="text"
						value={ bcc }
						onChange={ ( event ) => setBcc( event.target.value ) }
						onFocus={ () => setActiveField( 'bcc' ) }
						placeholder={ __( 'Comma separated email addresses', 'gutena-forms' ) }
					/>
					<div className="gf-merge-tags-display">
						{ MERGE_TAGS_EMAIL.map( ( tag ) => (
							<button
								key={ tag }
								type="button"
								className="gf-merge-tag-item"
								onClick={ () => insertMergeTag( tag ) }
							>
								{ tag }
							</button>
						) ) }
					</div>
				</div>

				<div className="gutena-forms__field-container">
					<label htmlFor="gf-en-reply-to">
						{ __( 'Reply To', 'gutena-forms' ) }
					</label>
					<input
						id="gf-en-reply-to"
						type="text"
						value={ replyTo }
						onChange={ ( event ) => setReplyTo( event.target.value ) }
						onFocus={ () => setActiveField( 'reply_to' ) }
						placeholder={ __( 'Email address or merge tag', 'gutena-forms' ) }
					/>
					<div className="gf-merge-tags-display">
						{ MERGE_TAGS_EMAIL.map( ( tag ) => (
							<button
								key={ tag }
								type="button"
								className="gf-merge-tag-item"
								onClick={ () => insertMergeTag( tag ) }
							>
								{ tag }
							</button>
						) ) }
					</div>
				</div>

			<div className="gutena-forms__field-container">
					<label htmlFor="gf-en-reply-to-first-name">
						{ __( 'Reply To Name ( First Name )', 'gutena-forms' ) }
					</label>
					<select
						id="gf-en-reply-to-first-name"
						value={ replyToFirstName }
						onChange={ ( event ) => setReplyToFirstName( event.target.value ) }
					>
						{ FIELD_OPTIONS_FIRST_NAME.map( ( opt ) => (
							<option key={ opt.value } value={ opt.value }>
								{ opt.label }
							</option>
						) ) }
					</select>
					<p className="gutena-forms__field-description">
						{ __( 'Select first or full name field for reply to address.', 'gutena-forms' ) }
					</p>
				</div>
				<div className="gutena-forms__field-container">
					<label htmlFor="gf-en-reply-to-last-name">
						{ __( 'Reply To Name ( Last Name )', 'gutena-forms' ) }
					</label>
					<select
						id="gf-en-reply-to-last-name"
						value={ replyToLastName }
						onChange={ ( event ) => setReplyToLastName( event.target.value ) }
					>
						{ FIELD_OPTIONS_LAST_NAME.map( ( opt ) => (
							<option key={ opt.value } value={ opt.value }>
								{ opt.label }
							</option>
						) ) }
					</select>
					<p className="gutena-forms__field-description">
						{ __( 'Select last name field for reply to address.', 'gutena-forms' ) }
					</p>
				</div>

				<div className="gutena-forms__submit-button">
					<button
						type="button"
						onClick={ handleSave }
						disabled={ saving }
					>
						{ saving
							? __( 'Saving…', 'gutena-forms' )
							: __( 'Save Changes', 'gutena-forms' ) }
					</button>
				</div>
			</div>
		</div>
	);
};

export default GutenaFormEmailNotifications;
