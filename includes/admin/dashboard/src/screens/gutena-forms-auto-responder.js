import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { toast } from 'react-toastify';
import { gutenaFormsFetchSettings, gutenaFormsUpdateSettings } from '../api';
import SettingsLoading from '../skeletons/settings-loading';

const MERGE_TAGS = [
	'{site_name}',
	'{site_url}',
	'{submission_date}',
	'{form-title}',
	'{admin_email}',
];

const DEFAULT_MESSAGE =
	'Thankyou for your submission!\n\nDear {Name},\n\nThank you for contacting us through our contact form. We have received your message and will get back to you as soon as possible.';

const GutenaFormsAutoResponder = () => {
	const [ loading, setLoading ] = useState( true );
	const [ saving, setSaving ] = useState( false );
	const [ enable, setEnable ] = useState( false );
	const [ subject, setSubject ] = useState( '' );
	const [ message, setMessage ] = useState( '' );
	const [ activeField, setActiveField ] = useState( 'subject' );

	const subjectRef = useRef( null );
	const messageRef = useRef( null );

	useEffect( () => {
		setLoading( true );
		gutenaFormsFetchSettings( 'auto-responder' )
			.then( ( response ) => {
				const values = response?.values || {};

				setEnable( !! values.enable );
				setSubject(
					values.subject ||
						values.default_subject ||
						__( 'Thankyou for your submission', 'gutena-forms' )
				);
				setMessage(
					values.message || values.default_message || DEFAULT_MESSAGE
				);
			} )
			.finally( () => setLoading( false ) );
	}, [] );

	const insertMergeTag = ( tag ) => {
		if ( ! enable ) {
			return;
		}

		const field = activeField === 'message' ? 'message' : 'subject';
		const ref = field === 'message' ? messageRef : subjectRef;
		const currentValue = field === 'message' ? message : subject;
		const element = ref.current;

		if ( element && typeof element.selectionStart === 'number' ) {
			const start = element.selectionStart;
			const end = element.selectionEnd;
			const nextValue = currentValue.slice( 0, start ) + tag + currentValue.slice( end );

			if ( field === 'message' ) {
				setMessage( nextValue );
			} else {
				setSubject( nextValue );
			}

			requestAnimationFrame( () => {
				element.focus();
				const cursor = start + tag.length;
				element.setSelectionRange( cursor, cursor );
			} );
			return;
		}

		if ( field === 'message' ) {
			setMessage( `${ message }${ tag }` );
		} else {
			setSubject( `${ subject }${ tag }` );
		}
	};

	const handleSave = () => {
		setSaving( true );
		gutenaFormsUpdateSettings( 'auto-responder', {
			enable,
			subject,
			message,
		} )
			.then( () => {
				toast.success( __( 'Settings updated successfully.', 'gutena-forms' ) );
			} )
			.finally( () => setSaving( false ) );
	};

	if ( loading ) {
		return <SettingsLoading />;
	}

	const fieldsDisabled = ! enable;

	return (
		<div className="gutena-forms__auto-responder">
			<div className="gutena-forms__auto-responder-toggle-row">
				<button
					type="button"
					className={ `gutena-forms__auto-responder-switch${ enable ? ' is-on' : '' }` }
					role="switch"
					aria-checked={ enable }
					onClick={ () => setEnable( ! enable ) }
				>
					<span className="gutena-forms__auto-responder-switch-thumb" />
				</button>
				<div className="gutena-forms__auto-responder-toggle-copy">
					<p className="gutena-forms__auto-responder-toggle-title">
						{ __( 'Enable Auto-Responder', 'gutena-forms' ) }
					</p>
					<p className="gutena-forms__auto-responder-toggle-desc">
						{ __( 'Send an automatic reply to users who submit the form.', 'gutena-forms' ) }
					</p>
				</div>
			</div>

			<div className={ `gutena-forms__auto-responder-section${ fieldsDisabled ? ' is-disabled' : '' }` }>
				<label className="gutena-forms__auto-responder-label" htmlFor="gutena-auto-responder-subject">
					{ __( 'Subject', 'gutena-forms' ) }
				</label>
				<input
					ref={ subjectRef }
					id="gutena-auto-responder-subject"
					type="text"
					className="gutena-forms__auto-responder-input"
					value={ subject }
					onChange={ ( event ) => setSubject( event.target.value ) }
					onFocus={ () => setActiveField( 'subject' ) }
					disabled={ fieldsDisabled }
				/>

				<p className="gutena-forms__auto-responder-merge-label">{ __( 'Merge Tags:', 'gutena-forms' ) }</p>
				<div className="gutena-forms__auto-responder-merge-tags">
					{ MERGE_TAGS.map( ( tag ) => (
						<button
							key={ tag }
							type="button"
							className="gutena-forms__auto-responder-merge-tag"
							disabled={ fieldsDisabled }
							onClick={ () => insertMergeTag( tag ) }
						>
							{ tag }
						</button>
					) ) }
				</div>
			</div>

			<div className={ `gutena-forms__auto-responder-section${ fieldsDisabled ? ' is-disabled' : '' }` }>
				<label className="gutena-forms__auto-responder-label" htmlFor="gutena-auto-responder-message">
					{ __( 'Message', 'gutena-forms' ) }
				</label>
				<textarea
					ref={ messageRef }
					id="gutena-auto-responder-message"
					className="gutena-forms__auto-responder-textarea"
					value={ message }
					onChange={ ( event ) => setMessage( event.target.value ) }
					onFocus={ () => setActiveField( 'message' ) }
					disabled={ fieldsDisabled }
					rows={ 6 }
				/>
			</div>

			<button
				type="button"
				className="gutena-forms__auto-responder-save"
				onClick={ handleSave }
				disabled={ saving }
			>
				{ __( 'Save Changes', 'gutena-forms' ) }
			</button>
		</div>
	);
};

export default GutenaFormsAutoResponder;
