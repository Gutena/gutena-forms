import { Icon } from '@wordpress/components';

const Checklist = () => (
	<Icon
		icon={ () => (
			<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
				<g clipPath="url(#clip0_133_3289)">
					<path d="M12.625 7C12.625 10.1066 10.1066 12.625 7 12.625C3.8934 12.625 1.375 10.1066 1.375 7C1.375 3.8934 3.8934 1.375 7 1.375C10.1066 1.375 12.625 3.8934 12.625 7Z" fill="url(#paint0_linear_133_3289)"/>
					<path d="M13.75 7C13.75 10.7279 10.7279 13.75 7 13.75C3.27209 13.75 0.25 10.7279 0.25 7C0.25 3.27209 3.27209 0.25 7 0.25C10.7279 0.25 13.75 3.27209 13.75 7Z" fill="url(#paint1_linear_133_3289)"/>
					<path d="M9.49747 4.64362C9.82698 4.31411 10.3612 4.31411 10.6907 4.64362C11.0202 4.97313 11.0202 5.50737 10.6907 5.83688L7.50872 9.01886C7.17921 9.34834 6.64498 9.34834 6.31549 9.01886C5.98598 8.68934 5.98598 8.15511 6.31549 7.8256L9.49747 4.64362Z" fill="white"/>
					<path d="M4.33307 7.03412C4.00356 6.7046 4.00356 6.1704 4.33307 5.84088C4.66258 5.51137 5.19682 5.51137 5.52633 5.84088L7.51505 7.8296C7.84456 8.15912 7.84456 8.69335 7.51505 9.02286C7.18557 9.35238 6.65133 9.35238 6.32182 9.02286L4.33307 7.03412Z" fill="white"/>
				</g>
				<defs>
					<linearGradient id="paint0_linear_133_3289" x1="7" y1="1.375" x2="7" y2="12.625" gradientUnits="userSpaceOnUse">
						<stop stopColor="#0DA88C"/>
						<stop offset="1" stopColor="#0DA88C" stopOpacity="0"/>
					</linearGradient>
					<linearGradient id="paint1_linear_133_3289" x1="7" y1="0.25" x2="7" y2="13.75" gradientUnits="userSpaceOnUse">
						<stop stopColor="#0DA88C"/>
						<stop offset="1" stopColor="#0DA88C" stopOpacity="0"/>
					</linearGradient>
					<clipPath id="clip0_133_3289">
						<rect width="14" height="14" fill="white"/>
					</clipPath>
				</defs>
			</svg>
		) }
	/>
);

export default Checklist;
