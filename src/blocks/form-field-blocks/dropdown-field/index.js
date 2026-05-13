import { registerBlockType } from '@wordpress/blocks';
import './style.scss';
import Edit from './edit';
import Save from './save';
import metadata from './block.json';
import Icon from './icon';

registerBlockType( metadata, {
	icon: Icon,
	edit: Edit,
	save: Save,
} );
