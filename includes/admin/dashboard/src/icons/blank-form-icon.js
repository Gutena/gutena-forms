import { Icon } from '@wordpress/components';

const BlankFormIcon = () => (
	<Icon
		icon={ () => (
			<svg xmlns="http://www.w3.org/2000/svg" width="69" height="69" viewBox="0 0 69 69" fill="none">
				<rect x="8" y="8" width="53" height="53" rx="10" stroke="#0DA88C" strokeWidth="1.5" strokeDasharray="4 4" />
				<path d="M34.5 28V41M28 34.5H41" stroke="#0DA88C" strokeWidth="2" strokeLinecap="round" />
			</svg>
		) }
	/>
);

export default BlankFormIcon;
