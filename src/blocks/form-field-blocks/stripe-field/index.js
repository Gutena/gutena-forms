import { registerBlockType } from '@wordpress/blocks';
import { addFilter } from '@wordpress/hooks';
import domReady from '@wordpress/dom-ready';
import { select } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import {
	formHasStripeField,
	getFormClientId,
	isStripeGatewayEnabled,
} from '../../../shared/payments/stripe-field-utils';
import Edit from './edit';
import Save from './save';
import Icon from './icon';
import metadata from './block.json';
import './editor.scss';
import './style.scss';
addFilter(
	'blocks.registerBlockType',
	'gutena-forms/stripe-field-inserter',
	( settings, name ) => {
		if ( 'gutena/stripe-field' !== name ) {
			return settings;
		}

		if ( ! isStripeGatewayEnabled() ) {
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

	// Clear any stale save lock from the removed editor validation plugin.
	wp.data.dispatch( 'core/editor' )?.unlockPostSaving?.( 'gutena-stripe-field-validation' );

	const editPostDispatch = wp.data.dispatch( 'core/edit-post' );

	if ( isStripeGatewayEnabled() ) {
		editPostDispatch?.showBlockTypes?.( [ 'gutena/stripe-field' ] );
		return;
	}

	editPostDispatch?.hideBlockTypes?.( [ 'gutena/stripe-field' ] );
} );

addFilter(
	'blockEditor.__experimentalCanInsertBlockType',
	'gutena-forms/limit-stripe-field',
	( canInsert, blockType, rootClientId ) => {
		if ( 'gutena/stripe-field' !== blockType.name || ! canInsert ) {
			return canInsert;
		}

		if ( ! isStripeGatewayEnabled() ) {
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

		if ( formHasStripeField( formClientId ) ) {
			// eslint-disable-next-line no-alert
			window.alert( __( 'Payment field already exists.', 'gutena-forms' ) );
			return false;
		}

		return canInsert;
	}
);