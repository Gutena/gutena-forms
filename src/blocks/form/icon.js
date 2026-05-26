import { Icon } from '@wordpress/components';

export const gutenaFormsIcon = () => (
	<Icon
		icon={ () => (
			<svg
				width="24"
				height="24"
				viewBox="0 0 24 24"
				fill="none"
				xmlns="http://www.w3.org/2000/svg"
				color="#ffffff"
			>
				<rect
					x="2.75"
					y="3.75"
					width="18.5"
					height="16.5"
					stroke="#0EA489"
					strokeWidth="1.5"
				/>
				<rect x="6" y="7" width="12" height="1" fill="#0EA489" />
				<rect x="6" y="11" width="12" height="1" fill="#0EA489" />
				<rect x="6" y="15" width="12" height="1" fill="#0EA489" />
			</svg>
		) }
	/>
);

//lock 
export const lockIcon = ( color = '#606060') => (
	<Icon
		icon={ () => (
			<svg className="gf-lock-svg" width="11" height="12" viewBox="0 0 11 12" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M8.57143 4H9.71428C10.0299 4 10.2857 4.25584 10.2857 4.57143V11.4286C10.2857 11.7442 10.0299 12 9.71428 12H0.571429C0.25584 12 0 11.7442 0 11.4286V4.57143C0 4.25584 0.25584 4 0.571429 4H1.71429V3.42857C1.71429 1.53502 3.24931 0 5.14286 0C7.0364 0 8.57143 1.53502 8.57143 3.42857V4ZM4.57143 8.41851V9.71429H5.71429V8.41851C6.05589 8.22091 6.28571 7.8516 6.28571 7.42857C6.28571 6.79737 5.77406 6.28571 5.14286 6.28571C4.51166 6.28571 4 6.79737 4 7.42857C4 7.8516 4.22983 8.22091 4.57143 8.41851ZM7.42857 4V3.42857C7.42857 2.16621 6.4052 1.14286 5.14286 1.14286C3.88049 1.14286 2.85714 2.16621 2.85714 3.42857V4H7.42857Z" fill={ color } />
			</svg>
		) }
	/>
);