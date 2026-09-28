/**
 * Dashboard-only contextual tour trigger host (requires React Router).
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { useContextualTourTriggers } from '../hooks/useContextualTourTriggers';

/**
 * @param {Object} props
 * @param {Object} props.state
 * @param {Function} props.resumeContextualTourAt
 * @param {Function} props.markStepSeen
 * @param {Function} props.getTourState
 */
const DashboardContextualTourTriggers = ( {
	state,
	resumeContextualTourAt,
	markStepSeen,
	getTourState,
} ) => {
	useContextualTourTriggers( {
		runtime: 'dashboard',
		state,
		resumeContextualTourAt,
		markStepSeen,
		getTourState,
	} );

	return null;
};

export default DashboardContextualTourTriggers;
