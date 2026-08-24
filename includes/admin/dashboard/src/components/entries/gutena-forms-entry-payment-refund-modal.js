import { __, sprintf } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { Modal, Button } from '@wordpress/components';
import { refundEntryPayment } from '../../api/payments';

const GutenaFormsEntryPaymentRefundModal = ( {
	isOpen,
	onClose,
	entryId,
	payment,
	onRefunded,
} ) => {
	const refundableCents = payment?.refundable_amount || 0;
	const defaultAmount = ( refundableCents / 100 ).toFixed( 2 );
	const paymentReference = `#${ entryId }`;

	const [ amount, setAmount ] = useState( defaultAmount );
	const [ notes, setNotes ] = useState( '' );
	const [ error, setError ] = useState( '' );
	const [ submitting, setSubmitting ] = useState( false );

	useEffect( () => {
		if ( isOpen ) {
			setAmount( ( refundableCents / 100 ).toFixed( 2 ) );
			setNotes( '' );
			setError( '' );
			setSubmitting( false );
		}
	}, [ isOpen, refundableCents ] );

	const amountCents = Math.round( parseFloat( amount || '0' ) * 100 );
	const isFullRefund = amountCents > 0 && amountCents === refundableCents;

	const handleSubmit = () => {
		setError( '' );

		if ( ! amountCents || amountCents <= 0 ) {
			setError( __( 'Please enter a valid refund amount.', 'gutena-forms' ) );
			return;
		}

		if ( amountCents > refundableCents ) {
			setError(
				__( 'Maximum refundable amount: ', 'gutena-forms' ) + ( payment?.refundable_formatted || '' )
			);
			return;
		}

		setSubmitting( true );

		refundEntryPayment( entryId, amountCents, notes )
			.then( ( response ) => {
				onRefunded( response.payment );
				onClose();
			} )
			.catch( ( err ) => {
				setError( err.message || __( 'Refund failed. Please try again.', 'gutena-forms' ) );
			} )
			.finally( () => {
				setSubmitting( false );
			} );
	};

	if ( ! isOpen ) {
		return null;
	}

	return (
		<Modal
			title=""
			onRequestClose={ onClose }
			className="gutena-forms__payment-refund-modal"
		>
			<div className="gutena-forms__payment-refund-modal__body">
				<div className="gutena-forms__payment-refund-modal__heading">
					<h2 className="gutena-forms__payment-refund-modal__title">
						{ __( 'Refund Payment', 'gutena-forms' ) }
					</h2>
					<p className="gutena-forms__payment-refund-modal__intro">
						{ sprintf(
							/* translators: %s: payment reference id */
							__( 'Process for payment %s. The refunded amount will be sent to the customer\'s original payment method.', 'gutena-forms' ),
							paymentReference
						) }
					</p>
				</div>

				<div className="gutena-forms__payment-refund-modal__field">
					<label htmlFor="gutena-forms-refund-amount">{ __( 'Refund Amount', 'gutena-forms' ) }</label>
					<input
						id="gutena-forms-refund-amount"
						type="number"
						min="0"
						step="0.01"
						value={ amount }
						onChange={ ( e ) => setAmount( e.target.value ) }
						className="gutena-forms__payment-refund-modal__input"
					/>
					<p className="gutena-forms__payment-refund-modal__help">
						{ __( 'Maximum refundable amount: ', 'gutena-forms' ) }
						{ payment?.refundable_formatted || '' }
					</p>
				</div>

				<div className="gutena-forms__payment-refund-modal__field">
					<label htmlFor="gutena-forms-refund-notes">{ __( 'Refund Notes (Optional)', 'gutena-forms' ) }</label>
					<textarea
						id="gutena-forms-refund-notes"
						value={ notes }
						onChange={ ( e ) => setNotes( e.target.value ) }
						className="gutena-forms__payment-refund-modal__textarea"
						rows={ 4 }
						placeholder={ __( 'Add a reason or note for this refund…', 'gutena-forms' ) }
					/>
					<p className="gutena-forms__payment-refund-modal__help">
						{ __( 'This note will be stored with the refund record for future reference.', 'gutena-forms' ) }
					</p>
				</div>

				{ isFullRefund && (
					<div className="gutena-forms__payment-refund-modal__notice">
						{ __( 'This will issue a complete refund of ', 'gutena-forms' ) }
						<strong>{ payment?.refundable_formatted || '' }</strong>
						{ '. ' }
						{ __( 'The entire payment will be refunded.', 'gutena-forms' ) }
					</div>
				) }

				{ error && (
					<p className="gutena-forms__payment-refund-modal__error">{ error }</p>
				) }

				<div className="gutena-forms__payment-refund-modal__actions">
					<Button
						variant="secondary"
						className="gutena-forms__payment-refund-modal__cancel-btn"
						onClick={ onClose }
						disabled={ submitting }
					>
						{ __( 'Cancel', 'gutena-forms' ) }
					</Button>
					<Button
						variant="primary"
						className="gutena-forms__payment-refund-modal__submit-btn"
						onClick={ handleSubmit }
						disabled={ submitting }
					>
						{ submitting ? __( 'Processing…', 'gutena-forms' ) : __( 'Process Refund', 'gutena-forms' ) }
					</Button>
				</div>
			</div>
		</Modal>
	);
};

export default GutenaFormsEntryPaymentRefundModal;
