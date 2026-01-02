/**
 * WordPress dependencies
 */
import { Icon } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import successIcon from './success-tick.svg';
import errorIcon from './error.svg';
import ExistingFormsIcon from './existing-forms/icon';
import { OneColumnBasicIcon, OneColumnModernIcon, TwoColumnBasicIcon, TwoColumnModernIcon } from './variation-icons';

/** @typedef {import('@wordpress/blocks').WPBlockVariation} WPBlockVariation */
/**
 * Template option choices for predefined forms layouts.
 *
 * @type {WPBlockVariation[]}
 */
const variations = [];

if ( ! gutenaFormsBlock.is_gutena_forms_post_type && gutenaFormsBlock.forms_available ) {
	variations.push(
		{
			name: 'existing-forms',
			title: __( 'Chose from existing forms in your site', 'gutena-forms' ),
			description: __( 'Use a form you have already created.', 'gutena-forms' ),
			attributes: {},
			icon: <ExistingFormsIcon />,
			innerBlocks: [
				[ 'gutena/existing-forms' ]
			],
			scope: [ 'block' ],
		}
	);
	variations.push( {
		name: 'placeholder',
	} );
}

variations.push( {
	name: 'one-column-basic',
	title: __( 'One column basic', 'gutena-forms' ),
	description: __( 'One column basic', 'gutena-forms' ),
	attributes: {
		style: {
			spacing: {
				blockGap: '2rem',
				padding: { top: '2rem', bottom: '5rem' },
			},
		},
	},
	icon: <OneColumnBasicIcon />,
	innerBlocks: [
		[
			'gutena/text-field-group',
			{
				isRequired: true,
				fieldLabel: __( 'Field label', 'gutena-forms' ),
				fieldLabelContent: __( 'First name', 'gutena-forms' ),
				fieldType: 'text',
				placeholder: __( 'Enter your first name', 'gutena-forms' ),
			},
		],
		[
			'gutena/text-field-group',
			{
				isRequired: true,
				fieldLabel: __( 'Field label', 'gutena-forms' ),
				fieldLabelContent: __( 'Last name', 'gutena-forms' ),
				fieldType: 'text',
				placeholder: __( 'Enter your last name', 'gutena-forms' ),
			},
		],
		[
			'gutena/email-field-group',
			{
				isRequired: true,
				fieldLabel: __( 'Field label', 'gutena-forms' ),
				fieldLabelContent: 'Email',
				fieldType: 'email',
				placeholder: __( 'example@gmail.com', 'gutena-forms' ),
			},
		],
		[
			'gutena/text-field-group',
			{
				isRequired: true,
				fieldLabel: __( 'Field label', 'gutena-forms' ),
				fieldLabelContent: __( 'Subject', 'gutena-forms' ),
				fieldType: 'text',
				placeholder: __( 'Subject', 'gutena-forms' ),
			},
		],
		[
			'gutena/textarea-field-group',
			{
				isRequired: true,
				fieldLabel: __( 'Field label', 'gutena-forms' ),
				fieldLabelContent: __( 'Message', 'gutena-forms' ),
				fieldType: 'textarea',
				textAreaRows: 5,
				placeholder: __( 'Type here', 'gutena-forms' ),
			},
		],
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
								id: 3811,
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
								id: 3811,
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
	],
	scope: [ 'block' ],
	isDefault: true,
} );
variations.push( {
	name: 'one-column-modern',
	title: __( 'One column modern', 'gutena-forms' ),
	description: __( 'One column modern', 'gutena-forms' ),
	attributes: {
		inputBottomBorderOnly: true,
		style: {
			spacing: {
				blockGap: '2rem',
				padding: { top: '2rem', bottom: '5rem' },
			},
		},
	},
	icon: <OneColumnModernIcon />,
	innerBlocks: [
		[
			'gutena/text-field-group',
			{
				isRequired: true,
				fieldLabel: __( 'Field label', 'gutena-forms' ),
				fieldLabelContent: __( 'First name', 'gutena-forms' ),
				fieldType: 'text',
				placeholder: __( 'Enter your first name', 'gutena-forms' ),
			},
		],
		[
			'gutena/text-field-group',
			{
				isRequired: true,
				fieldLabel: __( 'Field label', 'gutena-forms' ),
				fieldLabelContent: __( 'Last name', 'gutena-forms' ),
				fieldType: 'text',
				placeholder: __( 'Enter your last name', 'gutena-forms' ),
			},
		],
		[
			'gutena/email-field-group',
			{
				isRequired: true,
				fieldLabel: __( 'Field label', 'gutena-forms' ),
				fieldLabelContent: 'Email',
				fieldType: 'email',
				placeholder: __( 'example@gmail.com', 'gutena-forms' ),
			},
		],
		[
			'gutena/text-field-group',
			{
				isRequired: true,
				fieldLabel: __( 'Field label', 'gutena-forms' ),
				fieldLabelContent: __( 'Subject', 'gutena-forms' ),
				fieldType: 'text',
				placeholder: __( 'Subject', 'gutena-forms' ),
			},
		],
		[
			'gutena/textarea-field-group',
			{
				isRequired: true,
				fieldLabel: __( 'Field label', 'gutena-forms' ),
				fieldLabelContent: __( 'Message', 'gutena-forms' ),
				fieldType: 'textarea',
				textAreaRows: 5,
				placeholder: __( 'Type here', 'gutena-forms' ),
			},
		],
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
								id: 3811,
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
								id: 3811,
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
	],
	scope: [ 'block' ],
} );
variations.push( {
	name: 'two-column-basic',
	title: __( 'Two column basic', 'gutena-forms' ),
	description: __( 'Two column basic', 'gutena-forms' ),
	attributes: {
		style: {
			spacing: {
				blockGap: '2rem',
				padding: {
					top: '2rem',
					bottom: '5rem',
					right: '1.25rem',
					left: '1.25rem',
				},
			},
		},
	},
	icon: <TwoColumnBasicIcon />,
	innerBlocks: [
		[
			'core/columns',
			{ verticalAlignment: 'top', align: 'full' },
			[
				[
					'core/column',
					{ verticalAlignment: 'top' },
					[
						[
							'gutena/text-field-group',
							{
								isRequired: true,
								fieldLabel: __(
									'Field label',
									'gutena-forms'
								),
								fieldLabelContent: __(
									'First name',
									'gutena-forms'
								),
								fieldType: 'text',
								placeholder: __(
									'Enter your first name',
									'gutena-forms'
								),
							},
						],
					],
				],
				[
					'core/column',
					{ verticalAlignment: 'top' },
					[
						[
							'gutena/text-field-group',
							{
								isRequired: true,
								fieldLabel: __(
									'Field label',
									'gutena-forms'
								),
								fieldLabelContent: __(
									'Last name',
									'gutena-forms'
								),
								fieldType: 'text',
								placeholder: __(
									'Enter your last name',
									'gutena-forms'
								),
							},
						],
					],
				],
			],
		],
		[
			'core/columns',
			{ verticalAlignment: 'top', align: 'full' },
			[
				[
					'core/column',
					{ verticalAlignment: 'top' },
					[
						[
							'gutena/text-field-group',
							{
								isRequired: true,
								fieldLabel: __(
									'Field label',
									'gutena-forms'
								),
								fieldLabelContent: __(
									'Subject',
									'gutena-forms'
								),
								fieldType: 'text',
								placeholder: __(
									'Subject',
									'gutena-forms'
								),
							},
						],
					],
				],
				[
					'core/column',
					{ verticalAlignment: 'top' },
					[
						[
							'gutena/email-field-group',
							{
								isRequired: true,
								fieldLabel: __(
									'Field label',
									'gutena-forms'
								),
								fieldLabelContent: 'Email',
								fieldType: 'email',
								placeholder: __(
									'example@gmail.com',
									'gutena-forms'
								),
							},
						],
					],
				],
			],
		],
		[
			'core/columns',
			{ verticalAlignment: 'top', align: 'full' },
			[
				[
					'core/column',
					{ verticalAlignment: 'top' },
					[
						[
							'gutena/textarea-field-group',
							{
								isRequired: true,
								fieldLabel: __(
									'Field label',
									'gutena-forms'
								),
								fieldLabelContent: __(
									'Message',
									'gutena-forms'
								),
								fieldType: 'textarea',
								textAreaRows: 5,
								placeholder: __(
									'Type here',
									'gutena-forms'
								),
							},
						],
					],
				],
			],
		],
		[
			'core/columns',
			{ verticalAlignment: 'top', align: 'full' },
			[
				[
					'core/column',
					{ verticalAlignment: 'top' },
					[
						[
							'core/buttons',
							{ className: 'gutena-forms-submit-buttons' },
							[
								[
									'core/button',
									{
										text: __(
											'Submit',
											'gutena-forms'
										),
										className:
											'gutena-forms-submit-button',
										placeholder: __(
											'Submit',
											'gutena-forms'
										),
									},
								],
							],
						],
					],
				],
			],
		],
		[
			'gutena/form-confirm-msg',
			{ align: 'full' },
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
								id: 3811,
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
			{ align: 'full' },
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
								id: 3811,
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
	],
	scope: [ 'block' ],
} );
variations.push( {
	name: 'two-column-modern',
	title: __( 'Two column modern', 'gutena-forms' ),
	description: __( 'Two column modern', 'gutena-forms' ),
	attributes: {
		inputBottomBorderOnly: true,
		style: {
			spacing: {
				blockGap: '2rem',
				padding: {
					top: '2rem',
					bottom: '5rem',
					right: '1.25rem',
					left: '1.25rem',
				},
			},
		},
	},
	icon: <TwoColumnModernIcon />,
	innerBlocks: [
		[
			'core/columns',
			{ verticalAlignment: 'top', align: 'full' },
			[
				[
					'core/column',
					{ verticalAlignment: 'top' },
					[
						[
							'gutena/text-field-group',
							{
								isRequired: true,
								fieldLabel: __(
									'Field label',
									'gutena-forms'
								),
								fieldLabelContent: __(
									'First name',
									'gutena-forms'
								),
								fieldType: 'text',
								placeholder: __(
									'Enter your first name',
									'gutena-forms'
								),
							},
						],
					],
				],
				[
					'core/column',
					{ verticalAlignment: 'top' },
					[
						[
							'gutena/text-field-group',
							{
								isRequired: true,
								fieldLabel: __(
									'Field label',
									'gutena-forms'
								),
								fieldLabelContent: __(
									'Last name',
									'gutena-forms'
								),
								fieldType: 'text',
								placeholder: __(
									'Enter your last name',
									'gutena-forms'
								),
							},
						],
					],
				],
			],
		],
		[
			'core/columns',
			{ verticalAlignment: 'top', align: 'full' },
			[
				[
					'core/column',
					{ verticalAlignment: 'top' },
					[
						[
							'gutena/text-field-group',
							{
								isRequired: true,
								fieldLabel: __(
									'Field label',
									'gutena-forms'
								),
								fieldLabelContent: __(
									'Subject',
									'gutena-forms'
								),
								fieldType: 'text',
								placeholder: __(
									'Subject',
									'gutena-forms'
								),
							},
						],
					],
				],
				[
					'core/column',
					{ verticalAlignment: 'top' },
					[
						[
							'gutena/email-field-group',
							{
								isRequired: true,
								fieldLabel: __(
									'Field label',
									'gutena-forms'
								),
								fieldLabelContent: 'Email',
								fieldType: 'email',
								placeholder: __(
									'example@gmail.com',
									'gutena-forms'
								),
							},
						],
					],
				],
			],
		],
		[
			'core/columns',
			{ verticalAlignment: 'top', align: 'full' },
			[
				[
					'core/column',
					{ verticalAlignment: 'top' },
					[
						[
							'gutena/textarea-field-group',
							{
								isRequired: true,
								fieldLabel: __(
									'Field label',
									'gutena-forms'
								),
								fieldLabelContent: __(
									'Message',
									'gutena-forms'
								),
								fieldType: 'textarea',
								textAreaRows: 5,
								placeholder: __(
									'Type here',
									'gutena-forms'
								),
							},
						],
					],
				],
			],
		],
		[
			'core/columns',
			{ verticalAlignment: 'top', align: 'full' },
			[
				[
					'core/column',
					{ verticalAlignment: 'top' },
					[
						[
							'core/buttons',
							{ className: 'gutena-forms-submit-buttons' },
							[
								[
									'core/button',
									{
										text: __(
											'Submit',
											'gutena-forms'
										),
										className:
											'gutena-forms-submit-button',
										placeholder: __(
											'Submit',
											'gutena-forms'
										),
									},
								],
							],
						],
					],
				],
			],
		],
		[
			'gutena/form-confirm-msg',
			{ align: 'full' },
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
								id: 3811,
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
			{ align: 'full' },
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
								id: 3811,
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
	],
	scope: [ 'block' ],
} );

export default variations;
