import { __ } from '@wordpress/i18n';

const TemplateFormPreviewSuccess = ( { message } ) => {
	const successMessage = message || __( 'Your form submitted successfully!', 'gutena-forms' );

	return (
		<div className="wp-block-gutena-form-confirm-msg" role="status" aria-live="polite">
			<div
				className="wp-block-group"
				style={ {
					padding: '12px',
					background: '#d8eacc',
					borderRadius: '5px',
				} }
			>
				<p className="has-tiny-font-size">{ successMessage }</p>
			</div>
		</div>
	);
};

export default TemplateFormPreviewSuccess;
