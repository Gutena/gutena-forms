import {
	InnerBlocks,
	useBlockEditingMode,
	useBlockProps,
} from '@wordpress/block-editor';
import { useFormConfirmationEditor } from '../form/settings/form-confirmation-editor-context';
import { useParentFormConfirmation } from '../../shared/hooks/use-parent-form-confirmation';
import FormNoticePreview from '../../shared/components/FormNoticePreview';

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

const ALLOWED_BLOCKS = [
	'core/columns',
	'core/group',
	'core/image',
	'core/paragraph',
	'core/social-links',
	'core/embed',
];

export default function edit( { clientId } ) {
	const blockProps = useBlockProps();
	const parentConfirmation = useParentFormConfirmation( clientId );
	const { openFormConfirmationModal } = useFormConfirmationEditor();
	const isConfirmationEnabled = !! parentConfirmation?.isConfirmationEnabled;

	useBlockEditingMode( isConfirmationEnabled ? 'disabled' : 'default' );

	if ( isConfirmationEnabled ) {
		return (
			<FormNoticePreview
				blockProps={ blockProps }
				variant="error"
				previewHtml={ parentConfirmation.errorMessage }
				onActivate={ () => openFormConfirmationModal( 'errorMessage' ) }
			/>
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
