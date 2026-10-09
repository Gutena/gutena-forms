import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Icon from './icon';
import ProFieldPlaceholder from '../../../../shared/pro-field-placeholder';

registerBlockType( metadata, {
	edit: ProFieldPlaceholder,
	save: () => null,
	icon: Icon,
} );