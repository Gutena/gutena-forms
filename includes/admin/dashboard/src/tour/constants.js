/**
 * Product tour constants.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

export const TOUR_STEP_COUNT = 16;
export const TOUR_FIRST_STEP = 0;
export const TOUR_LAST_STEP = 15;

export const TOUR_STATUS = {
	NOT_STARTED: 'not_started',
	ACTIVE: 'active',
	SKIPPED: 'skipped',
	COMPLETED: 'completed',
};

export const TOUR_TARGET_VIEWS = {
	DASHBOARD: 'dashboard',
	FORMS: 'forms',
	ENTRIES: 'entries',
	EXTRAS: 'extras',
	EDITOR: 'editor',
};

export const TOUR_SECTIONS = {
	INTRO: 'intro',
	NAVIGATION: 'navigation',
	FORM_CREATION: 'form_creation',
	ENTRIES: 'entries',
	DONE: 'done',
};

export const TOUR_SECTION_COLORS = {
	intro: '#0EA489',
	navigation: '#2271b1',
	form_creation: '#7c3aed',
	entries: '#0EA489',
	done: '#0EA489',
};

export const TOUR_PLACEMENT = {
	CENTER: 'center',
	TOP: 'top',
	BOTTOM: 'bottom',
	LEFT: 'left',
	RIGHT: 'right',
};

export const TOUR_TARGET_WAIT_TIMEOUT_MS = 10000;
export const TOUR_TARGET_POLL_INTERVAL_MS = 100;

export const TOUR_QUERY_PARAM = 'gf_tour_step';
