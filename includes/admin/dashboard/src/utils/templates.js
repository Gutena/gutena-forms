/**
 * Dashboard screen template mappings (settings and main pages).
 *
 * @since 1.7.0
 * @package Gutena Forms
 */

import GutenaFormsManageTags from '../screens/gutena-forms-manage-tags';
import GutenaFormsManageStatus from '../screens/gutena-forms-manage-status';
import GutenaFormsUserAccess from '../screens/gutena-forms-user-access';
import GutenaFormsIntegrations from '../screens/gutena-forms-integrations'
import { applyFilters } from '@wordpress/hooks';
import GutenaFormsMcp from "../screens/gutena-forms-mcp";

const baseSettingsTemplates = {
	'manage-tags': GutenaFormsManageTags,
	'manage-status': GutenaFormsManageStatus,
	'user-access': GutenaFormsUserAccess,
	'integrations': GutenaFormsIntegrations,
	'mcp': GutenaFormsMcp,
};

/**
 * Resolve a settings screen template at render time so Pro can register overrides after the free bundle loads.
 *
 * @since 1.7.0
 * @param {string} templateName Template key from settings field config.
 * @returns {React.ComponentType|undefined}
 */
export const getSettingsTemplate = ( templateName ) => {
	const proTemplates = applyFilters( 'gutena-forms.components', {} );

	if ( proTemplates[ templateName ] ) {
		return proTemplates[ templateName ];
	}

	return baseSettingsTemplates[ templateName ];
};

/**
 * @deprecated Use getSettingsTemplate() so Pro overrides are resolved after both bundles load.
 * @type {Object.<string, React.ComponentType>}
 */
export const SettingsTemplates = {
	...baseSettingsTemplates,
	...applyFilters( 'gutena-forms.components', {} ),
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

import MCPConfigurationTemplate from "../components/mcp-configuration-template";

export const FieldTemplates = {
	'mcp-configuration': MCPConfigurationTemplate,
};
