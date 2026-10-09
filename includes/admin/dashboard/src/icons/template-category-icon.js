import { Icon } from '@wordpress/components';

const TemplateCategoryIcon = ( { children, viewBox = '0 0 16 16', width = 16, height = 16 } ) => (
	<Icon
		icon={ () => (
			<svg
				xmlns="http://www.w3.org/2000/svg"
				width={ width }
				height={ height }
				viewBox={ viewBox }
				fill="none"
				aria-hidden="true"
			>
				{ children }
			</svg>
		) }
	/>
);

export default TemplateCategoryIcon;
