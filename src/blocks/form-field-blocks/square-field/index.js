import { registerBlockType } from '@wordpress/blocks';
import { addFilter } from '@wordpress/hooks';
import domReady from '@wordpress/dom-ready';
import { select } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { formHasPaymentField, getFormClientId } from '../../../shared/payments/stripe-field-utils';
import { isSquareGatewayEnabled } from '../../../shared/payments/square-field-utils';
import Edit from './edit';
import Save from './save';
import Icon from './icon';
import metadata from './block.json';
import './editor.scss';

addFilter(
	'blocks.registerBlockType',
	'gutena-forms/square-field-inserter',
	( settings, name ) => {
		if ( 'gutena/square-field' !== name ) {
			return settings;
		}

		settings.icon = Icon;

		if ( ! isSquareGatewayEnabled() ) {
			settings.supports = {
				...settings.supports,
				inserter: false,
			};
		}

		return settings;
	}
);

registerBlockType( metadata, {
	icon: Icon,
	edit: Edit,
	save: Save,
} );

domReady( () => {
	if ( typeof wp === 'undefined' || ! wp.data?.dispatch ) {
		return;
	}

	wp.data.dispatch( 'core/editor' )?.unlockPostSaving?.( 'gutena-square-field-validation' );

	const editPostDispatch = wp.data.dispatch( 'core/edit-post' );

	if ( isSquareGatewayEnabled() ) {
		editPostDispatch?.showBlockTypes?.( [ 'gutena/square-field' ] );
		return;
	}

	editPostDispatch?.hideBlockTypes?.( [ 'gutena/square-field' ] );
} );

addFilter(
	'blockEditor.__experimentalCanInsertBlockType',
	'gutena-forms/limit-square-field',
	( canInsert, blockType, rootClientId ) => {
		if ( 'gutena/square-field' !== blockType.name || ! canInsert ) {
			return canInsert;
		}

		if ( ! isSquareGatewayEnabled() ) {
			return false;
		}

		let formClientId = rootClientId;
		const rootBlock = select( blockEditorStore ).getBlock( rootClientId );
		if ( rootBlock?.name !== 'gutena/forms' ) {
			formClientId = getFormClientId( rootClientId );
		}

		if ( ! formClientId ) {
			return canInsert;
		}

		if ( formHasPaymentField( formClientId ) ) {
			// eslint-disable-next-line no-alert
			window.alert( __( 'Payment field already exists.', 'gutena-forms' ) );
			return false;
		}

		return canInsert;
	}
);
