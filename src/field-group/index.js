import { registerBlockType } from '@wordpress/blocks';
import edit from './edit';
import save from './save';
import metadata from './block.json';
import Icon from './icon';

registerBlockType( metadata, {
	icon: <Icon />,
	edit,
	save,
} );
