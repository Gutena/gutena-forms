import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

const REST_NAMESPACE = 'gutena-forms/v1/';

export async function fetchStripeSettings() {
	const response = await apiFetch( {
		path: addQueryArgs( `${ REST_NAMESPACE }settings`, {
			settings_id: 'stripe',
		} ),
	} );

	return response?.settings?.values || {};
}

export async function stripeConnect( paymentMode = 'test' ) {
	const response = await apiFetch( {
		path: `${ REST_NAMESPACE }payments/stripe/connect`,
		method: 'POST',
		data: { payment_mode: paymentMode },
	} );

	if ( response?.success && response.redirect_url ) {
		return response;
	}

	throw new Error( response?.message || 'Connection failed. Please try again.' );
}

export async function fetchSquareSettings() {
	const response = await apiFetch( {
		path: addQueryArgs( `${ REST_NAMESPACE }settings`, {
			settings_id: 'square',
		} ),
	} );

	return response?.settings?.values || {};
}

export async function squareConnect( paymentMode = 'test' ) {
	const response = await apiFetch( {
		path: `${ REST_NAMESPACE }payments/square/connect`,
		method: 'POST',
		data: { payment_mode: paymentMode },
	} );

	if ( response?.success && response.redirect_url ) {
		return response;
	}

	throw new Error( response?.message || 'Connection failed. Please try again.' );
}

export async function squareDisconnect() {
	const response = await apiFetch( {
		path: `${ REST_NAMESPACE }payments/square/disconnect`,
		method: 'POST',
	} );

	if ( response?.success ) {
		return response;
	}

	throw new Error( response?.message || 'Failed to disconnect Square.' );
}

export async function fetchSquareConnectionStatus() {
	const response = await apiFetch( {
		path: `${ REST_NAMESPACE }payments/square/status`,
	} );

	return response || null;
}

export async function stripeRetryWebhook() {
	const response = await apiFetch( {
		path: `${ REST_NAMESPACE }payments/stripe/retry-webhook`,
		method: 'POST',
	} );

	if ( response?.success ) {
		return response;
	}

	throw new Error( response?.message || 'Failed to connect webhook.' );
}
