/**
 * Human-readable section labels for tour steps.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { __ } from '@wordpress/i18n';
import { TOUR_SECTIONS } from '../constants';

const SECTION_LABELS = {
	[ TOUR_SECTIONS.INTRO ]: __( 'Introduction', 'gutena-forms' ),
	[ TOUR_SECTIONS.NAVIGATION ]: __( 'Navigation', 'gutena-forms' ),
	[ TOUR_SECTIONS.FORM_CREATION ]: __( 'Form Creation', 'gutena-forms' ),
	[ TOUR_SECTIONS.ENTRIES ]: __( 'Entries', 'gutena-forms' ),
	[ TOUR_SECTIONS.DONE ]: __( 'Complete', 'gutena-forms' ),
};

/**
 * @param {string} section
 * @returns {string}
 */
export function getSectionLabel( section ) {
	return SECTION_LABELS[ section ] || section;
}
