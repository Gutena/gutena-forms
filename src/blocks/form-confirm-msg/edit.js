import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';

export default function edit( { context } ) {
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

	const formConfirmation = context?.[ 'gutena-forms/formConfirmation' ];
	const confirmationEnabled =
		!! formConfirmation?.enabled && 'success' === formConfirmation?.type;

	// When Form Confirmation manages the message, preview it here instead of
	// the inline blocks. The content is configured in the Form Confirmation
	// modal on the parent form block.
	if ( confirmationEnabled ) {
		const globalDefaults =
			'undefined' !== typeof gutenaFormsBlock &&
			gutenaFormsBlock?.form_confirmation_defaults
				? gutenaFormsBlock.form_confirmation_defaults
				: {};

		const message =
			formConfirmation.successMessage ||
			globalDefaults.successMessage ||
			'';

		return (
			<div { ...blockProps }>
				<div className="wp-block-gutena-form-confirm-msg__preview">
					<div
						className="gutena-forms-confirmation-preview"
						dangerouslySetInnerHTML={ { __html: message } }
					/>
					<p className="gutena-forms-confirmation-preview__hint">
						{ __(
							'Managed by the Form Confirmation settings on the form block.',
							'gutena-forms'
						) }
					</p>
				</div>
			</div>
		);
	}

	return (
		<div { ...blockProps }>
			<InnerBlocks
				template={ CONFIRMATION_MESSAGE_GROUP }
				allowedBlocks={ ALLOWED_BLOCKS }
			/>
		</div>
	);
}
