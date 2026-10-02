import { useCallback, useMemo, useState } from '@wordpress/element';
import {
	cloneConfirmation,
	confirmationMatchesGlobalDefaults,
	createSeedConfirmation,
	getGlobalConfirmationDefaults,
	getGlobalConfirmationDefaultsForEditor,
	persistFormConfirmation,
	resolveFormConfirmationState,
} from './form-confirmation-utils';

export const useFormConfirmationModal = ( settings, setAttributes, legacyAttrs ) => {
	const resolved = resolveFormConfirmationState( settings, legacyAttrs );
	const confirmationDefaults = getGlobalConfirmationDefaultsForEditor();
	const [ isModalOpen, setIsModalOpen ] = useState( false );
	const [ pendingFirstEnable, setPendingFirstEnable ] = useState( false );
	const [ draftConfirmation, setDraftConfirmation ] = useState( null );
	const [ initialFocusField, setInitialFocusField ] = useState( null );

	const displayEnabled = pendingFirstEnable ? true : resolved.enabled;
	const modalConfirmation = draftConfirmation ?? resolved.confirmation;

	const openFormConfirmationModal = useCallback(
		( focusField = null ) => {
			setDraftConfirmation( cloneConfirmation( resolved.confirmation ) );
			setInitialFocusField( focusField );
			setIsModalOpen( true );
		},
		[ resolved.confirmation ]
	);

	const closeFormConfirmationModal = useCallback( () => {
		if ( pendingFirstEnable ) {
			setPendingFirstEnable( false );
		}

		setDraftConfirmation( null );
		setInitialFocusField( null );
		setIsModalOpen( false );
	}, [ pendingFirstEnable ] );

	const saveFormConfirmation = useCallback(
		( confirmation ) => {
			const matchesGlobal = confirmationMatchesGlobalDefaults(
				confirmation,
				getGlobalConfirmationDefaults()
			);

			persistFormConfirmation(
				setAttributes,
				settings,
				{
					enabled: true,
					hasSavedConfig: true,
					defaultSettings: matchesGlobal,
					...confirmation,
				},
				{ syncLegacy: true }
			);
			setPendingFirstEnable( false );
			setDraftConfirmation( null );
			setInitialFocusField( null );
			setIsModalOpen( false );
		},
		[ setAttributes, settings ]
	);

	const handleToggle = useCallback(
		( enabled ) => {
			if ( enabled ) {
				if ( ! resolved.hasSavedConfig ) {
					setPendingFirstEnable( true );
					setDraftConfirmation(
						createSeedConfirmation(
							confirmationDefaults,
							resolved.confirmation
						)
					);
					setInitialFocusField( null );
					setIsModalOpen( true );
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
					hasSavedConfig: true,
				},
				{ resetLegacy: true }
			);
		},
		[
			confirmationDefaults,
			resolved.confirmation,
			resolved.hasSavedConfig,
			setAttributes,
			settings,
		]
	);

	const handleConfigure = useCallback( () => {
		openFormConfirmationModal( null );
	}, [ openFormConfirmationModal ] );

	const contextValue = useMemo(
		() => ( {
			isEnabled: resolved.enabled,
			displayEnabled,
			confirmation: resolved.confirmation,
			confirmationDefaults,
			isModalOpen,
			modalConfirmation,
			initialFocusField,
			openFormConfirmationModal,
			closeFormConfirmationModal,
			saveFormConfirmation,
			handleToggle,
			handleConfigure,
		} ),
		[
			confirmationDefaults,
			displayEnabled,
			handleConfigure,
			handleToggle,
			initialFocusField,
			isModalOpen,
			modalConfirmation,
			openFormConfirmationModal,
			closeFormConfirmationModal,
			resolved.confirmation,
			resolved.enabled,
			saveFormConfirmation,
		]
	);

	return {
		contextValue,
	};
};
