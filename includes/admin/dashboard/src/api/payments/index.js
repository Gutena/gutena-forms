import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { GutenaFormsRestConfiguration } from '../index';

const { namespace, proNamespace } = GutenaFormsRestConfiguration;

export async function fetchAllPaymentGateways() {
	const response = await apiFetch( {
		path: `${ namespace }payments/get-all`,
	} );

	if ( response.payments ) {
		return Object.values( response.payments );
	}

	throw new Error( 'Failed to fetch payment gateways.' );
}

export async function togglePaymentGateway( toggle, gateway ) {
	const response = await apiFetch( {
		path: addQueryArgs( `${ namespace }payments/toggle`, {
			toggle: toggle ? 'true' : 'false',
			gateway,
		} ),
		method: 'POST',
	} );

	if ( response.success ) {
		return response;
	}

	throw new Error( response.message || 'Failed to toggle payment gateway.' );
}

export async function stripeConnect( paymentMode = 'test' ) {
	const response = await apiFetch( {
		path: `${ namespace }payments/stripe/connect`,
		method: 'POST',
		data: { payment_mode: paymentMode },
	} );

	if ( response.success && response.redirect_url ) {
		return response;
	}

	throw new Error( response.message || 'Connection failed. Please try again.' );
}

export async function stripeDisconnect() {
	const response = await apiFetch( {
		path: `${ namespace }payments/stripe/disconnect`,
		method: 'POST',
	} );

	if ( response.success ) {
		return response;
	}

	throw new Error( response.message || 'Failed to disconnect Stripe.' );
}

export async function stripeRetryWebhook() {
	const response = await apiFetch( {
		path: `${ namespace }payments/stripe/retry-webhook`,
		method: 'POST',
	} );

	if ( response.success ) {
		return response;
	}

	throw new Error( response.message || 'Failed to connect webhook.' );
}

export async function fetchStripeConnectNotice() {
	const response = await apiFetch( {
		path: `${ namespace }payments/stripe/connect-notice`,
	} );

	return response.notice || null;
}

export async function squareConnect( paymentMode = 'test' ) {
	const response = await apiFetch( {
		path: `${ namespace }payments/square/connect`,
		method: 'POST',
		data: { payment_mode: paymentMode },
	} );

	if ( response.success && response.redirect_url ) {
		return response;
	}

	throw new Error( response.message || 'Connection failed. Please try again.' );
}

export async function squareDisconnect() {
	const response = await apiFetch( {
		path: `${ namespace }payments/square/disconnect`,
		method: 'POST',
	} );

	if ( response.success ) {
		return response;
	}

	throw new Error( response.message || 'Failed to disconnect Square.' );
}

export async function fetchSquareConnectNotice() {
	const response = await apiFetch( {
		path: `${ namespace }payments/square/connect-notice`,
	} );

	return response.notice || null;
}

export async function fetchSquareConnectionStatus() {
	const response = await apiFetch( {
		path: `${ namespace }payments/square/status`,
	} );

	return response || null;
}

export async function fetchAllPaymentEntries() {
	const response = await apiFetch( {
		path: `${ namespace }payments/entries/get-all`,
	} );

	if ( response.success && Array.isArray( response.payments ) ) {
		return response.payments;
	}

	throw new Error( response.message || 'Failed to fetch payment entries.' );
}

export async function fetchEntryPayment( entryId ) {
	const response = await apiFetch( {
		path: addQueryArgs( `${ namespace }entry/payment`, {
			id: entryId,
		} ),
	} );

	if ( response.success && response.payment ) {
		return response.payment;
	}

	throw new Error( response.message || 'Failed to fetch entry payment.' );
}

export async function refundEntryPayment( entryId, amountCents, notes = '' ) {
	const response = await apiFetch( {
		path: `${ proNamespace }entry/payment/refund`,
		method: 'POST',
		data: {
			id: entryId,
			amount: amountCents,
			notes,
		},
	} );

	if ( response.success ) {
		return response;
	}

	throw new Error( response.message || 'Refund failed. Please try again.' );
}

