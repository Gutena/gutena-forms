/**
 * Sixteen-step dot progress indicators.
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
const TourStepDots = ( { currentStep, sectionColor } ) => {
	return (
		<div
			className="gutena-forms-tour-dots"
			role="group"
			aria-label="Tour progress steps"
		>
			{ Array.from( { length: TOUR_STEP_COUNT } ).map( ( _, index ) => {
				let stateClass = 'gutena-forms-tour-dots__dot--future';

				if ( index === currentStep ) {
					stateClass = 'gutena-forms-tour-dots__dot--current';
				} else if ( index < currentStep ) {
					stateClass = 'gutena-forms-tour-dots__dot--past';
				}

				return (
					<span
						key={ index }
						className={ `gutena-forms-tour-dots__dot ${ stateClass }` }
						style={ { '--gf-tour-accent': sectionColor } }
						aria-hidden="true"
					/>
				);
			} ) }
		</div>
	);
};

export default TourStepDots;
