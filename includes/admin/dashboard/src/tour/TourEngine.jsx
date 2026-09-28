/**
 * Product tour visual shell (spotlight, tooltip, modals).
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { createPortal, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useTour } from './context/TourContext';
import { stepMatchesRuntime } from './utils/stepMatchesRuntime';
import { useTargetRect } from './hooks/useTargetRect';
import TourCenteredModal from './components/TourCenteredModal';
import TourSpotlight from './components/TourSpotlight';
import TourTooltipCard from './components/TourTooltipCard';

const TourEngine = () => {
	const {
		isOpen,
		runtime,
		currentStep,
		currentStepConfig,
		currentTarget,
		preferredTooltipPosition,
		isTargetWaiting,
		canGoBack,
		canGoNext,
		doNotShowAgainSelected,
		nextStep,
		previousStep,
		skipTour,
		completeTour,
		closeTour,
		setDoNotShowAgain,
	} = useTour();

	const isCenteredStep =
		currentStepConfig?.isIntro || currentStepConfig?.isDone;
	const isSpotlightStep =
		currentStepConfig?.useSpotlight && ! isCenteredStep;

	const targetRect = useTargetRect(
		currentTarget,
		isOpen && isSpotlightStep
	);

	useEffect( () => {
		if ( ! isOpen ) {
			return undefined;
		}

		const handleKeyDown = ( event ) => {
			if ( event.key === 'Escape' ) {
				event.preventDefault();
				closeTour();
			}
		};

		document.addEventListener( 'keydown', handleKeyDown );

		return () => {
			document.removeEventListener( 'keydown', handleKeyDown );
		};
	}, [ closeTour, isOpen ] );

	useEffect( () => {
		if ( ! isOpen ) {
			return undefined;
		}

		const previousOverflow = document.body.style.overflow;
		document.body.style.overflow = 'hidden';

		return () => {
			document.body.style.overflow = previousOverflow;
		};
	}, [ isOpen ] );

	if ( ! isOpen || ! currentStepConfig ) {
		return null;
	}

	const matchesRuntime = stepMatchesRuntime( currentStepConfig, runtime );

	if ( ! matchesRuntime ) {
		return null;
	}

	const tourMarkup = (
		<div
			className={ `gutena-forms-tour${
				isCenteredStep
					? ' gutena-forms-tour--centered'
					: ' gutena-forms-tour--spotlight'
			}` }
			data-gf-tour-engine=""
			data-step={ currentStep }
			style={ { '--gf-tour-accent': currentStepConfig.sectionColor } }
			aria-live="polite"
		>
			{ isCenteredStep && (
				<div
					className="gutena-forms-tour__overlay gutena-forms-tour__overlay--opaque"
					aria-hidden="true"
				/>
			) }

			{ isSpotlightStep && (
				<TourSpotlight
					rect={ targetRect }
					sectionColor={ currentStepConfig.sectionColor }
					isWaiting={ isTargetWaiting || ! targetRect }
				/>
			) }

			<div className="gutena-forms-tour__layer">
				{ currentStepConfig.isIntro && (
					<TourCenteredModal
						stepConfig={ currentStepConfig }
						isIntro={ true }
						isDone={ false }
						doNotShowAgain={ doNotShowAgainSelected }
						onDoNotShowAgainChange={ setDoNotShowAgain }
						onPrimaryAction={ nextStep }
						primaryLabel={ __( 'Start Tour', 'gutena-forms' ) }
					/>
				) }

				{ currentStepConfig.isDone && (
					<TourCenteredModal
						stepConfig={ currentStepConfig }
						isIntro={ false }
						isDone={ true }
						doNotShowAgain={ doNotShowAgainSelected }
						onDoNotShowAgainChange={ setDoNotShowAgain }
						onPrimaryAction={ completeTour }
						primaryLabel={ __( 'Done', 'gutena-forms' ) }
					/>
				) }

				{ isSpotlightStep && (
					<TourTooltipCard
						stepConfig={ currentStepConfig }
						currentStep={ currentStep }
						targetRect={ targetRect }
						preferredPlacement={ preferredTooltipPosition }
						isTargetWaiting={ isTargetWaiting || ! targetRect }
						canGoBack={ canGoBack }
						canGoNext={ canGoNext }
						onBack={ previousStep }
						onNext={ nextStep }
						onSkip={ skipTour }
					/>
				) }
			</div>
		</div>
	);

	return createPortal( tourMarkup, document.body );
};

export default TourEngine;
