import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, SelectControl, TextControl } from '@wordpress/components';
import { close } from '@wordpress/icons';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import FormConfirmationMessageField from './components/form-confirmation-message-field';
import FormConfirmationRadioGroup from './components/form-confirmation-radio-group';
import {
	cloneConfirmation,
	isValidRedirectUrl,
	sanitizeConfirmation,
	validateConfirmationForSave,
} from './form-confirmation-utils';
import { getConfirmationTagItems } from './form-confirmation-merge-tags';

const FormConfirmationModal = ( {
	isOpen,
	initialConfirmation,
	confirmationDefaults,
	formFields = [],
	onSave,
	onClose,
} ) => {
	const [ draft, setDraft ] = useState( null );
	const [ redirectUrlError, setRedirectUrlError ] = useState( '' );
	const initialConfirmationRef = useRef( initialConfirmation );

	useEffect( () => {
		initialConfirmationRef.current = initialConfirmation;
	}, [ initialConfirmation ] );

	const pages = useSelect(
		( select ) =>
			select( coreStore ).getEntityRecords( 'postType', 'page', {
				per_page: -1,
				status: 'publish',
			} ) || [],
		[]
	);

	useEffect( () => {
		if ( isOpen ) {
			setDraft( cloneConfirmation( initialConfirmationRef.current ) );
			setRedirectUrlError( '' );
		}
	}, [ isOpen ] );

	useEffect( () => {
		if ( ! isOpen ) {
			return undefined;
		}

		const previousOverflow = document.body.style.overflow;
		document.body.style.overflow = 'hidden';

		const handleEscape = ( event ) => {
			if ( 'Escape' === event.key ) {
				onClose();
			}
		};

		document.addEventListener( 'keydown', handleEscape );

		return () => {
			document.body.style.overflow = previousOverflow;
			document.removeEventListener( 'keydown', handleEscape );
		};
	}, [ isOpen, onClose ] );

	if ( ! isOpen || ! draft ) {
		return null;
	}

	const tagItems = getConfirmationTagItems( formFields );

	const updateDraft = ( key, value ) => {
		setDraft( ( current ) => ( {
			...current,
			[ key ]: value,
		} ) );

		if ( 'redirectUrl' === key ) {
			const trimmed = String( value || '' ).trim();
			if ( ! trimmed || isValidRedirectUrl( trimmed ) ) {
				setRedirectUrlError( '' );
			}
		}
	};

	const pageOptions = [
		{
			label: __( 'Select a page', 'gutena-forms' ),
			value: '0',
		},
		...pages.map( ( page ) => ( {
			label: page.title?.rendered || page.slug,
			value: String( page.id ),
		} ) ),
	];

	const handleSave = () => {
		const validation = validateConfirmationForSave( draft );
		if ( ! validation.valid ) {
			setRedirectUrlError( validation.message );
			return;
		}

		onSave( sanitizeConfirmation( draft, pages ) );
	};

	return (
		<div
			className="gutena-forms-form-confirmation-modal"
			role="dialog"
			aria-modal="true"
			aria-labelledby="gutena-forms-form-confirmation-modal-title"
		>
			<button
				type="button"
				className="gutena-forms-form-confirmation-modal__overlay"
				aria-label={ __( 'Close dialog', 'gutena-forms' ) }
				onClick={ onClose }
			/>

			<div className="gutena-forms-form-confirmation-modal__dialog">
				<div className="gutena-forms-form-confirmation-modal__header">
					<h2
						id="gutena-forms-form-confirmation-modal-title"
						className="gutena-forms-form-confirmation-modal__title"
					>
						{ __( 'Form Confirmation', 'gutena-forms' ) }
					</h2>
					<Button
						className="gutena-forms-form-confirmation-modal__close"
						icon={ close }
						label={ __( 'Close', 'gutena-forms' ) }
						onClick={ onClose }
					/>
				</div>

				<div className="gutena-forms-form-confirmation-modal__body">
					<div className="gutena-forms-form-confirmation-modal__section">
						<FormConfirmationRadioGroup
							id="gutena-form-confirmation-type"
							label={ __( 'Confirmation Type', 'gutena-forms' ) }
							value={ draft.confirmationType }
							options={ {
								message: __( 'Success Message', 'gutena-forms' ),
								redirect: __( 'Redirect', 'gutena-forms' ),
							} }
							onChange={ ( value ) =>
								updateDraft( 'confirmationType', value )
							}
						/>
					</div>

					{ 'message' === draft.confirmationType && (
						<>
							<div className="gutena-forms-form-confirmation-modal__section">
								<FormConfirmationMessageField
									id="gutena-form-confirmation-success-message"
									label={ __( 'Confirmation Message', 'gutena-forms' ) }
									value={ draft.successMessage }
									onChange={ ( value ) =>
										updateDraft( 'successMessage', value )
									}
									placeholder={ confirmationDefaults?.successMessage }
									formTagItems={ tagItems }
								/>
							</div>

							<div className="gutena-forms-form-confirmation-modal__section">
								<FormConfirmationMessageField
									id="gutena-form-confirmation-error-message"
									label={ __( 'Error Message', 'gutena-forms' ) }
									value={ draft.errorMessage }
									onChange={ ( value ) =>
										updateDraft( 'errorMessage', value )
									}
									placeholder={ confirmationDefaults?.errorMessage }
									formTagItems={ tagItems }
								/>
							</div>

							<div className="gutena-forms-form-confirmation-modal__section">
								<FormConfirmationRadioGroup
									id="gutena-form-confirmation-after-submit"
									label={ __( 'After Form Submission', 'gutena-forms' ) }
									value={ draft.afterSubmit || 'hide' }
									options={ {
										hide: __( 'Hide Form', 'gutena-forms' ),
										reset: __( 'Reset Form', 'gutena-forms' ),
									} }
									onChange={ ( value ) =>
										updateDraft( 'afterSubmit', value )
									}
								/>
							</div>
						</>
					) }

					{ 'redirect' === draft.confirmationType && (
						<>
							<div className="gutena-forms-form-confirmation-modal__section">
								<FormConfirmationRadioGroup
									id="gutena-form-confirmation-redirect-type"
									label={ __( 'Redirect to', 'gutena-forms' ) }
									value={ draft.redirectType }
									options={ {
										page: __( 'Page', 'gutena-forms' ),
										custom_url: __( 'Custom URL', 'gutena-forms' ),
									} }
									onChange={ ( value ) =>
										updateDraft( 'redirectType', value )
									}
								/>
							</div>

							{ 'page' === draft.redirectType && (
								<div className="gutena-forms-form-confirmation-modal__section">
									<SelectControl
										label={ __( 'Page', 'gutena-forms' ) }
										value={ String( draft.redirectPageId || 0 ) }
										options={ pageOptions }
										onChange={ ( value ) =>
											updateDraft(
												'redirectPageId',
												parseInt( value, 10 ) || 0
											)
										}
										__nextHasNoMarginBottom
									/>
								</div>
							) }

							{ 'custom_url' === draft.redirectType && (
								<div className="gutena-forms-form-confirmation-modal__section">
									<TextControl
										type="url"
										label={ __( 'Custom URL', 'gutena-forms' ) }
										value={ draft.redirectUrl }
										onChange={ ( value ) =>
											updateDraft( 'redirectUrl', value )
										}
										placeholder="https://example.com/thank-you"
										help={
											redirectUrlError ||
											__(
												'Enter a full URL using http:// or https://.',
												'gutena-forms'
											)
										}
										className={
											redirectUrlError
												? 'gutena-forms-form-confirmation-modal__url-error'
												: ''
										}
										__nextHasNoMarginBottom
									/>
								</div>
							) }
						</>
					) }
				</div>

				<div className="gutena-forms-form-confirmation-modal__footer">
					<Button
						variant="secondary"
						className="gutena-forms-form-confirmation-modal__cancel"
						onClick={ onClose }
					>
						{ __( 'Cancel', 'gutena-forms' ) }
					</Button>
					<Button
						variant="primary"
						className="gutena-forms-form-confirmation-modal__save"
						onClick={ handleSave }
					>
						{ __( 'Save', 'gutena-forms' ) }
					</Button>
				</div>
			</div>
		</div>
	);
};

export default FormConfirmationModal;
