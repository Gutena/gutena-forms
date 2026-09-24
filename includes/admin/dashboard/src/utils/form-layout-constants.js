/**
 * Form layout metadata for the New Form layouts picker.
 *
 * Mirrors block variation titles in src/blocks/form/variations/index.js.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

import { __ } from '@wordpress/i18n';

/** WordPress post editor URL for creating a new gutena_forms post. */
export const NEW_FORM_EDITOR_URL = 'post-new.php?post_type=gutena_forms';

/** Layout options shown in the New Form layouts tab. */
export const FORM_LAYOUTS = [
	{
		name: 'one-column-basic',
		title: __( 'One column basic', 'gutena-forms' ),
		description: __( 'A simple single-column contact form layout.', 'gutena-forms' ),
	},
	{
		name: 'one-column-modern',
		title: __( 'One column modern', 'gutena-forms' ),
		description: __( 'A modern single-column form with underline-style fields.', 'gutena-forms' ),
	},
	{
		name: 'two-column-basic',
		title: __( 'Two column basic', 'gutena-forms' ),
		description: __( 'A two-column form layout for side-by-side fields.', 'gutena-forms' ),
	},
	{
		name: 'two-column-modern',
		title: __( 'Two column modern', 'gutena-forms' ),
		description: __( 'A modern two-column form with underline-style fields.', 'gutena-forms' ),
	},
];
