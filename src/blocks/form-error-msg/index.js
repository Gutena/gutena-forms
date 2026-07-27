import { registerBlockType } from '@wordpress/blocks';
import edit from './edit';
import save from './save';
import metadata from './block.json';
import { Icon } from '@wordpress/components';

const formErrorMessage = () => (
	<Icon
		icon={ () => (
			<svg
				width="16"
				height="16"
				viewBox="0 0 16 16"
				fill="none"
				xmlns="http://www.w3.org/2000/svg"
			>
				<path
					d="M8 16C3.5816 16 0 12.4184 0 8C0 3.5816 3.5816 0 8 0C12.4184 0 16 3.5816 16 8C16 12.4184 12.4184 16 8 16ZM8 14.4C9.69739 14.4 11.3253 13.7257 12.5255 12.5255C13.7257 11.3253 14.4 9.69739 14.4 8C14.4 6.30261 13.7257 4.67475 12.5255 3.47452C11.3253 2.27428 9.69739 1.6 8 1.6C6.30261 1.6 4.67475 2.27428 3.47452 3.47452C2.27428 4.67475 1.6 6.30261 1.6 8C1.6 9.69739 2.27428 11.3253 3.47452 12.5255C4.67475 13.7257 6.30261 14.4 8 14.4ZM7.2 10.4H8.8V12H7.2V10.4ZM7.2 4H8.8V8.8H7.2V4Z"
					fill="#C51919"
				/>
			</svg>
		) }
	/>
);

registerBlockType( metadata, {
	icon: formErrorMessage,
	edit,
	save,
} );
