/**
 * Layout field preset definitions (single source of truth).
 */

export const DEFAULT_LAYOUT_PRESET = '50-50';

export const LAYOUT_PRESETS = {
	full: {
		key: 'full',
		label: 'Full width',
		columnCount: 1,
		gridTemplateColumns: '1fr',
		displayRatio: '100%',
		sidebarRatioLabel: 'Full width - 1/1',
	},
	'50-50': {
		key: '50-50',
		label: '50/50',
		columnCount: 2,
		gridTemplateColumns: '1fr 1fr',
		displayRatio: '50% / 50%',
		sidebarRatioLabel: '50% - 50%',
	},
	'33-33-33': {
		key: '33-33-33',
		label: '33/33/33',
		columnCount: 3,
		gridTemplateColumns: '1fr 1fr 1fr',
		displayRatio: '33% / 33% / 33%',
		sidebarRatioLabel: '33% - 33% - 33%',
	},
	'33-67': {
		key: '33-67',
		label: '33/67',
		columnCount: 2,
		gridTemplateColumns: '1fr 2fr',
		displayRatio: '33% / 67%',
		sidebarRatioLabel: '33% - 67%',
	},
	'67-33': {
		key: '67-33',
		label: '67/33',
		columnCount: 2,
		gridTemplateColumns: '2fr 1fr',
		displayRatio: '67% / 33%',
		sidebarRatioLabel: '67% - 33%',
	},
	'25-50-25': {
		key: '25-50-25',
		label: '25/50/25',
		columnCount: 3,
		gridTemplateColumns: '1fr 2fr 1fr',
		displayRatio: '25% / 50% / 25%',
		sidebarRatioLabel: '25% - 50% - 25%',
	},
	'50-25-25': {
		key: '50-25-25',
		label: '50/25/25',
		columnCount: 3,
		gridTemplateColumns: '2fr 1fr 1fr',
		displayRatio: '50% / 25% / 25%',
		sidebarRatioLabel: '50% - 25% - 25%',
	},
	'25-25-50': {
		key: '25-25-50',
		label: '25/25/50',
		columnCount: 3,
		gridTemplateColumns: '1fr 1fr 2fr',
		displayRatio: '25% / 25% / 50%',
		sidebarRatioLabel: '25% - 25% - 50%',
	},
	'25-25-25-25': {
		key: '25-25-25-25',
		label: '25/25/25/25',
		columnCount: 4,
		gridTemplateColumns: '1fr 1fr 1fr 1fr',
		displayRatio: '25% / 25% / 25% / 25%',
		sidebarRatioLabel: '25% - 25% - 25% - 25%',
	},
};

export const LAYOUT_PRESET_KEYS = Object.keys( LAYOUT_PRESETS );

export function getLayoutPreset( presetKey ) {
	return LAYOUT_PRESETS[ presetKey ] || LAYOUT_PRESETS[ DEFAULT_LAYOUT_PRESET ];
}

export function getDisplayRatioForPreset( presetKey ) {
	return getLayoutPreset( presetKey ).displayRatio;
}

export function getSidebarRatioLabelForPreset( presetKey ) {
	return getLayoutPreset( presetKey ).sidebarRatioLabel;
}

export function getColumnCountForPreset( presetKey ) {
	return getLayoutPreset( presetKey ).columnCount;
}
