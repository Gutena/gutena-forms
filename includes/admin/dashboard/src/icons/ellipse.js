import { Icon } from '@wordpress/components';

const Ellipse = ( { fill } ) => (
	<Icon
		icon={ () => (
			<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
				<circle cx="9" cy="9" r="8" fill={ fill } stroke="white" stroke-width="2"/>
			</svg>
		) }
	/>
);

export default Ellipse;
