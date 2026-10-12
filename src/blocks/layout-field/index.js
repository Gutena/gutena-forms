import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';
import save from './save';
import { registerLayoutInsertRules } from '../../shared/layout/layout-insert-rules';
import './editor.scss';
import './style.scss';

registerLayoutInsertRules();

registerBlockType( metadata, {
	edit: Edit,
	save,
} );
