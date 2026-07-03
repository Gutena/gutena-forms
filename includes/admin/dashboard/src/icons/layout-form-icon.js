import { Icon } from '@wordpress/components';

const LayoutFormIcon = () => (
	<Icon
		icon={ () => (
			<svg xmlns="http://www.w3.org/2000/svg" width="48" height="50" viewBox="0 0 48 50" fill="none">
				<rect x="0" y="0" width="20" height="14" rx="4" fill="#0DA88C" />
				<rect x="0" y="18" width="20" height="32" rx="4" fill="#087A68" />
				<rect x="24" y="0" width="24" height="32" rx="4" fill="#087A68" />
				<rect x="24" y="36" width="24" height="14" rx="4" fill="#0DA88C" />
			</svg>
		) }
	/>
);

export default LayoutFormIcon;
