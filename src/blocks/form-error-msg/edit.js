import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';

export default function edit( { context } ) {
	const ERROR_MESSAGE_GROUP = [
		[
			'core/group',
			{},
			[
				[
					'core/paragraph',
					{
						placeholder: 'Error message goes here...',
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
	const confirmationEnabled = !! formConfirmation?.enabled;

	if ( confirmationEnabled ) {
		const globalDefaults =
			'undefined' !== typeof gutenaFormsBlock &&
			gutenaFormsBlock?.form_confirmation_defaults
				? gutenaFormsBlock.form_confirmation_defaults
				: {};

		const message =
			formConfirmation.errorMessage ||
			globalDefaults.errorMessage ||
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
				template={ ERROR_MESSAGE_GROUP }
				allowedBlocks={ ALLOWED_BLOCKS }
			/>
		</div>
	);
}
