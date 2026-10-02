import { __ } from '@wordpress/i18n';
import { Button, PanelBody, ToggleControl } from '@wordpress/components';
import { settings as settingsIcon } from '@wordpress/icons';
import { useFormConfirmationEditor } from './form-confirmation-editor-context';
import FormConfirmationModal from './form-confirmation-modal';

const FormConfirmationSettings = () => {
	const {
		displayEnabled,
		confirmationDefaults,
		isModalOpen,
		modalConfirmation,
		initialFocusField,
		handleToggle,
		handleConfigure,
		closeFormConfirmationModal,
		saveFormConfirmation,
	} = useFormConfirmationEditor();

	return (
		<>
		<PanelBody

			title={ __( 'Form Confirmation', 'gutena-forms' ) }

			initialOpen={ true }

			className="gutena-forms-form-confirmation-panel"

		>

			<div className="gutena-forms-form-confirmation-panel__content">

				<ToggleControl

					className="gutena-forms-form-confirmation-panel__toggle"

					label={ __( 'Enable Form Confirmation', 'gutena-forms' ) }

					checked={ displayEnabled }

					onChange={ handleToggle }

				/>

				{ displayEnabled && (

					<Button

						variant="secondary"

						className="gutena-forms-form-confirmation-panel__configure"

						icon={ settingsIcon }

						onClick={ handleConfigure }

					>

						{ __( 'Configuration', 'gutena-forms' ) }

					</Button>

				) }

			</div>

		</PanelBody>

		<FormConfirmationModal
			isOpen={ isModalOpen }
			initialConfirmation={ modalConfirmation }
			confirmationDefaults={ confirmationDefaults }
			initialFocusField={ initialFocusField }
			onSave={ saveFormConfirmation }
			onClose={ closeFormConfirmationModal }
		/>
		</>
	);
};



export default FormConfirmationSettings;

