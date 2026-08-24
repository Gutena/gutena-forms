const RefundIcon = ( { color = '#0DA88C', size = 12 } ) => (
	<svg width={ size } height={ size } viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<path
			d="M10 6C10 8.20914 8.20914 10 6 10C3.79086 10 2 8.20914 2 6C2 3.79086 3.79086 2 6 2C7.48071 2 8.75 2.75 9.5 4"
			stroke={ color }
			strokeWidth="1.2"
			strokeLinecap="round"
		/>
		<path
			d="M9.5 2V4H7.5"
			stroke={ color }
			strokeWidth="1.2"
			strokeLinecap="round"
			strokeLinejoin="round"
		/>
	</svg>
);

export default RefundIcon;
