import { Icon } from '@wordpress/components';

export const Plus = () => (
	<Icon
		icon={ () => (
			<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
				<circle cx="9" cy="9" r="9" fill="#D2FFF7"/>
				<path d="M8.17405 12.6V6H9.84158V12.6H8.17405ZM5.40002 10.0714V8.54286H12.6V10.0714H5.40002Z" fill="#0DA88C"/>
			</svg>
		) }
	/>
)

export const AddNew = () => (
	<Icon
		icon={ () => (
			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
				<path d="M5.33325 8H10.6666" stroke="#424D55" strokeLinecap="round" strokeLinejoin="round"/>
				<path d="M8 10.6668V5.3335" stroke="#424D55" strokeLinecap="round" strokeLinejoin="round"/>
				<path d="M5.99992 14.6668H9.99992C13.3333 14.6668 14.6666 13.3335 14.6666 10.0002V6.00016C14.6666 2.66683 13.3333 1.3335 9.99992 1.3335H5.99992C2.66659 1.3335 1.33325 2.66683 1.33325 6.00016V10.0002C1.33325 13.3335 2.66659 14.6668 5.99992 14.6668Z" stroke="#424D55" strokeLinecap="round" strokeLinejoin="round"/>
			</svg>
		) }
	/>
);
