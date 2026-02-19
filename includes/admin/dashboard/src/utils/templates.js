/**
 * Dashboard screen template mappings (settings and main pages).
 *
 * @since 1.7.0
 * @package Gutena Forms
 */

import GutenaFormsManageTags from '../screens/gutena-forms-manage-tags';
import GutenaFormsManageStatus from '../screens/gutena-forms-manage-status';
import GutenaFormsUserAccess from '../screens/gutena-forms-user-access';
import { applyFilters } from '@wordpress/hooks';

/**
 * React components for settings sub-screens (pro: tags, status, user access).
 *
 * @since 1.7.0
 * @type {Object.<string, React.ComponentType>}
 */
export const SettingsTemplates = {
	'manage-tags': GutenaFormsManageTags,
	'manage-status': GutenaFormsManageStatus,
	'user-access': GutenaFormsUserAccess,
	...applyFilters( 'gutena-forms.components', {} )
};

import GutenaFormsForms from '../screens/gutena-forms-forms';
import GutenaFormsEntries from '../screens/gutena-forms-entries';

/**
 * React components for main dashboard pages (forms list, entries list).
 *
 * @since 1.7.0
 * @type {Object.<string, React.ComponentType>}
 */
export const PageTemplates = {
	'forms': GutenaFormsForms,
	'entries': GutenaFormsEntries,
};
