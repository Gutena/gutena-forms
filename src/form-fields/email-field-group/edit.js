import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';

const Edit = ( { attributes } ) => {
	const blockProps = useBlockProps();

	const FORM_GROUP = [
		[
			'core/group',
			{
				layout: {
					type: 'flex',
					orientation: 'vertical',
					className: 'gutena-field-group-row',
				},
			},
			[
				[
					'core/heading',
					{
						level: 3,
						content: attributes.fieldLabelContent,
						placeholder: attributes.fieldLabel,
						className: 'heading-input-label-gutena',
					},
				],
				[
					'gutena/form-field',
					{
						fieldType: attributes.fieldType,
						fieldName: attributes.fieldLabelContent,
						nameAttr:
							'' === attributes.fieldLabelContent
								? ''
								: attributes.fieldLabelContent
									.toLowerCase()
									.replace( / /g, '_' ),
						placeholder: attributes.placeholder,
						textAreaRows: attributes.textAreaRows,
						isRequired: attributes.isRequired,
						lock: {
							remove: true,
							move: true,
						}
					},
				],
			],
		],
		[
			'core/paragraph',
			{
				className: 'gutena-forms-field-error-msg',
				lock: {
					remove: true,
					move: true,
				}
			},
		],
	];

	const ALLOWED_BLOCKS = [
		'core/columns',
		'core/group',
		'core/image',
		'core/paragraph',
	];

	return (
		<div { ...blockProps }>
			<InnerBlocks
				template={ FORM_GROUP }
				allowedBlocks={ ALLOWED_BLOCKS }
			/>
		</div>
	);
};

export default Edit;
