import { Icon } from '@wordpress/components';

const PaymentIcon = () => (
	<Icon
		icon={ () => (
			<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M13.3333 2.66667H2.66667C1.93333 2.66667 1.33333 3.26667 1.33333 4V12C1.33333 12.7333 1.93333 13.3333 2.66667 13.3333H13.3333C14.0667 13.3333 14.6667 12.7333 14.6667 12V4C14.6667 3.26667 14.0667 2.66667 13.3333 2.66667ZM2.66667 4H13.3333V6.66667H2.66667V4ZM13.3333 12H2.66667V8H13.3333V12Z" fill="#4B5563"/>
				<path d="M4 10H6.66667V10.6667H4V10Z" fill="#4B5563"/>
			</svg>
		) }
	/>
);

export default PaymentIcon;
