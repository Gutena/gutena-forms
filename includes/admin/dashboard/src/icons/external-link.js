const ExternalLink = ( { color = '#0DA88C', size = 12 } ) => (
	<svg width={ size } height={ size } viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<path d="M9.5 2.5H7.5M9.5 2.5V4.5M9.5 2.5L5 7M4 3H3.5C2.67157 3 2 3.67157 2 4.5V8.5C2 9.32843 2.67157 10 3.5 10H7.5C8.32843 10 9 9.32843 9 8.5V8" stroke={ color } strokeWidth="1.2" strokeLinecap="round" strokeLinejoin="round" />
	</svg>
);

export default ExternalLink;
