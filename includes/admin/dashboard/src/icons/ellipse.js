import { Icon } from '@wordpress/components';
import { addFilter } from '@wordpress/hooks';

const Ellipse = ( { fill } ) => (
	<Icon
		icon={ () => (
			<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
				<circle cx="9" cy="9" r="8" fill={ fill } stroke="white" strokeWidth="2"/>
			</svg>
		) }
	/>
);

addFilter(
	'gutenaFormsPro.core.components',
	'gutena-forms-free',
	( components ) => {

		components['Ellipse'] = Ellipse;

		return components;
	}
);

export default Ellipse;
