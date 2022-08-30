import { registerBlockType } from '@wordpress/blocks';
import edit from './edit';
import metadata from './block.json';
import { Icon } from '@wordpress/components';

const formFieldIcon = () => (
	<Icon
		icon={ () => (
			<svg
				width="24"
				height="24"
				viewBox="0 0 24 24"
				fill="none"
				xmlns="http://www.w3.org/2000/svg"
			>
				<rect x="2" y="4" width="8" height="2" fill="#3F6DE4" />
				<rect x="2" y="11" width="8" height="2" fill="#3F6DE4" />
				<rect x="14" y="4" width="8" height="2" fill="#3F6DE4" />
				<rect x="14" y="11" width="8" height="2" fill="#3F6DE4" />
				<rect x="2" y="18" width="20" height="2" fill="#3F6DE4" />
			</svg>
		) }
	/>
);

registerBlockType( metadata, {
	icon: formFieldIcon,
	edit,
} );
