
import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import Save from './save';
import Icon from './icon';
import metadata from './block.json';

registerBlockType( metadata, {
	edit: Edit,
	save: Save,
	icon: Icon,
} );
