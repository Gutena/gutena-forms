import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import edit from './edit';
import save from './save';
import { gutenaFormsIcon } from './icon';
import variations from '../../variations';
import './style.scss';

registerBlockType( metadata, {
	icon: gutenaFormsIcon,
	variations,
	edit,
	save,
} );
