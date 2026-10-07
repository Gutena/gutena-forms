/**
 * Centralized product tour state provider.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import {
	useCallback,
	useEffect,
	useMemo,
	useReducer,
	useRef,
} from '@wordpress/element';
import { TourContext } from './context/TourContext';
import { getTourStep, isValidTourStep } from './config/steps';
import {
	TOUR_FIRST_STEP,
	TOUR_LAST_STEP,
} from './constants';
import { useTourTarget } from './hooks/useTourTarget';
import {
	createInitialTourState,
	TOUR_ACTIONS,
	tourReducer,
} from './reducer';
import {
	gutenaFormsFetchTourPreferences,
	gutenaFormsSaveTourPreferences,
} from './services/tourApi';
import { stateToPersistence } from './services/tourPersistence';
import {
	getDashboardTourUrlForView,
	getEditorTourUrl,
	getTourStepFromQuery,
	navigateDashboardView,
	requiresRuntimeHandoff,
} from './utils/navigation';
import { resolveResumeTourStep } from './utils/resolveResumeTourStep';
import {
	clearTourHandoffStep,
	setTourHandoffStep,
} from './utils/tourHandoff';
import { shouldNavigateForStep } from './utils/shouldNavigateForStep';
import { saveEditorPostBeforeNavigation } from './utils/saveEditorPost';
import { updateEditorTourQueryParam } from './utils/updateEditorTourQuery';
import {
	shouldAutoLaunchContextualStep,
	shouldAutoLaunchTour,
} from './utils/shouldAutoLaunchTour';
import DashboardContextualTourTriggers from './components/DashboardContextualTourTriggers';
import EditorContextualTourTriggers from './components/EditorContextualTourTriggers';
import TourEngine from './TourEngine';

/**
 * @param {Object} props
 * @param {import('react').ReactNode} props.children
 * @param {'dashboard'|'editor'} [props.runtime]
 * @param {Function|null} [props.navigate] react-router navigate (dashboard only).
 * @param {Function|null} [props.setActiveMenu]
 * @param {Object} [props.initialPreferences]
 * @param {string} [props.adminURL]
 * @param {string} [props.dashboardURL]
 */
const TourProvider = ( {
	children,
	runtime = 'dashboard',
	navigate = null,
	setActiveMenu = null,
	initialPreferences = null,
	adminURL = '',
	dashboardURL = '',
} ) => {
	const [ state, dispatch ] = useReducer(
		tourReducer,
		createInitialTourState( initialPreferences )
	);
	const persistQueueRef = useRef( Promise.resolve() );
	const hasAutoResumedRef = useRef( false );
	const hasFreshPrefsFetchedRef = useRef( false );
	const stateRef = useRef( state );

	stateRef.current = state;

	const getTourState = useCallback( () => stateRef.current, [] );

	const currentStepConfig = useMemo(
		() => getTourStep( state.currentStep ),
		[ state.currentStep ]
	);

	const currentTargetView = currentStepConfig?.targetView || null;
	const preferredTooltipPosition = currentStepConfig?.placement || 'bottom';

	const persistState = useCallback( ( nextState ) => {
		persistQueueRef.current = persistQueueRef.current
			.catch( () => undefined )
			.then( () => gutenaFormsSaveTourPreferences( stateToPersistence( nextState ) ) );

		return persistQueueRef.current;
	}, [] );

	const navigateToStep = useCallback(
		async ( stepIndex, stateSnapshot = state ) => {
			const stepConfig = getTourStep( stepIndex );

			if ( ! stepConfig || ! shouldNavigateForStep( stepConfig, runtime ) ) {
				return;
			}

			if (
				runtime === 'editor' &&
				stepConfig.editorRemountOnEnter &&
				stepConfig.targetView === 'editor'
			) {
				const currentQueryStep = getTourStepFromQuery();

				if ( currentQueryStep === stepIndex ) {
					return;
				}

				updateEditorTourQueryParam( stepIndex );
				return;
			}

			if ( requiresRuntimeHandoff( runtime, stepConfig.targetView ) ) {
				if ( runtime === 'editor' ) {
					await saveEditorPostBeforeNavigation();
				}

				try {
					await gutenaFormsSaveTourPreferences(
						stateToPersistence( stateSnapshot )
					);
				} catch {
					// Continue navigation when persistence fails.
				}

				setTourHandoffStep( stepIndex );

				window.location.assign(
					stepConfig.targetView === 'editor'
						? getEditorTourUrl( adminURL, stepIndex )
						: getDashboardTourUrlForView(
							dashboardURL,
							stepConfig.targetView,
							stepIndex
						)
				);
				return;
			}

			if ( runtime === 'dashboard' && navigate ) {
				navigateDashboardView(
					navigate,
					setActiveMenu,
					stepConfig.targetView
				);
			}
		},
		[ adminURL, dashboardURL, navigate, runtime, setActiveMenu, state ]
	);

	const markStepSeen = useCallback(
		( step ) => {
			if ( ! isValidTourStep( step ) ) {
				return Promise.resolve();
			}

			const nextState = tourReducer( stateRef.current, {
				type: TOUR_ACTIONS.MARK_STEP_SEEN,
				step,
			} );

			dispatch( { type: TOUR_ACTIONS.MARK_STEP_SEEN, step } );

			return persistState( nextState );
		},
		[ persistState ]
	);

	const setDoNotShowAgain = useCallback( ( value ) => {
		dispatch( { type: TOUR_ACTIONS.SET_DO_NOT_SHOW_AGAIN, value } );
	}, [] );

	const startTourAt = useCallback(
		async ( step ) => {
			if ( ! isValidTourStep( step ) || stateRef.current.doNotShowAgain ) {
				return false;
			}

			const nextState = tourReducer( stateRef.current, {
				type: TOUR_ACTIONS.START_TOUR,
				step,
			} );

			dispatch( { type: TOUR_ACTIONS.START_TOUR, step } );
			await persistState( nextState );
			await navigateToStep( step, nextState );

			return true;
		},
		[ navigateToStep, persistState ]
	);

	const resumeContextualTourAt = useCallback(
		async ( step, pathname = '' ) => {
			if (
				! shouldAutoLaunchContextualStep(
					stateRef.current,
					step,
					pathname
				)
			) {
				return false;
			}

			const nextState = tourReducer( stateRef.current, {
				type: TOUR_ACTIONS.START_TOUR,
				step,
			} );

			dispatch( { type: TOUR_ACTIONS.START_TOUR, step } );
			await persistState( nextState );
			await navigateToStep( step, nextState );

			return true;
		},
		[ navigateToStep, persistState ]
	);

	const startTour = useCallback( () => {
		startTourAt( TOUR_FIRST_STEP );
	}, [ startTourAt ] );

	const goToStep = useCallback(
		async ( step ) => {
			if ( ! isValidTourStep( step ) ) {
				return;
			}

			const currentStep = stateRef.current.currentStep;

			let nextState = tourReducer( stateRef.current, {
				type: TOUR_ACTIONS.MARK_STEP_SEEN,
				step: currentStep,
			} );

			nextState = tourReducer( nextState, {
				type: TOUR_ACTIONS.SET_STEP,
				step,
			} );

			dispatch( {
				type: TOUR_ACTIONS.MARK_STEP_SEEN,
				step: currentStep,
			} );
			dispatch( { type: TOUR_ACTIONS.SET_STEP, step } );

			try {
				await persistState( nextState );
			} catch {
				// Continue navigation when persistence fails.
			}

			await navigateToStep( step, nextState );
		},
		[ navigateToStep, persistState ]
	);

	const nextStep = useCallback( () => {
		if ( state.currentStep >= TOUR_LAST_STEP ) {
			return;
		}

		goToStep( state.currentStep + 1 );
	}, [ goToStep, state.currentStep ] );

	const previousStep = useCallback( () => {
		if ( state.currentStep <= TOUR_FIRST_STEP ) {
			return;
		}

		goToStep( state.currentStep - 1 );
	}, [ goToStep, state.currentStep ] );

	const closeTour = useCallback( () => {
		dispatch( { type: TOUR_ACTIONS.CLOSE_TOUR } );
	}, [] );

	const skipTour = useCallback( () => {
		const nextState = tourReducer( stateRef.current, {
			type: TOUR_ACTIONS.SKIP_TOUR,
		} );

		dispatch( { type: TOUR_ACTIONS.SKIP_TOUR } );
		persistState( nextState );
	}, [ persistState ] );

	const completeTour = useCallback( () => {
		const nextState = tourReducer( stateRef.current, {
			type: TOUR_ACTIONS.COMPLETE_TOUR,
		} );

		dispatch( { type: TOUR_ACTIONS.COMPLETE_TOUR } );
		persistState( nextState );
	}, [ persistState ] );

	useEffect( () => {
		let isMounted = true;

		if ( initialPreferences ) {
			dispatch( {
				type: TOUR_ACTIONS.HYDRATE,
				preferences: initialPreferences,
			} );
			return undefined;
		}

		gutenaFormsFetchTourPreferences()
			.then( ( preferences ) => {
				if ( ! isMounted ) {
					return;
				}

				dispatch( {
					type: TOUR_ACTIONS.HYDRATE,
					preferences,
				} );
			} )
			.catch( () => {
				if ( ! isMounted ) {
					return;
				}

				dispatch( { type: TOUR_ACTIONS.HYDRATE, preferences: {} } );
			} );

		return () => {
			isMounted = false;
		};
	}, [ initialPreferences ] );

	useEffect( () => {
		if ( ! state.isHydrated || hasAutoResumedRef.current ) {
			return;
		}

		const resumeStep = resolveResumeTourStep( state );
		const canResume = shouldAutoLaunchTour( {
			context: 'query_resume',
			state,
			options: { step: resumeStep },
		} );

		if ( ! canResume ) {
			return;
		}

		hasAutoResumedRef.current = true;
		clearTourHandoffStep();

		const resumeState = tourReducer( state, {
			type: TOUR_ACTIONS.START_TOUR,
			step: resumeStep,
		} );

		dispatch( { type: TOUR_ACTIONS.START_TOUR, step: resumeStep } );
		navigateToStep( resumeStep, resumeState );
	}, [
		navigateToStep,
		state.isHydrated,
		state.tourStatus,
		state.currentStep,
		state.doNotShowAgain,
		state.isOpen,
	] );

	useEffect( () => {
		if (
			runtime !== 'editor' ||
			! state.isHydrated ||
			hasFreshPrefsFetchedRef.current
		) {
			return undefined;
		}

		hasFreshPrefsFetchedRef.current = true;
		let cancelled = false;

		gutenaFormsFetchTourPreferences()
			.then( ( preferences ) => {
				if ( cancelled ) {
					return;
				}

				dispatch( {
					type: TOUR_ACTIONS.HYDRATE,
					preferences,
				} );
			} )
			.catch( () => {
				// Keep localized preferences when the REST request fails.
			} );

		return () => {
			cancelled = true;
		};
	}, [ runtime, state.isHydrated ] );

	useTourTarget( {
		isOpen: state.isOpen,
		currentStep: state.currentStep,
		dispatch,
	} );

	const canGoBack = state.currentStep > TOUR_FIRST_STEP;
	const canGoNext = state.currentStep < TOUR_LAST_STEP;
	const canSkip = state.currentStep < TOUR_LAST_STEP;

	const contextValue = useMemo(
		() => ( {
			...state,
			runtime,
			currentStepConfig,
			currentTargetView,
			preferredTooltipPosition,
			canGoBack,
			canGoNext,
			canSkip,
			startTour,
			startTourAt,
			resumeContextualTourAt,
			nextStep,
			previousStep,
			skipTour,
			completeTour,
			closeTour,
			markStepSeen,
			setDoNotShowAgain,
		} ),
		[
			canGoBack,
			canGoNext,
			canSkip,
			closeTour,
			completeTour,
			currentStepConfig,
			currentTargetView,
			markStepSeen,
			nextStep,
			preferredTooltipPosition,
			previousStep,
			runtime,
			setDoNotShowAgain,
			skipTour,
			resumeContextualTourAt,
			startTour,
			startTourAt,
			state,
		]
	);

	return (
		<TourContext.Provider value={ contextValue }>
			{ children }
			{ runtime === 'dashboard' && (
				<DashboardContextualTourTriggers
					state={ state }
					resumeContextualTourAt={ resumeContextualTourAt }
					markStepSeen={ markStepSeen }
					getTourState={ getTourState }
				/>
			) }
			{ runtime === 'editor' && (
				<EditorContextualTourTriggers
					state={ state }
					resumeContextualTourAt={ resumeContextualTourAt }
				/>
			) }
			<TourEngine />
		</TourContext.Provider>
	);
};

export default TourProvider;
