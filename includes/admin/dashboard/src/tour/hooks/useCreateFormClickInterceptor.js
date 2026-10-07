/**
 * Trigger B precedence — prepare Create Form navigation for Step 7 resume.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { useEffect } from '@wordpress/element';
import { CONTEXTUAL_TRIGGER } from '../constants/contextualTriggers';
import {
	isCreateFormHref,
	setPendingContextualStep,
	suppressTriggerA,
} from '../utils/contextualTriggerEligibility';
import { shouldAutoLaunchContextualStep } from '../utils/shouldAutoLaunchTour';
import {
	beginContextualLaunch,
	endContextualLaunch,
} from '../utils/contextualLaunchCoordinator';

/**
 * @param {Object} params
 * @param {'dashboard'|'editor'} params.runtime
 * @param {Object} params.state
 * @param {Function} params.markStepSeen
 */
export function useCreateFormClickInterceptor( { runtime, state, markStepSeen } ) {
	useEffect( () => {
		if ( runtime !== 'dashboard' || ! state.isHydrated ) {
			return undefined;
		}

		const handleClick = async ( event ) => {
			if ( ! shouldAutoLaunchContextualStep( state, 6 ) ) {
				return;
			}

			const link = event.target.closest( 'a[href*="post_type=gutena_forms"]' );

			if ( ! link ) {
				return;
			}

			const href = link.getAttribute( 'href' );

			if ( ! isCreateFormHref( href ) ) {
				return;
			}

			if (
				! beginContextualLaunch(
					CONTEXTUAL_TRIGGER.CREATE_FORM_STEP_6,
					'create-form-click'
				)
			) {
				return;
			}

			event.preventDefault();

			try {
				if ( ! state.stepsSeen[ 5 ] ) {
					await markStepSeen( 5 );
				}

				suppressTriggerA();
				setPendingContextualStep( 6 );
				window.location.assign( href );
			} finally {
				endContextualLaunch();
			}
		};

		document.addEventListener( 'click', handleClick, true );

		return () => {
			document.removeEventListener( 'click', handleClick, true );
		};
	}, [
		markStepSeen,
		runtime,
		state,
		state.isHydrated,
		state.stepsSeen,
		state.tourStatus,
		state.doNotShowAgain,
		state.isOpen,
	] );
}
