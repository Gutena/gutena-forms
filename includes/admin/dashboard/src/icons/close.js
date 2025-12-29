import { Icon } from '@wordpress/components';

const Close = () => (
	<Icon
		icon={ () => (
			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
				<path d="M8.00016 14.6668C11.6668 14.6668 14.6668 11.6668 14.6668 8.00016C14.6668 4.3335 11.6668 1.3335 8.00016 1.3335C4.3335 1.3335 1.3335 4.3335 1.3335 8.00016C1.3335 11.6668 4.3335 14.6668 8.00016 14.6668Z" stroke="white" strokeLinecap="round" strokeLinejoin="round"/>
				<path d="M6.11328 9.88661L9.88661 6.11328" stroke="white" strokeLinecap="round" strokeLinejoin="round"/>
				<path d="M9.88661 9.88661L6.11328 6.11328" stroke="white" strokeLinecap="round" strokeLinejoin="round"/>
			</svg>
		) }
	/>
);

export default Close;
