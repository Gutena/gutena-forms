import { registerBlockType } from '@wordpress/blocks';
import edit from './edit';
import save from './save';
import metadata from './block.json';
import { Icon } from '@wordpress/components';

const fieldGroup = () => (
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
					x="13.75"
					y="10.75"
					width="7.5"
					height="9.5"
					stroke="#3F6DE4"
					strokeWidth="1.5"
				/>
				<rect x="2" y="4" width="20" height="2" fill="#3F6DE4" />
				<rect x="2" y="11" width="9" height="2" fill="#3F6DE4" />
				<rect x="2" y="18" width="7" height="2" fill="#3F6DE4" />
			</svg>
		) }
	/>
);

registerBlockType( metadata, {
	icon: fieldGroup,
	edit,
	save,
} );
