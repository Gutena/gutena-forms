import { applyFilters } from '@wordpress/hooks';

export const LAYOUT_FIELD_BLOCK = 'gutena/layout-field';
export const LAYOUT_COLUMN_BLOCK = 'gutena/layout-column';

export const FREE_STANDALONE_FIELD_BLOCKS = [
	'gutena/text-field',
	'gutena/optin-field',
	'gutena/dropdown-field',
	'gutena/email-field',
	'gutena/number-field',
	'gutena/textarea-field',
	'gutena/range-field',
	'gutena/radio-field',
	'gutena/checkbox-field',
];

export const PRO_STANDALONE_FIELD_BLOCKS = [
	'gutena/url-field',
	'gutena/phone-field',
	'gutena/password-field',
	'gutena/hidden-field',
	'gutena/date-field',
	'gutena/time-field',
	'gutena/country-field',
	'gutena/state-field',
	'gutena/file-upload-field',
	'gutena/rating-field',
];

export const FORM_STRUCTURE_BLOCKS = [
	'core/columns',
	'core/group',
	'core/image',
	'core/paragraph',
	'core/buttons',
	'gutena/form-confirm-msg',
	'gutena/form-error-msg',
	LAYOUT_FIELD_BLOCK,
];

export const LAYOUT_COLUMN_BLOCKED_BLOCKS = [
	LAYOUT_FIELD_BLOCK,
	LAYOUT_COLUMN_BLOCK,
	'core/buttons',
	'gutena/form-confirm-msg',
	'gutena/form-error-msg',
	'gutena/page-break-field',
];

/**
 * Pro-only blocks allowed at form root (e.g. page break), not inside layout columns.
 */
export function getFormRootProBlocks() {
	if (
		typeof gutenaFormsBlock === 'undefined' ||
		! gutenaFormsBlock?.is_pro
	) {
		return [];
	}
	return applyFilters(
		'gutena_forms__allowed_inner_blocks',
		PRO_STANDALONE_FIELD_BLOCKS
	);
}

export function getProStandaloneFieldBlocks() {
	if (
		typeof gutenaFormsBlock === 'undefined' ||
		! gutenaFormsBlock?.is_pro
	) {
		return [];
	}
	return PRO_STANDALONE_FIELD_BLOCKS;
}

export function getLayoutColumnAllowedBlocks() {
	return [
		...FREE_STANDALONE_FIELD_BLOCKS,
		...getProStandaloneFieldBlocks(),
	];
}

export function getFormRootAllowedBlocks() {
	return [
		...FORM_STRUCTURE_BLOCKS,
		...FREE_STANDALONE_FIELD_BLOCKS,
		...getFormRootProBlocks(),
	];
}

export function isBlockAllowedInLayoutColumn( blockName ) {
	if ( ! blockName ) {
		return false;
	}
	if ( LAYOUT_COLUMN_BLOCKED_BLOCKS.includes( blockName ) ) {
		return false;
	}
	return getLayoutColumnAllowedBlocks().includes( blockName );
}
