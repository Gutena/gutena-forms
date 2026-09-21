import { Icon } from '@wordpress/components';

const AlertError = () => (
	<Icon
		icon={ () => (
			<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
				<circle cx="6" cy="6" r="6" fill="#D70404" />
				<path
					d="M6 3.2V6.5"
					stroke="white"
					strokeWidth="1.2"
					strokeLinecap="round"
				/>
				<circle cx="6" cy="8.4" r="0.7" fill="white" />
			</svg>
		) }
	/>
);

export default AlertError;
