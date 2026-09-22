import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { Link } from 'react-router';
import { BlackCrown as Crown } from '../../icons/crown';

const FormReadyActions = ( {
	onPreview,
	onContinue,
	onUpgrade,
	continueLoading,
	canUse,
	canPreview = true,
	proRequired,
} ) => (
	<div className="gutena-forms-form-ready__actions">
		{ canPreview && (
			<Button
				variant="secondary"
				className="gutena-forms-form-ready__action-preview"
				onClick={ onPreview }
			>
				{ __( 'Preview Form', 'gutena-forms' ) }
			</Button>
		) }

		<Button
			as={ Link }
			to="/settings/templates"
			variant="secondary"
			className="gutena-forms-form-ready__action-change"
		>
			{ __( 'Change Template', 'gutena-forms' ) }
		</Button>

		{ canUse && (
			<Button
				variant="primary"
				className="gutena-forms-form-ready__action-continue"
				onClick={ onContinue }
				isBusy={ continueLoading }
				disabled={ continueLoading }
			>
				{ __( 'Continue', 'gutena-forms' ) }
			</Button>
		) }

		{ proRequired && (
			<Button
				variant="primary"
				className="gutena-forms-form-ready__action-upgrade"
				onClick={ onUpgrade }
			>
				<Crown />
				{ __( 'Upgrade to Pro', 'gutena-forms' ) }
			</Button>
		) }
	</div>
);

export default FormReadyActions;
