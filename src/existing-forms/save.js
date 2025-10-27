import { registerBlockType } from '@wordpress/blocks';
import { Icon } from '@wordpress/components';
import metadata from '../block.json';
import save from './save';
import edit from './edit';

registerBlockType( metadata, {
    icon: <Icon />,
    edit,
    save,
} );
