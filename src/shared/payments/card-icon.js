/**
 * Credit card icon used for Stripe payment field block UI.
 */
export const CardIcon = ( { size = 32, color = '#0DA88C' } ) => (
	<svg
		xmlns="http://www.w3.org/2000/svg"
		width={ size }
		height={ size }
		viewBox="0 0 16 16"
		fill="none"
		aria-hidden="true"
	>
		<rect x="1" y="3.5" width="14" height="9" rx="1.5" stroke={ color } strokeWidth="1.25" />
		<path d="M1 6.5H15" stroke={ color } strokeWidth="1.25" />
	</svg>
);

export default CardIcon;
