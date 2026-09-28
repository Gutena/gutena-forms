/**
 * Authoritative product tour step configuration (indexes 0–15).
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { __ } from '@wordpress/i18n';
import {
	TOUR_PLACEMENT,
	TOUR_SECTIONS,
	TOUR_SECTION_COLORS,
	TOUR_TARGET_VIEWS,
} from '../constants';
import { tourTargetSelector } from '../utils/tourTarget';

/**
 * @typedef {Object} TourStepConfig
 * @property {number} index
 * @property {string} id
 * @property {string} section
 * @property {string} title
 * @property {string} description
 * @property {string} targetView
 * @property {string} [targetSelector]
 * @property {() => (HTMLElement|null)} [resolveTarget]
 * @property {string} placement
 * @property {boolean} useSpotlight
 * @property {boolean} isIntro
 * @property {boolean} isDone
 * @property {string} sectionColor
 * @property {boolean} [navigateOnEnter]
 * @property {boolean} [editorRemountOnEnter]
 */

/** @type {TourStepConfig[]} */
export const TOUR_STEPS = [
	{
		index: 0,
		id: 'intro',
		section: TOUR_SECTIONS.INTRO,
		title: __( 'Welcome to Gutena Forms', 'gutena-forms' ),
		description: __(
			'Take a quick tour to learn how to build forms, manage entries, and get the most out of Gutena Forms.',
			'gutena-forms'
		),
		targetView: TOUR_TARGET_VIEWS.DASHBOARD,
		placement: TOUR_PLACEMENT.CENTER,
		useSpotlight: false,
		isIntro: true,
		isDone: false,
		navigateOnEnter: false,
		sectionColor: TOUR_SECTION_COLORS.intro,
	},
	{
		index: 1,
		id: 'tab-bar',
		section: TOUR_SECTIONS.NAVIGATION,
		title: __( 'Navigation', 'gutena-forms' ),
		description: __(
			'This is your main navigation hub. Use these tabs to move between Dashboard, Forms, Entries, and Extras.',
			'gutena-forms'
		),
		targetView: TOUR_TARGET_VIEWS.DASHBOARD,
		targetSelector: tourTargetSelector( 'tab-bar' ),
		placement: TOUR_PLACEMENT.BOTTOM,
		useSpotlight: true,
		isIntro: false,
		isDone: false,
		navigateOnEnter: false,
		sectionColor: TOUR_SECTION_COLORS.navigation,
	},
	{
		index: 2,
		id: 'tab-dashboard',
		section: TOUR_SECTIONS.NAVIGATION,
		title: __( 'Dashboard', 'gutena-forms' ),
		description: __(
			'Your home base for guides, feature highlights, and getting-started resources.',
			'gutena-forms'
		),
		targetView: TOUR_TARGET_VIEWS.DASHBOARD,
		targetSelector: tourTargetSelector( 'tab-dashboard' ),
		placement: TOUR_PLACEMENT.BOTTOM,
		useSpotlight: true,
		isIntro: false,
		isDone: false,
		navigateOnEnter: false,
		sectionColor: TOUR_SECTION_COLORS.navigation,
	},
	{
		index: 3,
		id: 'tab-forms',
		section: TOUR_SECTIONS.NAVIGATION,
		title: __( 'Forms', 'gutena-forms' ),
		description: __(
			'Create, edit, and manage all of your forms from this area.',
			'gutena-forms'
		),
		targetView: TOUR_TARGET_VIEWS.DASHBOARD,
		targetSelector: tourTargetSelector( 'tab-forms' ),
		placement: TOUR_PLACEMENT.BOTTOM,
		useSpotlight: true,
		isIntro: false,
		isDone: false,
		navigateOnEnter: false,
		sectionColor: TOUR_SECTION_COLORS.navigation,
	},
	{
		index: 4,
		id: 'tab-entries',
		section: TOUR_SECTIONS.NAVIGATION,
		title: __( 'Entries', 'gutena-forms' ),
		description: __(
			'All form submissions are stored here so you can review and manage entries.',
			'gutena-forms'
		),
		targetView: TOUR_TARGET_VIEWS.DASHBOARD,
		targetSelector: tourTargetSelector( 'tab-entries' ),
		placement: TOUR_PLACEMENT.BOTTOM,
		useSpotlight: true,
		isIntro: false,
		isDone: false,
		navigateOnEnter: false,
		sectionColor: TOUR_SECTION_COLORS.navigation,
	},
	{
		index: 5,
		id: 'tab-extras',
		section: TOUR_SECTIONS.NAVIGATION,
		title: __( 'Extras', 'gutena-forms' ),
		description: __(
			'Access Settings, submit a Feature Request, and open the Knowledge Base for help and documentation.',
			'gutena-forms'
		),
		targetView: TOUR_TARGET_VIEWS.DASHBOARD,
		targetSelector: tourTargetSelector( 'tab-extras' ),
		placement: TOUR_PLACEMENT.BOTTOM,
		useSpotlight: true,
		isIntro: false,
		isDone: false,
		navigateOnEnter: false,
		sectionColor: TOUR_SECTION_COLORS.navigation,
	},
	{
		index: 6,
		id: 'add-new-form',
		section: TOUR_SECTIONS.FORM_CREATION,
		title: __( 'Add New Form', 'gutena-forms' ),
		description: __(
			'Click Add New Form to open the Gutena Forms editor and start building.',
			'gutena-forms'
		),
		targetView: TOUR_TARGET_VIEWS.FORMS,
		targetSelector: tourTargetSelector( 'add-new-form' ),
		placement: TOUR_PLACEMENT.LEFT,
		useSpotlight: true,
		isIntro: false,
		isDone: false,
		navigateOnEnter: true,
		sectionColor: TOUR_SECTION_COLORS.form_creation,
	},
	{
		index: 7,
		id: 'layout-picker',
		section: TOUR_SECTIONS.FORM_CREATION,
		title: __( 'Layout Picker', 'gutena-forms' ),
		description: __(
			'Choose from four starter layouts: One Column Basic, One Column Modern, Two Column Basic, and Two Column Modern.',
			'gutena-forms'
		),
		targetView: TOUR_TARGET_VIEWS.EDITOR,
		targetSelector: tourTargetSelector( 'layout-picker' ),
		resolveTarget: () =>
			document.querySelector( tourTargetSelector( 'layout-picker' ) ) ||
			document.querySelector( '.block-editor-block-variation-picker' ) ||
			document.querySelector(
				'.wp-block-gutena-forms .block-editor-block-variation-picker'
			) ||
			document.querySelector( tourTargetSelector( 'editor-canvas' ) ) ||
			document.querySelector( '.wp-block-gutena-forms' ) ||
			document.querySelector( '.edit-post-visual-editor' ),
		placement: TOUR_PLACEMENT.RIGHT,
		useSpotlight: true,
		isIntro: false,
		isDone: false,
		navigateOnEnter: true,
		sectionColor: TOUR_SECTION_COLORS.form_creation,
	},
	{
		index: 8,
		id: 'editor-blocks',
		section: TOUR_SECTIONS.FORM_CREATION,
		title: __( 'Editor Blocks', 'gutena-forms' ),
		description: __(
			'Add and manage field types from the block inserter, including text, email, textarea, and more.',
			'gutena-forms'
		),
		targetView: TOUR_TARGET_VIEWS.EDITOR,
		targetSelector: tourTargetSelector( 'editor-blocks' ),
		resolveTarget: () =>
			document.querySelector(
				`${ tourTargetSelector( 'editor-blocks' ) }, .editor-document-tools__inserter-toggle, .block-editor-inserter__toggle`
			),
		placement: TOUR_PLACEMENT.RIGHT,
		useSpotlight: true,
		isIntro: false,
		isDone: false,
		navigateOnEnter: true,
		editorRemountOnEnter: true,
		sectionColor: TOUR_SECTION_COLORS.form_creation,
	},
	{
		index: 9,
		id: 'editor-canvas',
		section: TOUR_SECTIONS.FORM_CREATION,
		title: __( 'Editor Canvas', 'gutena-forms' ),
		description: __(
			'Build your form on the canvas. Click fields to select them and drag to reorder blocks.',
			'gutena-forms'
		),
		targetView: TOUR_TARGET_VIEWS.EDITOR,
		targetSelector: tourTargetSelector( 'editor-canvas' ),
		resolveTarget: () =>
			document.querySelector(
				`${ tourTargetSelector( 'editor-canvas' ) }, .wp-block-gutena-forms .gutena-forms-content-wrapper`
			),
		placement: TOUR_PLACEMENT.TOP,
		useSpotlight: true,
		isIntro: false,
		isDone: false,
		navigateOnEnter: false,
		sectionColor: TOUR_SECTION_COLORS.form_creation,
	},
	{
		index: 10,
		id: 'editor-settings',
		section: TOUR_SECTIONS.FORM_CREATION,
		title: __( 'Editor Settings', 'gutena-forms' ),
		description: __(
			'Configure form and field settings in the Block panel, including labels, colors, email options, and validation.',
			'gutena-forms'
		),
		targetView: TOUR_TARGET_VIEWS.EDITOR,
		targetSelector: tourTargetSelector( 'editor-settings' ),
		resolveTarget: () =>
			document.querySelector(
				`${ tourTargetSelector( 'editor-settings' ) }, .interface-interface-skeleton__sidebar`
			),
		placement: TOUR_PLACEMENT.LEFT,
		useSpotlight: true,
		isIntro: false,
		isDone: false,
		navigateOnEnter: false,
		sectionColor: TOUR_SECTION_COLORS.form_creation,
	},
	{
		index: 11,
		id: 'embed-in-page',
		section: TOUR_SECTIONS.FORM_CREATION,
		title: __( 'Embed in Page', 'gutena-forms' ),
		description: __(
			'Use the Gutena Form sidebar to embed your form on any page with the Gutena Forms block or shortcode.',
			'gutena-forms'
		),
		targetView: TOUR_TARGET_VIEWS.EDITOR,
		targetSelector: tourTargetSelector( 'embed-in-page' ),
		placement: TOUR_PLACEMENT.LEFT,
		useSpotlight: true,
		isIntro: false,
		isDone: false,
		navigateOnEnter: true,
		editorRemountOnEnter: true,
		sectionColor: TOUR_SECTION_COLORS.form_creation,
	},
	{
		index: 12,
		id: 'editor-save',
		section: TOUR_SECTIONS.FORM_CREATION,
		title: __( 'Save', 'gutena-forms' ),
		description: __(
			'Save your draft or publish the form when you are ready to start collecting submissions.',
			'gutena-forms'
		),
		targetView: TOUR_TARGET_VIEWS.EDITOR,
		targetSelector: tourTargetSelector( 'editor-save' ),
		resolveTarget: () =>
			document.querySelector(
				`${ tourTargetSelector( 'editor-save' ) }, .edit-post-header__settings, .editor-header__settings`
			),
		placement: TOUR_PLACEMENT.BOTTOM,
		useSpotlight: true,
		isIntro: false,
		isDone: false,
		navigateOnEnter: false,
		sectionColor: TOUR_SECTION_COLORS.form_creation,
	},
	{
		index: 13,
		id: 'entries-table',
		section: TOUR_SECTIONS.ENTRIES,
		title: __( 'Entries Table', 'gutena-forms' ),
		description: __(
			'Browse all submissions in this table. Filter by form, status, or tags to find specific entries.',
			'gutena-forms'
		),
		targetView: TOUR_TARGET_VIEWS.ENTRIES,
		targetSelector: tourTargetSelector( 'entries-table' ),
		resolveTarget: () =>
			document.querySelector(
				`${ tourTargetSelector( 'entries-table' ) }, #gutena-forms__entries-table`
			),
		placement: TOUR_PLACEMENT.TOP,
		useSpotlight: true,
		isIntro: false,
		isDone: false,
		navigateOnEnter: true,
		sectionColor: TOUR_SECTION_COLORS.entries,
	},
	{
		index: 14,
		id: 'entries-row',
		section: TOUR_SECTIONS.ENTRIES,
		title: __( 'Entry Row', 'gutena-forms' ),
		description: __(
			'Each row shows the Entry ID, Form Name, First Value, Status, and actions to view or manage the submission.',
			'gutena-forms'
		),
		targetView: TOUR_TARGET_VIEWS.ENTRIES,
		targetSelector: tourTargetSelector( 'entries-row' ),
		resolveTarget: () =>
			document.querySelector(
				`${ tourTargetSelector( 'entries-row' ) }, ${ tourTargetSelector( 'entries-row-placeholder' ) }`
			),
		placement: TOUR_PLACEMENT.BOTTOM,
		useSpotlight: true,
		isIntro: false,
		isDone: false,
		navigateOnEnter: false,
		sectionColor: TOUR_SECTION_COLORS.entries,
	},
	{
		index: 15,
		id: 'done',
		section: TOUR_SECTIONS.DONE,
		title: __( "You're all set! 🎉", 'gutena-forms' ),
		description: __(
			'You have completed the Gutena Forms tour. You are ready to build forms and manage entries.',
			'gutena-forms'
		),
		targetView: TOUR_TARGET_VIEWS.DASHBOARD,
		placement: TOUR_PLACEMENT.CENTER,
		useSpotlight: false,
		isIntro: false,
		isDone: true,
		navigateOnEnter: true,
		sectionColor: TOUR_SECTION_COLORS.done,
	},
];

/**
 * @param {number} stepIndex
 * @returns {TourStepConfig|undefined}
 */
export function getTourStep( stepIndex ) {
	return TOUR_STEPS.find( ( step ) => step.index === stepIndex );
}

/**
 * @param {number} stepIndex
 * @returns {boolean}
 */
export function isValidTourStep( stepIndex ) {
	return Number.isInteger( stepIndex ) && stepIndex >= 0 && stepIndex <= 15;
}

/**
 * @param {string} targetView
 * @returns {boolean}
 */
export function isEditorTourView( targetView ) {
	return targetView === TOUR_TARGET_VIEWS.EDITOR;
}
