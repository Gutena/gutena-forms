import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Icon from './icon';

registerBlockType( metadata, {
	edit: () => null,
	save: () => null,
	icon: Icon,
} );
