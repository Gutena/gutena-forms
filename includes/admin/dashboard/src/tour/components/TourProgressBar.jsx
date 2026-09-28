/**
 * Tour progress bar (currentStep / 16).
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { TOUR_STEP_COUNT } from '../constants';

/**
 * @param {Object} props
 * @param {number} props.currentStep
 * @param {string} props.sectionColor
 */
const TourProgressBar = ( { currentStep, sectionColor } ) => {
	const fillPercent = ( currentStep / TOUR_STEP_COUNT ) * 100;

	return (
		<div
			className="gutena-forms-tour-progress"
			role="progressbar"
			aria-valuemin={ 0 }
			aria-valuemax={ TOUR_STEP_COUNT }
			aria-valuenow={ currentStep }
			style={ { '--gf-tour-accent': sectionColor } }
		>
			<div
				className="gutena-forms-tour-progress__fill"
				style={ { width: `${ fillPercent }%` } }
			/>
		</div>
	);
};

export default TourProgressBar;
