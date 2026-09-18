import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, PanelBody, ToggleControl } from '@wordpress/components';
import EmailNotificationsModal from './email-notifications-modal';
import {
	cloneNotifications,
	createSeedNotification,
	persistEmailNotifications,
	resolveEmailNotificationsState,
} from './email-notifications-utils';

const EmailNotificationsSettings = ( {
	settings,
	setAttributes,
	legacyAttrs,
	formFields = [],
	textFieldOptions = [],
} ) => {
	const resolved = resolveEmailNotificationsState( settings, legacyAttrs );
	const notificationDefaults = resolved.defaults;
	const [ isModalOpen, setIsModalOpen ] = useState( false );
	const [ pendingFirstEnable, setPendingFirstEnable ] = useState( false );
	const [ draftNotifications, setDraftNotifications ] = useState( null );

	const displayEnabled = pendingFirstEnable ? true : resolved.enabled;
	const modalNotifications =
		draftNotifications ?? resolved.notifications;

	const openModal = ( notifications ) => {
		setDraftNotifications( cloneNotifications( notifications ) );
		setIsModalOpen( true );
	};

	const handleToggle = ( enabled ) => {
		if ( enabled ) {
			if ( ! resolved.hasSavedConfig ) {
				setPendingFirstEnable( true );
				openModal( [
					createSeedNotification( notificationDefaults ),
				] );
				return;
			}

			persistEmailNotifications( setAttributes, settings, {
				enabled: true,
				hasSavedConfig: true,
			} );
			return;
		}

		setPendingFirstEnable( false );
		persistEmailNotifications( setAttributes, settings, {
			enabled: false,
			hasSavedConfig: resolved.hasSavedConfig,
		} );
	};

	const handleConfigure = () => {
		openModal( resolved.notifications );
	};

	const handleModalSave = ( notifications ) => {
		persistEmailNotifications( setAttributes, settings, {
			enabled: true,
			hasSavedConfig: true,
			notifications,
		} );
		setPendingFirstEnable( false );
		setDraftNotifications( null );
		setIsModalOpen( false );
	};

	const handleModalClose = () => {
		if ( pendingFirstEnable ) {
			setPendingFirstEnable( false );
		}

		setDraftNotifications( null );
		setIsModalOpen( false );
	};

	return (
		<>
			<PanelBody
				title={ __( 'Email Notifications', 'gutena-forms' ) }
				initialOpen={ true }
				className="gutena-forms-email-notifications-panel"
			>
				<div className="gutena-forms-email-notifications-panel__content">
					<ToggleControl
						className="gutena-forms-email-notifications-panel__toggle"
						label={ __( 'Enable Email Notifications', 'gutena-forms' ) }
						checked={ displayEnabled }
						onChange={ handleToggle }
					/>
					{ displayEnabled && (
						<Button
							variant="secondary"
							className="gutena-forms-email-notifications-panel__configure"
							onClick={ handleConfigure }
						>
							{ __( 'Configure', 'gutena-forms' ) }
						</Button>
					) }
				</div>
			</PanelBody>

			<EmailNotificationsModal
				isOpen={ isModalOpen }
				initialNotifications={ modalNotifications }
				notificationDefaults={ notificationDefaults }
				formFields={ formFields }
				textFieldOptions={ textFieldOptions }
				onSave={ handleModalSave }
				onClose={ handleModalClose }
			/>
		</>
	);
};

export default EmailNotificationsSettings;
