import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import { createPortal, createInterpolateElement } from '@wordpress/element';
import { Button } from '@wordpress/components';

const InfoIcon = () => (
	<svg
		className="gutena-forms__square-test-modal__alert-icon"
		width="16"
		height="16"
		viewBox="0 0 16 16"
		fill="none"
		xmlns="http://www.w3.org/2000/svg"
		aria-hidden="true"
	>
		<circle cx="8" cy="8" r="7.25" stroke="currentColor" strokeWidth="1.5" />
		<path d="M8 7V11" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
		<circle cx="8" cy="5" r="0.75" fill="currentColor" />
	</svg>
);

const SquareTestModeModal = ( { isOpen, onClose, onContinue } ) => {
	const [ isVisible, setIsVisible ] = useState( false );
	const [ isAnimating, setIsAnimating ] = useState( false );

	useEffect( () => {
		if ( isOpen ) {
			setIsVisible( true );
			document.body.style.overflow = 'hidden';

			const timer = window.setTimeout( () => {
				setIsAnimating( true );
			}, 10 );

			return () => {
				window.clearTimeout( timer );
			};
		}

		setIsAnimating( false );
		document.body.style.overflow = '';

		const timer = window.setTimeout( () => {
			setIsVisible( false );
		}, 250 );

		return () => {
			window.clearTimeout( timer );
			document.body.style.overflow = '';
		};
	}, [ isOpen ] );

	useEffect( () => {
		if ( ! isOpen || ! isVisible ) {
			return undefined;
		}

		const handleKeyDown = ( event ) => {
			if ( 'Escape' === event.key ) {
				onClose();
			}
		};

		document.addEventListener( 'keydown', handleKeyDown );

		return () => {
			document.removeEventListener( 'keydown', handleKeyDown );
		};
	}, [ isOpen, isVisible, onClose ] );

	if ( ! isVisible ) {
		return null;
	}

	const handleBackdropClick = ( event ) => {
		if ( event.target === event.currentTarget ) {
			onClose();
		}
	};

	return createPortal(
		<div
			className={ `gutena-forms__square-test-modal-overlay${ isAnimating ? ' is-visible' : '' }` }
			onClick={ handleBackdropClick }
			role="presentation"
		>
			<div
				className="gutena-forms__square-test-modal"
				role="dialog"
				aria-modal="true"
				aria-labelledby="gutena-forms-square-test-modal-title"
			>
				<button
					type="button"
					className="gutena-forms__square-test-modal__close"
					onClick={ onClose }
					aria-label={ __( 'Close', 'gutena-forms' ) }
				>
					<svg width="18" height="18" viewBox="0 0 18 18" fill="none" aria-hidden="true">
						<path d="M4.5 4.5L13.5 13.5" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
						<path d="M13.5 4.5L4.5 13.5" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
					</svg>
				</button>

				<h2
					id="gutena-forms-square-test-modal-title"
					className="gutena-forms__square-test-modal__title"
				>
					{ __( 'Setting up Square for Test payments', 'gutena-forms' ) }
				</h2>

				<div className="gutena-forms__square-test-modal__alert">
					<InfoIcon />
					<p>
						{ __( 'Important! If you skip these initial steps, you will get stuck on a white screen.', 'gutena-forms' ) }
					</p>
				</div>

				<ol className="gutena-forms__square-test-modal__steps">
					<li>
						{ createInterpolateElement(
							__( 'Click <link>here</link> to create a Square account if you do not already have one.', 'gutena-forms' ),
							{
								link: (
									<a
										href="https://app.squareup.com/signup"
										target="_blank"
										rel="noopener noreferrer"
									/>
								),
							}
						) }
					</li>
					<li>
						{ createInterpolateElement(
							__( 'Click <link>here</link> and create a Square sandbox test account.', 'gutena-forms' ),
							{
								link: (
									<a
										href="https://developer.squareup.com/console/en/sandbox-test-accounts"
										target="_blank"
										rel="noopener noreferrer"
									/>
								),
							}
						) }
					</li>
					<li>
						{ __( 'Click "Square Dashboard" for the new sandbox test account. Leave the tab open and return to this page.', 'gutena-forms' ) }
					</li>
					<li>
						{ createInterpolateElement(
							__( 'Click <link>here</link>. You will be taken to Square to allow the required permissions for handling payments.', 'gutena-forms' ),
							{
								link: (
									<button
										type="button"
										className="gutena-forms__square-test-modal__inline-action"
										onClick={ onContinue }
									/>
								),
							}
						) }
					</li>
				</ol>

				<div className="gutena-forms__square-test-modal__actions">
					<Button
						variant="secondary"
						className="gutena-forms__square-test-modal__cancel-btn"
						onClick={ onClose }
					>
						{ __( 'Cancel', 'gutena-forms' ) }
					</Button>
					<Button
						variant="primary"
						className="gutena-forms__square-test-modal__continue-btn"
						onClick={ onContinue }
					>
						{ __( 'Continue', 'gutena-forms' ) }
					</Button>
				</div>
			</div>
		</div>,
		document.body
	);
};

export default SquareTestModeModal;
