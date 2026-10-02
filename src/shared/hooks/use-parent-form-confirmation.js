import { useSelect } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { resolveFormConfirmationState } from '../../blocks/form/settings/form-confirmation-utils';

export const useParentFormConfirmation = ( clientId ) =>
	useSelect(
		( select ) => {
			const { getBlockParentsByBlockName, getBlock } =
				select( blockEditorStore );
			const parents = getBlockParentsByBlockName(
				clientId,
				'gutena/forms',
				true
			);

			if ( ! parents?.length ) {
				return null;
			}

			const parentBlock = getBlock( parents[ 0 ] );

			if ( ! parentBlock ) {
				return null;
			}

			const parentSettings = parentBlock.attributes?.settings || {};
			const legacyAttrs = {
				formID: parentBlock.attributes?.formID,
				afterSubmitAction: parentBlock.attributes?.afterSubmitAction,
				afterSubmitHide: parentBlock.attributes?.afterSubmitHide,
				redirectUrl: parentBlock.attributes?.redirectUrl,
			};
			const resolved = resolveFormConfirmationState(
				parentSettings,
				legacyAttrs
			);
			const defaults = resolved.defaults;

			return {
				isConfirmationEnabled: resolved.enabled,
				successMessage:
					resolved.confirmation.successMessage ||
					defaults.successMessage ||
					'',
				errorMessage:
					resolved.confirmation.errorMessage ||
					defaults.errorMessage ||
					'',
				defaults,
			};
		},
		[ clientId ]
	);
