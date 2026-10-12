/**
 * Default inner block templates for gutena/forms (mirrors PHP block builder).
 *
 * @see includes/admin/modules/templates/helpers/class-templates-block-builder.php
 */
import { __ } from '@wordpress/i18n';
import { LAYOUT_COLUMN_BLOCK, LAYOUT_FIELD_BLOCK } from './form-allowed-blocks';
import { getDisplayRatioForPreset } from './layout-presets';

/**
 * @returns {string}
 */
export function getPluginAssetUrl( relativePath ) {
	const base =
		typeof gutenaFormsBlock !== 'undefined' && gutenaFormsBlock?.pluginUrl
			? gutenaFormsBlock.pluginUrl
			: '';
	return base ? `${ base.replace( /\/$/, '' ) }/${ relativePath.replace( /^\//, '' ) }` : '';
}

function blankNameField() {
	return [
		'gutena/text-field',
		{
			nameAttr: 'f_0',
			isRequired: true,
			fieldName: __( 'Name', 'gutena-forms' ),
			fieldType: 'text',
			placeholder: __( 'Enter your name', 'gutena-forms' ),
		},
	];
}

function blankEmailField() {
	return [
		'gutena/email-field',
		{
			nameAttr: 'f_1',
			isRequired: true,
			fieldName: __( 'Email', 'gutena-forms' ),
			fieldType: 'email',
			placeholder: __( 'Enter email', 'gutena-forms' ),
		},
	];
}

function blankMessageField() {
	return [
		'gutena/textarea-field',
		{
			nameAttr: 'f_2',
			isRequired: true,
			fieldName: __( 'Message', 'gutena-forms' ),
			fieldType: 'textarea',
			textAreaRows: 5,
			placeholder: __( 'Type here', 'gutena-forms' ),
		},
	];
}

function formFooterTemplate() {
	const successIcon = getPluginAssetUrl(
		'src/blocks/form/variations/assets/success-tick.svg'
	);
	const errorIcon = getPluginAssetUrl(
		'src/blocks/form/variations/assets/error.svg'
	);

	return [
		[
			'core/buttons',
			{ className: 'gutena-forms-submit-buttons' },
			[
				[
					'core/button',
					{
						text: __( 'Submit', 'gutena-forms' ),
						className: 'gutena-forms-submit-button',
						placeholder: __( 'Submit', 'gutena-forms' ),
					},
				],
			],
		],
		[
			'gutena/form-confirm-msg',
			{},
			[
				[
					'core/group',
					{
						style: {
							spacing: {
								blockGap: '8px',
								padding: {
									bottom: '12px',
									right: '12px',
									left: '12px',
									top: '12px',
								},
							},
							color: { background: '#d8eacc' },
							border: { radius: '5px' },
						},
						layout: {
							type: 'flex',
							flexWrap: 'nowrap',
							verticalAlignment: 'center',
							justifyContent: 'left',
						},
					},
					[
						[
							'core/image',
							{
								sizeSlug: 'large',
								linkDestination: 'none',
								className: 'form-message-icon',
								url: successIcon,
								alt: __( 'Success', 'gutena-forms' ),
							},
						],
						[
							'core/paragraph',
							{
								style: {
									typography: {
										lineHeight: '1.2',
										fontStyle: 'normal',
										fontWeight: '500',
										fontSize: '12px',
									},
								},
								textColor: 'black',
								fontSize: 'tiny',
								content: __(
									'Your form submitted successfully!',
									'gutena-forms'
								),
							},
						],
					],
				],
			],
		],
		[
			'gutena/form-error-msg',
			{},
			[
				[
					'core/group',
					{
						style: {
							spacing: {
								blockGap: '8px',
								padding: {
									bottom: '12px',
									right: '12px',
									left: '12px',
									top: '12px',
								},
							},
							color: { background: '#ffd3d3' },
							border: { radius: '5px' },
						},
						layout: {
							type: 'flex',
							flexWrap: 'nowrap',
							verticalAlignment: 'center',
							justifyContent: 'left',
						},
					},
					[
						[
							'core/image',
							{
								sizeSlug: 'large',
								linkDestination: 'none',
								className: 'form-message-icon',
								url: errorIcon,
								alt: __( 'Error', 'gutena-forms' ),
							},
						],
						[
							'core/paragraph',
							{
								style: {
									typography: {
										lineHeight: '1.2',
										fontStyle: 'normal',
										fontWeight: '500',
										fontSize: '12px',
									},
								},
								textColor: 'black',
								className: 'gutena-forms-error-text',
								content: __(
									'Sorry! your form was not submitted properly, Please check the errors above.',
									'gutena-forms'
								),
							},
						],
					],
				],
			],
		],
	];
}

/**
 * Stacked name, email, message + form footer.
 *
 * @return {Array} Inner blocks template for gutena/forms.
 */
export function getBasicBlankInnerTemplate() {
	return [ blankNameField(), blankEmailField(), blankMessageField(), ...formFooterTemplate() ];
}

/**
 * Name | email in 50/50 layout, message full width, + footer.
 *
 * @return {Array}
 */
export function getModernBlankInnerTemplate() {
	const preset = '50-50';
	const displayRatio = getDisplayRatioForPreset( preset );

	return [
		[
			LAYOUT_FIELD_BLOCK,
			{
				layoutLabel: __( 'Layout', 'gutena-forms' ),
				preset,
				displayRatio,
			},
			[ LAYOUT_COLUMN_BLOCK, {}, [ blankNameField() ] ],
			[ LAYOUT_COLUMN_BLOCK, {}, [ blankEmailField() ] ],
		],
		blankMessageField(),
		...formFooterTemplate(),
	];
}

/**
 * Form block attributes for dashboard blank / CPT seed (basic spacing).
 *
 * @param {'basic'|'modern'} [layoutMode]
 * @return {Object}
 */
export function getBlankFormBlockAttributes( layoutMode = 'basic' ) {
	return {
		formLayoutMode: layoutMode,
		style: {
			spacing: {
				blockGap: '2rem',
				padding: {
					top: '2rem',
					bottom: '5rem',
				},
			},
		},
	};
}

export function getBasicBlankFormAttributes() {
	return getBlankFormBlockAttributes( 'basic' );
}

export function getModernBlankFormAttributes() {
	return getBlankFormBlockAttributes( 'modern' );
}
