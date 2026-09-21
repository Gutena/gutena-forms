import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { toast } from 'react-toastify';
import Copy from '../icons/copy';

const GutenaFormsRestApi = ( props ) => {
	const { endpoint, apiKey } = props;

	const copyToClipboard = ( text, label ) => {
		if ( ! text ) {
			return;
		}
		try {
			navigator.clipboard.writeText( text ).then( () => {
				toast.success( label );
			} );
		} catch {
			// Fallback for older browsers.
			const textarea = document.createElement( 'textarea' );
			textarea.value = text;
			document.body.appendChild( textarea );
			textarea.select();
			try {
				document.execCommand( 'copy' );
				toast.success( label );
			} catch {
			}
			document.body.removeChild( textarea );
		}
	};

	const exampleRequest = `POST ${ endpoint }
Content-Type: application/json
X-Gutena-API-Key: ${ apiKey }

{
  "form_id": "123",
  "fields": {
    "your_name": "John Doe",
    "your_email": "john@example.com",
    "your_phone": "+923001234567"
  }
}`;

	const exampleResponse = `{ "status": "Success", "message": "Entry stored" }`;

	return (
		<div className={ 'gutena-forms__rest-api-wrapper' }>
			<h6>{ __( 'REST API Endpoint', 'gutena-forms' ) }</h6>
			<p>
				{ __( 'Use the URL below to submit form entries from external apps such as a chat bot. Submissions are stored in your Gutena Forms entries, just like normal form submissions.', 'gutena-forms' ) }
			</p>

			<div className={ 'gutena-forms__endpoint-row' }>
				<code className={ 'gutena-forms__endpoint-url' }>{ endpoint }</code>
				<div
					className={ 'gutena-forms__copy-icon' }
					onClick={ () => copyToClipboard( endpoint, __( 'Endpoint URL copied!', 'gutena-forms' ) ) }
					role="presentation"
				>
					<Copy />
				</div>
			</div>

			{ apiKey && (
				<>
					<h6>{ __( 'Your API Key', 'gutena-forms' ) }</h6>
					<p>
						{ __( 'Send this key in the X-Gutena-API-Key header of every request. Keep it secret — anyone with this key can submit entries to your forms.', 'gutena-forms' ) }
					</p>
					<div className={ 'gutena-forms__endpoint-row' }>
						<code className={ 'gutena-forms__api-key' }>{ apiKey }</code>
						<div
							className={ 'gutena-forms__copy-icon' }
							onClick={ () => copyToClipboard( apiKey, __( 'API key copied!', 'gutena-forms' ) ) }
							role="presentation"
						>
							<Copy />
						</div>
					</div>
				</>
			) }

			<h6>{ __( 'How to Pass Fields', 'gutena-forms' ) }</h6>
			<p>
				{ __( 'Send a POST request with a JSON body. The "form_id" is the Gutena Form post ID (a number). The "fields" object keys must match the Field ID (name_attr) of each field in your form. Find the Field ID for each field in the form editor.', 'gutena-forms' ) }
			</p>

			<h6>{ __( 'Finding the Form ID', 'gutena-forms' ) }</h6>
			<p>
				{ __( 'The "form_id" is the numeric post ID of your Gutena Form. You can find it in two places:', 'gutena-forms' ) }
			</p>
			<ul>
				<li>{ __( 'Go to Gutena Forms → Forms. The post ID appears in the edit URL when you click the edit (pencil) icon, e.g. post.php?post=123&action=edit — the number after post= is your form_id.', 'gutena-forms' ) }</li>
				<li>{ __( 'In the form editor, the post ID is shown in the browser address bar while editing the form.', 'gutena-forms' ) }</li>
			</ul>
			<p>
				{ __( 'You may also pass the internal block formID string (e.g. gutena_forms_ID_...) if you have it — both are accepted.', 'gutena-forms' ) }
			</p>

			<pre>
				<div
					className={ 'gutena-forms__copy-icon' }
					onClick={ () => copyToClipboard( exampleRequest, __( 'Example request copied!', 'gutena-forms' ) ) }
					role="presentation"
				>
					<Copy />
				</div>
				<div id={ 'gutena-forms__rest-api-request' }>{ exampleRequest }</div>
			</pre>

			<h6>{ __( 'Example Response', 'gutena-forms' ) }</h6>
			<pre>
				<div id={ 'gutena-forms__rest-api-response' }>{ exampleResponse }</div>
			</pre>

			<h6>{ __( 'Notes', 'gutena-forms' ) }</h6>
			<ul>
				<li>{ __( 'When the REST API toggle is off, the endpoint returns a 404.', 'gutena-forms' ) }</li>
				<li>{ __( 'When "Require API Key" is on, requests without a valid X-Gutena-API-Key header return a 401.', 'gutena-forms' ) }</li>
				<li>{ __( 'The form_id accepts either the Gutena Form post ID (numeric) or the internal block formID string. The post ID is resolved to the block formID automatically.', 'gutena-forms' ) }</li>
				<li>{ __( 'Unknown field keys are silently skipped. The form_id must match an existing Gutena Form.', 'gutena-forms' ) }</li>
			</ul>
		</div>
	);
};

export default GutenaFormsRestApi;
