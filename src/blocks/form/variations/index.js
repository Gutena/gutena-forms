/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import ExistingFormsIcon from '../../existing-forms/icon';

/** @typedef {import('@wordpress/blocks').WPBlockVariation} WPBlockVariation */
/**
 * Block variations for page-context form insertion.
 * Layout templates are managed via the dashboard template library.
 *
 * @type {WPBlockVariation[]}
 */
const variations = [];

if ( ! gutenaFormsBlock.is_gutena_forms_post_type && gutenaFormsBlock.forms_available ) {
	variations.push(
		{
			name: 'existing-forms',
			title: '',
			description: __( 'Use a form you have already created.', 'gutena-forms' ),
			attributes: {},
			icon: <ExistingFormsIcon />,
			innerBlocks: [
				[ 'gutena/existing-forms' ]
			],
			scope: [ 'block' ],
		}
	);
}

export default variations;
