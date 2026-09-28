/**
 * Centered modal for intro (step 0) and done (step 15).
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { useEffect, useRef } from '@wordpress/element';
import { CheckboxControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * @param {Object} props
 * @param {Object} props.stepConfig
 * @param {boolean} props.isIntro
 * @param {boolean} props.isDone
 * @param {boolean} props.doNotShowAgain
 * @param {Function} props.onDoNotShowAgainChange
 * @param {Function} props.onPrimaryAction
 * @param {string} props.primaryLabel
 */
const TourCenteredModal = ( {
	stepConfig,
	isIntro,
	isDone,
	doNotShowAgain,
	onDoNotShowAgainChange,
	onPrimaryAction,
	primaryLabel,
} ) => {
	const primaryButtonRef = useRef( null );

	useEffect( () => {
		if ( primaryButtonRef.current ) {
			primaryButtonRef.current.focus( { preventScroll: true } );
		}
	}, [ isIntro, isDone ] );

	return (
		<div
			className="gutena-forms-tour-modal"
			role="dialog"
			aria-modal="true"
			aria-labelledby="gutena-forms-tour-modal-title"
			aria-describedby="gutena-forms-tour-modal-body"
			style={ { '--gf-tour-accent': stepConfig.sectionColor } }
		>
			<div className="gutena-forms-tour-modal__card">
				{ isIntro && (
					<div className="gutena-forms-tour-modal__badge" aria-hidden="true">
						<span className="gutena-forms-tour-modal__badge-icon">GF</span>
					</div>
				) }

				{ isDone && (
					<div
						className="gutena-forms-tour-modal__celebration"
						aria-hidden="true"
					>
						🎉
					</div>
				) }

				<h2
					id="gutena-forms-tour-modal-title"
					className="gutena-forms-tour-modal__title"
				>
					{ stepConfig.title }
				</h2>

				<p
					id="gutena-forms-tour-modal-body"
					className="gutena-forms-tour-modal__body"
				>
					{ stepConfig.description }
				</p>

				{ isIntro && (
					<div className="gutena-forms-tour-modal__checkbox">
						<CheckboxControl
							label={ __( 'Do not show me this again', 'gutena-forms' ) }
							checked={ doNotShowAgain }
							onChange={ onDoNotShowAgainChange }
						/>
					</div>
				) }

				<button
					ref={ primaryButtonRef }
					type="button"
					className="gutena-forms-tour-modal__primary"
					onClick={ onPrimaryAction }
					aria-label={ primaryLabel }
				>
					{ primaryLabel }
					{ isIntro && (
						<span className="gutena-forms-tour-modal__primary-icon" aria-hidden="true">
							→
						</span>
					) }
					{ isDone && (
						<span className="gutena-forms-tour-modal__primary-icon" aria-hidden="true">
							✓
						</span>
					) }
				</button>
			</div>
		</div>
	);
};

export default TourCenteredModal;
