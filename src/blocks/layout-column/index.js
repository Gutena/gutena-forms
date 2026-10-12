import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';
import save from './save';
import './editor.scss';
import './style.scss';

registerBlockType( metadata, {
	edit: Edit,
	save,
} );
