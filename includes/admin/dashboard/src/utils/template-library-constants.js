/**
 * Template Library UI constants.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

import { __ } from '@wordpress/i18n';

/** All templates category slug (empty string = no filter). */
export const ALL_TEMPLATES_CATEGORY = '';

/** Category slugs that are visible but not yet filterable. */
export const COMING_SOON_CATEGORIES = [
	'payment-forms',
	'quizzes',
	'registration-forms',
];

/** Templates per page for grid pagination. */
export const TEMPLATES_PER_PAGE = 9;

/** External URL for requesting new templates. */
export const REQUEST_TEMPLATE_URL =
	'https://gutenaforms.com/roadmap/?utm_source=plugin&utm_medium=template_library&utm_campaign=request_template';

/** Label for the "all templates" pseudo-category. */
export const ALL_TEMPLATES_LABEL = __( 'All Form Templates', 'gutena-forms' );

/** Label for the request-template action. */
export const REQUEST_TEMPLATE_LABEL = __( 'Request Template', 'gutena-forms' );

/** Coming soon badge label. */
export const COMING_SOON_LABEL = __( 'Coming soon', 'gutena-forms' );

/** Free plan badge label. */
export const FREE_LABEL = __( 'Free', 'gutena-forms' );

/** Route path for the New Form creation screen. */
export const NEW_FORM_PATH = '/settings/new-form';

/** Route path for the standalone Template Library screen. */
export const TEMPLATE_LIBRARY_PATH = '/settings/templates';

/** Preview return context: template library list. */
export const PREVIEW_RETURN_LIBRARY = 'library';

/** Preview return context: new form creation screen. */
export const PREVIEW_RETURN_NEW_FORM = 'new-form';

/** Preview return context: form ready screen. */
export const PREVIEW_RETURN_FORM_READY = 'form-ready';

/** Preview return context: template modal details. */
export const PREVIEW_RETURN_MODAL = 'modal';

/** Category display order (excluding "All" and "Request Template"). */
export const CATEGORY_ORDER = [
	'application-forms',
	'booking-forms',
	'event-planning',
	'lead-generation',
	'marketing',
	'payment-forms',
	'quizzes',
	'registration-forms',
	'support-requests',
	'surveys-feedback',
];
