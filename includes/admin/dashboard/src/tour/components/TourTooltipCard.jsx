/**
 * Spotlight-step tooltip card (steps 1–14).
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { TOUR_STEP_COUNT } from '../constants';
import { getSectionLabel } from '../utils/getSectionLabel';
import { useTooltipPosition } from '../hooks/useTooltipPosition';
import TourProgressBar from './TourProgressBar';
import TourStepDots from './TourStepDots';

/**
 * @param {Object} props
 * @param {Object} props.stepConfig
 * @param {number} props.currentStep
 * @param {Object|null} props.targetRect
 * @param {string} props.preferredPlacement
 * @param {boolean} props.isTargetWaiting
 * @param {boolean} props.canGoBack
 * @param {boolean} props.canGoNext
 * @param {Function} props.onBack
 * @param {Function} props.onNext
 * @param {Function} props.onSkip
 */
const TourTooltipCard = ( {
	stepConfig,
	currentStep,
	targetRect,
	preferredPlacement,
	isTargetWaiting,
	canGoBack,
	canGoNext,
	onBack,
	onNext,
	onSkip,
} ) => {
	const tooltipRef = useRef( null );
	const backButtonRef = useRef( null );

	const useCenteredFallback = isTargetWaiting || ! targetRect;

	const position = useTooltipPosition( {
		targetRect,
		preferredPlacement,
		tooltipRef,
		isActive: Boolean( targetRect ) && ! isTargetWaiting,
	} );

	useEffect( () => {
		if ( backButtonRef.current ) {
			backButtonRef.current.focus( { preventScroll: true } );
		}
	}, [ currentStep ] );

	const placement = useCenteredFallback
		? 'center'
		: position?.placement || preferredPlacement;
	const isVisible = useCenteredFallback || Boolean( position );

	return (
		<div
			ref={ tooltipRef }
			className={ `gutena-forms-tour-tooltip${
				isVisible ? ' is-visible' : ''
			}${ useCenteredFallback ? ' gutena-forms-tour-tooltip--centered-fallback' : '' }` }
			style={ {
				'--gf-tour-accent': stepConfig.sectionColor,
				...( useCenteredFallback
					? {}
					: position
						? {
							top: `${ position.top }px`,
							left: `${ position.left }px`,
						}
						: {
							top: '-9999px',
							left: '-9999px',
						} ),
			} }
			data-placement={ placement }
			role="dialog"
			aria-modal="false"
			aria-labelledby="gutena-forms-tour-tooltip-title"
			aria-describedby="gutena-forms-tour-tooltip-body"
		>
			<div className="gutena-forms-tour-tooltip__arrow" aria-hidden="true" />

			<p className="gutena-forms-tour-tooltip__section">
				{ getSectionLabel( stepConfig.section ) }
			</p>

			<h2
				id="gutena-forms-tour-tooltip-title"
				className="gutena-forms-tour-tooltip__title"
			>
				{ stepConfig.title }
			</h2>

			<p
				id="gutena-forms-tour-tooltip-body"
				className="gutena-forms-tour-tooltip__body"
			>
				{ stepConfig.description }
			</p>

			<TourProgressBar
				currentStep={ currentStep }
				sectionColor={ stepConfig.sectionColor }
			/>

			<TourStepDots
				currentStep={ currentStep }
				sectionColor={ stepConfig.sectionColor }
			/>

			<div className="gutena-forms-tour-tooltip__footer">
				<span className="gutena-forms-tour-tooltip__counter">
					{ currentStep } / { TOUR_STEP_COUNT }
				</span>

				<div className="gutena-forms-tour-tooltip__nav">
					<button
						ref={ backButtonRef }
						type="button"
						className="gutena-forms-tour-tooltip__button gutena-forms-tour-tooltip__button--secondary"
						onClick={ onBack }
						disabled={ ! canGoBack }
						aria-label={ __( 'Go to previous tour step', 'gutena-forms' ) }
					>
						{ __( 'Back', 'gutena-forms' ) }
					</button>

					<button
						type="button"
						className="gutena-forms-tour-tooltip__button gutena-forms-tour-tooltip__button--primary"
						onClick={ onNext }
						disabled={ ! canGoNext }
						aria-label={ __( 'Go to next tour step', 'gutena-forms' ) }
					>
						{ __( 'Next', 'gutena-forms' ) }
					</button>
				</div>

				<button
					type="button"
					className="gutena-forms-tour-tooltip__skip"
					onClick={ onSkip }
					aria-label={ __( 'Skip the product tour', 'gutena-forms' ) }
				>
					{ __( 'Skip tour', 'gutena-forms' ) }
				</button>
			</div>
		</div>
	);
};

export default TourTooltipCard;
