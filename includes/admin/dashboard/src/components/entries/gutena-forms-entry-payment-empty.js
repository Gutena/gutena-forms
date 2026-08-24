import { __ } from '@wordpress/i18n';

const GutenaFormsEntryPaymentEmpty = () => {
	return (
		<div className="gutena-forms__entry-payment-empty">
			<h3 className="heading">{ __( 'Payment', 'gutena-forms' ) }</h3>
			<p className="description">
				{ __( 'This entry does not have a payment associated with it.', 'gutena-forms' ) }
			</p>
		</div>
	);
};

export default GutenaFormsEntryPaymentEmpty;
