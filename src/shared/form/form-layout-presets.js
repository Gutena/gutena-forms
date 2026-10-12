/**
 * Style attribute presets for basic vs modern form layout mode.
 */

const BASIC_INPUT_DEFAULTS = {
	inputBottomBorderOnly: false,
	inputBorderRadius: '8px',
	inputBgColor: '#ffffff',
};

const MODERN_INPUT_DEFAULTS = {
	inputBottomBorderOnly: true,
	inputBorderRadius: '0',
	inputBgColor: '#ffffff',
};

/**
 * @param {'basic'|'modern'} mode
 * @return {Object}
 */
export function getFormLayoutStyleAttributes( mode ) {
	if ( mode === 'modern' ) {
		return { ...MODERN_INPUT_DEFAULTS };
	}
	return { ...BASIC_INPUT_DEFAULTS };
}

/**
 * @param {'basic'|'modern'} mode
 * @return {string}
 */
export function getFormLayoutClassSuffix( mode ) {
	return mode === 'modern' ? 'gf-form-layout-modern' : 'gf-form-layout-basic';
}
