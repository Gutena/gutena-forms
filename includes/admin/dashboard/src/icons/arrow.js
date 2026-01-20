import { Icon } from '@wordpress/components';

const Arrow = () => (
	<Icon
		icon={ () => (
			<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 11 11" fill="none">
				<path d="M10 7L5.38976 3.12757L1.00353 7.25201" stroke="#424D55" strokeLinecap="round"/>
			</svg>
		) }
	/>
)

export default Arrow;

export const ArrowLeft = () => (
	<Icon
		icon={ () => (
			<svg xmlns="http://www.w3.org/2000/svg" width="15" height="12" viewBox="0 0 15 12" fill="none">
				<path d="M0.21967 4.99262C-0.0732231 5.28551 -0.0732231 5.76039 0.21967 6.05328L4.99264 10.8263C5.28553 11.1191 5.76041 11.1191 6.0533 10.8263C6.34619 10.5334 6.34619 10.0585 6.0533 9.76559L1.81066 5.52295L6.0533 1.28031C6.34619 0.987415 6.34619 0.512542 6.0533 0.219648C5.76041 -0.073245 5.28553 -0.073245 4.99264 0.219648L0.21967 4.99262ZM14.75 5.52295V4.77295L0.75 4.77295V5.52295V6.27295L14.75 6.27295V5.52295Z" fill="#2C3338"/>
			</svg>
		) }
	/>
);
