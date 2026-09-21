import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, PanelBody, ToggleControl } from '@wordpress/components';
import { settings as settingsIcon } from '@wordpress/icons';
import FormConfirmationModal from './form-confirmation-modal';
import {
	cloneConfirmation,
	createSeedConfirmation,
	persistFormConfirmation,
	resolveFormConfirmationState,
} from './form-confirmation-utils';

const FormConfirmationSettings = ( {
	settings,
	setAttributes,
	legacyAttrs,
	formFields = [],
} ) => {
	const resolved = resolveFormConfirmationState( settings, legacyAttrs );
	const confirmationDefaults = resolved.defaults;
	const [ isModalOpen, setIsModalOpen ] = useState( false );
	const [ pendingFirstEnable, setPendingFirstEnable ] = useState( false );
	const [ draftConfirmation, setDraftConfirmation ] = useState( null );

	const displayEnabled = pendingFirstEnable ? true : resolved.enabled;
	const modalConfirmation = draftConfirmation ?? resolved.confirmation;

	const openModal = ( confirmation ) => {
		setDraftConfirmation( cloneConfirmation( confirmation ) );
		setIsModalOpen( true );
	};

	const handleToggle = ( enabled ) => {
		if ( enabled ) {
			if ( ! resolved.hasSavedConfig ) {
				setPendingFirstEnable( true );
				openModal(
					createSeedConfirmation(
						confirmationDefaults,
						resolved.confirmation
					)
				);
				return;
			}

			persistFormConfirmation(
				setAttributes,
				settings,
				{
					enabled: true,
					hasSavedConfig: true,
					...resolved.confirmation,
				},
				{ syncLegacy: true }
			);
			return;
		}

		setPendingFirstEnable( false );
		persistFormConfirmation(
			setAttributes,
			settings,
			{
				enabled: false,
				hasSavedConfig: resolved.hasSavedConfig,
			},
			{ resetLegacy: true }
		);
	};

	const handleConfigure = () => {
		openModal( resolved.confirmation );
	};

	const handleModalSave = ( confirmation ) => {
		persistFormConfirmation(
			setAttributes,
			settings,
			{
				enabled: true,
				hasSavedConfig: true,
				defaultSettings: false,
				...confirmation,
			},
			{ syncLegacy: true }
		);
		setPendingFirstEnable( false );
		setDraftConfirmation( null );
		setIsModalOpen( false );
	};

	const handleModalClose = () => {
		if ( pendingFirstEnable ) {
			setPendingFirstEnable( false );
		}

		setDraftConfirmation( null );
		setIsModalOpen( false );
	};

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
				formFields={ formFields }
				onSave={ handleModalSave }
				onClose={ handleModalClose }
			/>
		</>
	);
};

export default FormConfirmationSettings;
