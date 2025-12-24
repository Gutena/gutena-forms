import { Icon } from '@wordpress/components';

const Plus = () => {
	return (
		<Icon
			icon={ () => (
				<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
					<circle cx="9" cy="9" r="9" fill="#D2FFF7"/>
					<path d="M8.17405 12.6V6H9.84158V12.6H8.17405ZM5.40002 10.0714V8.54286H12.6V10.0714H5.40002Z" fill="#0DA88C"/>
				</svg>
			) }
		/>
	);
}

export default Plus;
