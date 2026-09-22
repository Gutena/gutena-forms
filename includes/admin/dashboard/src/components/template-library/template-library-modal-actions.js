import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { BlackCrown as Crown } from '../../icons/crown';

const TemplateLibraryModalActions = ( {
	template,
	onPreview,
	onUseTemplate,
	onUpgrade,
	showPreviewAction = false,
} ) => {
	if ( ! template ) {
		return null;
	}

	return (
		<div className="gutena-forms-template-library__modal-actions">
			{ showPreviewAction && template.can_preview && (
				<Button
					variant="secondary"
					className="gutena-forms-template-library__action-preview"
					onClick={ onPreview }
				>
					{ __( 'Preview', 'gutena-forms' ) }
				</Button>
			) }

			{ template.can_use && (
				<Button
					variant="primary"
					className="gutena-forms-template-library__action-use"
					onClick={ onUseTemplate }
				>
					{ __( 'Use Template', 'gutena-forms' ) }
				</Button>
			) }

			{ ! template.can_use && template.is_pro && (
				<Button
					variant="primary"
					className="gutena-forms-template-library__action-upgrade"
					onClick={ onUpgrade }
				>
					<Crown />
					{ __( 'Upgrade to Pro', 'gutena-forms' ) }
				</Button>
			) }
		</div>
	);
};

export default TemplateLibraryModalActions;
