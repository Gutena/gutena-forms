import { Icon } from '@wordpress/components';

const Close = () => (
	<Icon
		icon={ () => (
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
				<path d="M10.0003 18.3327C14.5837 18.3327 18.3337 14.5827 18.3337 9.99935C18.3337 5.41601 14.5837 1.66602 10.0003 1.66602C5.41699 1.66602 1.66699 5.41601 1.66699 9.99935C1.66699 14.5827 5.41699 18.3327 10.0003 18.3327Z" stroke="white" strokeWidth="1.25" strokeLinecap="round" strokeLinejoin="round"/>
				<path d="M7.6416 12.3592L12.3583 7.64258" stroke="white" strokeWidth="1.25" strokeLinecap="round" strokeLinejoin="round"/>
				<path d="M12.3583 12.3592L7.6416 7.64258" stroke="white" strokeWidth="1.25" strokeLinecap="round" strokeLinejoin="round"/>
			</svg>
		) }
	/>
);

export default Close;
