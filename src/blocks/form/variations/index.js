/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import ExistingFormsIcon from '../../existing-forms/icon';
import {
	OneColumnBasicIcon,
	OneColumnModernIcon,
} from './icons';
import {
	getBasicBlankFormAttributes,
	getBasicBlankInnerTemplate,
	getModernBlankFormAttributes,
	getModernBlankInnerTemplate,
} from '../../../shared/constants/default-form-inner-template';
import { getFormLayoutStyleAttributes } from '../../../shared/form/form-layout-presets';

/** @typedef {import('@wordpress/blocks').WPBlockVariation} WPBlockVariation */
/**
 * Block variations for page-context form insertion.
 *
 * @type {WPBlockVariation[]}
 */
const variations = [];

if ( ! gutenaFormsBlock.is_gutena_forms_post_type ) {
	variations.push(
		{
			name: 'form-basic',
			title: __( 'Basic form', 'gutena-forms' ),
			description: __(
				'Stacked fields with boxed inputs.',
				'gutena-forms'
			),
			attributes: {
				...getBasicBlankFormAttributes(),
				...getFormLayoutStyleAttributes( 'basic' ),
			},
			icon: <OneColumnBasicIcon />,
			innerBlocks: getBasicBlankInnerTemplate(),
			scope: [ 'block' ],
			isDefault: true,
		},
		{
			name: 'form-modern',
			title: __( 'Modern form', 'gutena-forms' ),
			description: __(
				'Underline inputs with column layout rows.',
				'gutena-forms'
			),
			attributes: {
				...getModernBlankFormAttributes(),
				...getFormLayoutStyleAttributes( 'modern' ),
			},
			icon: <OneColumnModernIcon />,
			innerBlocks: getModernBlankInnerTemplate(),
			scope: [ 'block' ],
		}
	);

	if ( gutenaFormsBlock.forms_available ) {
		variations.push( {
			name: 'existing-forms',
			title: '',
			description: __(
				'Use a form you have already created.',
				'gutena-forms'
			),
			attributes: {},
			icon: <ExistingFormsIcon />,
			innerBlocks: [ [ 'gutena/existing-forms' ] ],
			scope: [ 'block' ],
		} );
	}
}

export default variations;
