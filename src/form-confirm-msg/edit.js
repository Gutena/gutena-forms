import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';

export default function edit( {
	className,
	attributes,
	setAttributes,
	isSelected,
	clientId,
} ) {
	const CONFIRMATION_MESSAGE_GROUP = [
		[
			'core/group',
			{},
			[
				[
					'core/paragraph',
					{
						placeholder: 'Confirmation message goes here...',
					},
				],
			],
		],
	];

	const blockProps = useBlockProps();
	const ALLOWED_BLOCKS = [
		'core/columns',
		'core/group',
		'core/image',
		'core/paragraph',
		'core/social-links',
		'core/embed',
	];
	return (
		<div { ...blockProps }>
			<InnerBlocks
				template={ CONFIRMATION_MESSAGE_GROUP }
				allowedBlocks={ ALLOWED_BLOCKS }
			/>
		</div>
	);
}
