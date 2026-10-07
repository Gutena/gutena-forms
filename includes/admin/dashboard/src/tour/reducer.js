/**
 * Product tour reducer.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { TOUR_LAST_STEP, TOUR_STATUS } from './constants';
import { createInitialStepsSeen } from './utils/createInitialStepsSeen';

export const TOUR_ACTIONS = {
	HYDRATE: 'HYDRATE',
	START_TOUR: 'START_TOUR',
	SET_STEP: 'SET_STEP',
	MARK_STEP_SEEN: 'MARK_STEP_SEEN',
	SET_DO_NOT_SHOW_AGAIN: 'SET_DO_NOT_SHOW_AGAIN',
	CLOSE_TOUR: 'CLOSE_TOUR',
	SKIP_TOUR: 'SKIP_TOUR',
	COMPLETE_TOUR: 'COMPLETE_TOUR',
	SET_TARGET_WAITING: 'SET_TARGET_WAITING',
	SET_CURRENT_TARGET: 'SET_CURRENT_TARGET',
};

/**
 * @param {Object} [preferences]
 * @returns {Object}
 */
export function createInitialTourState( preferences ) {
	const stepsSeen = createInitialStepsSeen( preferences?.steps );

	return {
		isOpen: false,
		currentStep: Number.isInteger( preferences?.current_step )
			? preferences.current_step
			: 0,
		tourStatus: preferences?.tour_status || TOUR_STATUS.NOT_STARTED,
		stepsSeen,
		doNotShowAgain: Boolean( preferences?.do_not_show_again ),
		doNotShowAgainSelected: Boolean( preferences?.do_not_show_again ),
		currentTarget: null,
		isTargetWaiting: false,
		isHydrated: false,
	};
}

/**
 * @param {Object} state
 * @param {Object} action
 * @returns {Object}
 */
export function tourReducer( state, action ) {
	switch ( action.type ) {
		case TOUR_ACTIONS.HYDRATE: {
			const hydrated = createInitialTourState( action.preferences );

			// Never close an in-progress tour when syncing persisted preferences.
			if ( state.isOpen ) {
				return {
					...state,
					tourStatus: hydrated.tourStatus,
					stepsSeen: hydrated.stepsSeen,
					doNotShowAgain: hydrated.doNotShowAgain,
					isHydrated: true,
				};
			}

			return {
				...state,
				...hydrated,
				isHydrated: true,
			};
		}

		case TOUR_ACTIONS.START_TOUR:
			return {
				...state,
				isOpen: true,
				currentStep: action.step,
				tourStatus: TOUR_STATUS.ACTIVE,
				currentTarget: null,
				isTargetWaiting: true,
			};

		case TOUR_ACTIONS.SET_STEP:
			return {
				...state,
				currentStep: action.step,
				tourStatus: state.isOpen ? TOUR_STATUS.ACTIVE : state.tourStatus,
				currentTarget: null,
				isTargetWaiting: true,
			};

		case TOUR_ACTIONS.MARK_STEP_SEEN:
			return {
				...state,
				stepsSeen: {
					...state.stepsSeen,
					[ action.step ]: true,
				},
			};

		case TOUR_ACTIONS.SET_DO_NOT_SHOW_AGAIN:
			return {
				...state,
				doNotShowAgainSelected: Boolean( action.value ),
			};

		case TOUR_ACTIONS.CLOSE_TOUR:
			return {
				...state,
				isOpen: false,
				currentTarget: null,
				isTargetWaiting: false,
			};

		case TOUR_ACTIONS.SKIP_TOUR: {
			const nextState = {
				...state,
				isOpen: false,
				tourStatus: TOUR_STATUS.SKIPPED,
				currentTarget: null,
				isTargetWaiting: false,
			};

			if ( state.doNotShowAgainSelected ) {
				nextState.doNotShowAgain = true;
			}

			return nextState;
		}

		case TOUR_ACTIONS.COMPLETE_TOUR: {
			const nextState = {
				...state,
				isOpen: false,
				tourStatus: TOUR_STATUS.COMPLETED,
				currentTarget: null,
				isTargetWaiting: false,
				stepsSeen: {
					...state.stepsSeen,
					[ TOUR_LAST_STEP ]: true,
				},
			};

			if ( state.doNotShowAgainSelected ) {
				nextState.doNotShowAgain = true;
			}

			return nextState;
		}

		case TOUR_ACTIONS.SET_TARGET_WAITING:
			return {
				...state,
				isTargetWaiting: Boolean( action.value ),
			};

		case TOUR_ACTIONS.SET_CURRENT_TARGET:
			return {
				...state,
				currentTarget: action.target,
				isTargetWaiting: false,
			};

		default:
			return state;
	}
}
