import { registerBlockType } from '@wordpress/blocks';
import { Icon } from '@wordpress/components';
import metadata from './block.json';
import save from './save';
import edit from './edit';

/*registerBlockType( metadata, {
	icon: <Icon
		icon={ () => (
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 2L2 22h20L12 2Z" stroke="black" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
			</svg>
		) }
	/>,
	edit,
	save,
} );*/

