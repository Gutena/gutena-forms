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
