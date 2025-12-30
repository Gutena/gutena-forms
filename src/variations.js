/**
 * WordPress dependencies
 */
import { Icon } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import successIcon from './success-tick.svg';
import errorIcon from './error.svg';
import ExistingFormsIcon from './existing-forms/icon';

/** @typedef {import('@wordpress/blocks').WPBlockVariation} WPBlockVariation */
/**
 * Template option choices for predefined forms layouts.
 *
 * @type {WPBlockVariation[]}
 */
const variations = [
	{
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
		icon: (
			<Icon
				icon={
					<svg
						width="250"
						height="150"
						viewBox="0 0 534 353"
						fill="none"
						xmlns="http://www.w3.org/2000/svg"
					>
						<rect
							x="0.315796"
							y="0.842041"
							width="533.684"
							height="351.411"
							fill="white"
						/>
						<rect
							x="40.5"
							y="50.5"
							width="454"
							height="41"
							fill="white"
							stroke="#7090E5"
						/>
						<rect
							opacity="0.7"
							x="59.2913"
							y="68.5901"
							width="37.7087"
							height="4"
							rx="2"
							fill="#7090E5"
						/>
						<rect
							x="40.5"
							y="120.5"
							width="454"
							height="41"
							fill="white"
							stroke="#7090E5"
						/>
						<rect
							opacity="0.7"
							x="59.2913"
							y="138.59"
							width="37.7087"
							height="4"
							rx="2"
							fill="#7090E5"
						/>
						<rect
							x="40.5"
							y="190.5"
							width="454"
							height="41"
							fill="white"
							stroke="#7090E5"
						/>
						<rect
							opacity="0.7"
							x="59.2913"
							y="208.59"
							width="37.7087"
							height="4"
							rx="2"
							fill="#7090E5"
						/>
						<rect
							x="40"
							y="267"
							width="147"
							height="45"
							fill="#7090E5"
						/>
					</svg>
				}
			/>
		),
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
	},
	{
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
		icon: (
			<Icon
				icon={
					<svg
						width="250"
						height="150"
						viewBox="0 0 534 352"
						fill="none"
						xmlns="http://www.w3.org/2000/svg"
					>
						<rect width="533.684" height="351.411" fill="white" />
						<rect
							x="39.9342"
							y="78.408"
							width="454.5"
							height="0.5"
							fill="white"
							stroke="#7090E5"
							strokeWidth="0.5"
						/>
						<rect
							opacity="0.7"
							x="51.9755"
							y="54.748"
							width="37.7087"
							height="4"
							rx="2"
							fill="#7090E5"
						/>
						<rect
							x="39.9342"
							y="148.66"
							width="454.5"
							height="0.5"
							fill="white"
							stroke="#7090E5"
							strokeWidth="0.5"
						/>
						<rect
							opacity="0.7"
							x="51.9755"
							y="125"
							width="37.7087"
							height="4"
							rx="2"
							fill="#7090E5"
						/>
						<rect
							x="39.9342"
							y="218.66"
							width="454.5"
							height="0.5"
							fill="white"
							stroke="#7090E5"
							strokeWidth="0.5"
						/>
						<rect
							opacity="0.7"
							x="51.9755"
							y="195"
							width="37.7087"
							height="4"
							rx="2"
							fill="#7090E5"
						/>
						<rect
							x="39"
							y="267"
							width="147"
							height="45"
							fill="#7090E5"
						/>
					</svg>
				}
			/>
		),
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
	},
	{
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
		icon: (
			<Icon
				icon={
					<svg
						width="250"
						height="150"
						viewBox="0 0 534 352"
						fill="none"
						xmlns="http://www.w3.org/2000/svg"
					>
						<rect width="533.684" height="351.411" fill="white" />
						<rect
							x="40.5"
							y="49.5"
							width="219.043"
							height="41"
							fill="white"
							stroke="#7090E5"
						/>
						<rect
							opacity="0.7"
							x="57.3254"
							y="67.748"
							width="34.4297"
							height="4"
							rx="2"
							fill="#7090E5"
						/>
						<rect
							x="40.5"
							y="116.5"
							width="219.043"
							height="41"
							fill="white"
							stroke="#7090E5"
						/>
						<rect
							opacity="0.7"
							x="57.3254"
							y="134.748"
							width="34.4297"
							height="4"
							rx="2"
							fill="#7090E5"
						/>
						<rect
							x="40.5"
							y="186.5"
							width="219.043"
							height="41"
							fill="white"
							stroke="#7090E5"
						/>
						<rect
							opacity="0.7"
							x="57.3254"
							y="204.748"
							width="34.4297"
							height="4"
							rx="2"
							fill="#7090E5"
						/>
						<rect
							x="282.457"
							y="49.5"
							width="219.043"
							height="41"
							fill="white"
							stroke="#7090E5"
						/>
						<rect
							opacity="0.7"
							x="299.282"
							y="67.748"
							width="34.4297"
							height="4"
							rx="2"
							fill="#7090E5"
						/>
						<rect
							x="282.457"
							y="116.5"
							width="219.043"
							height="41"
							fill="white"
							stroke="#7090E5"
						/>
						<rect
							opacity="0.7"
							x="299.282"
							y="134.748"
							width="34.4297"
							height="4"
							rx="2"
							fill="#7090E5"
						/>
						<rect
							x="282.457"
							y="186.5"
							width="219.043"
							height="41"
							fill="white"
							stroke="#7090E5"
						/>
						<rect
							opacity="0.7"
							x="299.282"
							y="204.748"
							width="34.4297"
							height="4"
							rx="2"
							fill="#7090E5"
						/>
						<rect
							x="39"
							y="259"
							width="147"
							height="45"
							fill="#7090E5"
						/>
					</svg>
				}
			/>
		),
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
	},
	{
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
		icon: (
			<Icon
				icon={
					<svg
						width="250"
						height="150"
						viewBox="0 0 535 352"
						fill="none"
						xmlns="http://www.w3.org/2000/svg"
					>
						<rect
							x="0.684204"
							y="0.157959"
							width="533.684"
							height="351.411"
							fill="white"
						/>
						<rect
							x="40.25"
							y="78.25"
							width="211.11"
							height="0.5"
							fill="white"
							stroke="#7090E5"
							strokeWidth="0.5"
						/>
						<rect
							opacity="0.7"
							x="52.1769"
							y="54.906"
							width="36.2707"
							height="4"
							rx="2"
							fill="#7090E5"
						/>
						<rect
							x="282.64"
							y="77.344"
							width="211.11"
							height="0.5"
							fill="white"
							stroke="#7090E5"
							strokeWidth="0.5"
						/>
						<rect
							opacity="0.7"
							x="294.567"
							y="54"
							width="36.2707"
							height="4"
							rx="2"
							fill="#7090E5"
						/>
						<rect
							x="40.25"
							y="154.25"
							width="211.11"
							height="0.5"
							fill="white"
							stroke="#7090E5"
							strokeWidth="0.5"
						/>
						<rect
							opacity="0.7"
							x="52.1769"
							y="130.158"
							width="36.2707"
							height="4"
							rx="2"
							fill="#7090E5"
						/>
						<rect
							x="282.64"
							y="153.344"
							width="211.11"
							height="0.5"
							fill="white"
							stroke="#7090E5"
							strokeWidth="0.5"
						/>
						<rect
							opacity="0.7"
							x="294.567"
							y="129.252"
							width="36.2707"
							height="4"
							rx="2"
							fill="#7090E5"
						/>
						<rect
							x="40.25"
							y="229.25"
							width="211.11"
							height="0.5"
							fill="white"
							stroke="#7090E5"
							strokeWidth="0.5"
						/>
						<rect
							opacity="0.7"
							x="52.1769"
							y="205.158"
							width="36.2707"
							height="4"
							rx="2"
							fill="#7090E5"
						/>
						<rect
							x="282.64"
							y="228.344"
							width="211.11"
							height="0.5"
							fill="white"
							stroke="#7090E5"
							strokeWidth="0.5"
						/>
						<rect
							opacity="0.7"
							x="294.567"
							y="204.252"
							width="36.2707"
							height="4"
							rx="2"
							fill="#7090E5"
						/>
						<rect
							x="39.6842"
							y="267.158"
							width="147"
							height="45"
							fill="#7090E5"
						/>
					</svg>
				}
			/>
		),
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
	},
];

if ( ! gutenaFormsBlock.is_gutena_forms_post_type && gutenaFormsBlock.forms_available ) {
	variations.push(
		{
			name: 'existing-forms',
			title: __( 'Existing Forms', 'gutena-forms' ),
			description: __( 'Use a form you have already created.', 'gutena-forms' ),
			attributes: {},
			icon: <ExistingFormsIcon />,
			innerBlocks: [
				[ 'gutena/existing-forms' ]
			],
			scope: [ 'block' ],
		}
	);
}

export default variations;
