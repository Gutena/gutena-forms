import Edit from './edit';
import Save from './save';
import Icon from './icon';
import metadata from './block.json';
import { registerBlockType } from '@wordpress/blocks';

registerBlockType( metadata, {
	icon: Icon,
	edit: Edit,
	save: Save,
} );
