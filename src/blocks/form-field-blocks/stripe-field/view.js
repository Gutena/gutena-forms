/**
 * Frontend Stripe card fields and payment processing for gutena/stripe-field.
 */

const ATTR_ROOT = 'data-gutena-stripe-payment';
const ATTR_INIT = 'data-gutena-stripe-initialized';

const CURRENCY_SYMBOLS = {
	USD: '$',
	EUR: '€',
	GBP: '£',
	AUD: '$',
	CAD: '$',
	INR: '₹',
	BDT: '৳',
};

/**
 * @param {string} currency
 * @param {number} amount
 */
function formatAmount( currency, amount ) {
	const symbol = CURRENCY_SYMBOLS[ currency ] || `${ currency } `;
	return `${ symbol }${ Number( amount ).toFixed( 2 ) }`;
}

/**
 * Resolve REST URL and nonce for Stripe payment requests.
 *
 * @param {HTMLElement|null} root Stripe payment root element.
 * @return {{ restBase: string, nonce: string, wpRestNonce: string }}
 */
function getStripeRequestContext( root ) {
	const restBase =
		root?.getAttribute( 'data-rest-url' ) ||
		( typeof gutenaFormsBlock !== 'undefined' && gutenaFormsBlock.rest_url
			? gutenaFormsBlock.rest_url
			: '/wp-json/gutena-forms/v1/' );

	const nonce =
		root?.getAttribute( 'data-form-nonce' ) ||
		( typeof gutenaFormsBlock !== 'undefined' && gutenaFormsBlock.nonce
			? gutenaFormsBlock.nonce
			: '' );

	const wpRestNonce =
		root?.getAttribute( 'data-wp-rest-nonce' ) ||
		( typeof gutenaFormsBlock !== 'undefined' && gutenaFormsBlock.rest_nonce
			? gutenaFormsBlock.rest_nonce
			: '' ) ||
		( typeof wpApiSettings !== 'undefined' && wpApiSettings.nonce
			? wpApiSettings.nonce
			: '' );

	return { restBase, nonce, wpRestNonce };
}

/**
 * @param {string} restPath
 * @param {Record<string, unknown>} body
 * @param {HTMLElement|null} root
 */
async function stripeRestRequest( restPath, body = {}, root = null ) {
	const contextRoot = root || document.querySelector( `[${ ATTR_ROOT }]` );
	const { restBase, nonce, wpRestNonce } = getStripeRequestContext( contextRoot );

	if ( ! nonce ) {
		return {
			success: false,
			message: 'Payment security token is missing. Please refresh the page and try again.',
		};
	}

	const headers = {
		'Content-Type': 'application/json',
		'X-Gutena-Nonce': nonce,
	};

	if ( wpRestNonce ) {
		headers[ 'X-WP-Nonce' ] = wpRestNonce;
	}

	const response = await fetch( `${ restBase }${ restPath }`, {
		method: 'POST',
		credentials: 'same-origin',
		headers,
		body: JSON.stringify( {
			...body,
			nonce,
		} ),
	} );

	const data = await response.json();

	if ( ! response.ok && data?.message && ! data?.success ) {
		return {
			success: false,
			message: data.message,
			code: data.code || 'rest_forbidden',
		};
	}

	return data;
}

/**
 * @param {HTMLElement} root
 */
function updateAmountHint( root ) {
	const hintEl = root.querySelector( '.gutena-forms-stripe-payment__amount-hint' );
	if ( ! hintEl ) {
		return;
	}

	const amountType = root.getAttribute( 'data-amount-type' ) || 'fixed';
	const currency = root.getAttribute( 'data-stripe-currency' ) || 'USD';
	const fixedAmount = parseFloat( root.getAttribute( 'data-fixed-amount' ) || '0' );
	const variableField = root.getAttribute( 'data-variable-amount-field' ) || '';

	if ( 'fixed' === amountType && fixedAmount > 0 ) {
		hintEl.textContent = formatAmount( currency, fixedAmount );
		return;
	}

	if ( 'variable' === amountType && variableField ) {
		const form = root.closest( 'form' );
		const sourceField = form?.querySelector( `[name="${ variableField }"]` );
		const rawValue = sourceField?.value ?? '';
		const parsed = parseFloat( rawValue );

		if ( ! Number.isNaN( parsed ) && parsed > 0 ) {
			hintEl.textContent = formatAmount( currency, parsed );
			return;
		}
	}

	hintEl.textContent = 'Complete the form to view the amount.';
}

/**
 * @param {HTMLElement} root
 */
function bindAmountListeners( root ) {
	updateAmountHint( root );

	const variableField = root.getAttribute( 'data-variable-amount-field' ) || '';
	if ( ! variableField ) {
		return;
	}

	const form = root.closest( 'form' );
	const sourceField = form?.querySelector( `[name="${ variableField }"]` );
	if ( ! sourceField ) {
		return;
	}

	sourceField.addEventListener( 'input', () => updateAmountHint( root ) );
	sourceField.addEventListener( 'change', () => updateAmountHint( root ) );
}

/**
 * @param {HTMLElement} root
 * @returns {Promise<{publishableKey: string, accountId: string}>}
 */
async function resolveStripeConfig( root ) {
	let publishableKey = root.getAttribute( 'data-stripe-publishable-key' ) || '';
	let accountId = root.getAttribute( 'data-stripe-account' ) || '';

	if ( publishableKey ) {
		return { publishableKey, accountId };
	}

	const formId = root.getAttribute( 'data-form-id' ) || '';
	if ( ! formId ) {
		return { publishableKey: '', accountId: '' };
	}

	const response = await stripeRestRequest( 'payments/stripe/public-config', {
		form_id: formId,
	}, root );

	if ( ! response?.success || ! response?.config?.publishable_key ) {
		return { publishableKey: '', accountId: '' };
	}

	publishableKey = response.config.publishable_key;
	accountId = response.config.account_id || '';

	root.setAttribute( 'data-stripe-publishable-key', publishableKey );
	root.setAttribute( 'data-stripe-account', accountId );

	return { publishableKey, accountId };
}

/**
 * @param {HTMLElement} root
 * @param {{publishableKey: string, accountId: string}} config
 */
function mountStripeElements( root, config ) {
	const fieldId = root.getAttribute( 'data-stripe-field-id' ) || 'stripe_payment';
	const errorEl = root.querySelector( '.gutena-forms-stripe-payment__error' );
	const countrySelect = root.querySelector( '.gutena-forms-stripe-payment__country' );
	const hiddenInput = root.querySelector( `input[name="${ fieldId }_payment_method"]` );
	const intentInput = root.querySelector( '.gutena-forms-stripe-payment__intent-input' );

	const numberMount = document.getElementById( `gutena-stripe-card-number-${ fieldId }` );
	const expiryMount = document.getElementById( `gutena-stripe-card-expiry-${ fieldId }` );
	const cvcMount = document.getElementById( `gutena-stripe-card-cvc-${ fieldId }` );
	const numberWrap = numberMount?.closest( '.gutena-forms-stripe-payment__input-wrap--number' );

	if ( ! numberMount || ! expiryMount || ! cvcMount ) {
		return;
	}

	const stripeOptions = config.accountId ? { stripeAccount: config.accountId } : {};
	const stripe = window.Stripe( config.publishableKey, stripeOptions );

	const elements = stripe.elements( {
		appearance: {
			theme: 'stripe',
			variables: {
				colorPrimary: '#0570de',
				colorBackground: '#ffffff',
				colorText: '#30313d',
				colorDanger: '#df1b41',
				colorTextPlaceholder: '#a3acb9',
				fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif',
				fontSizeBase: '16px',
				spacingUnit: '3px',
				borderRadius: '5px',
			},
			rules: {
				'.Input': {
					border: 'none',
					boxShadow: 'none',
					padding: '0',
					backgroundColor: 'transparent',
					lineHeight: '20px',
				},
				'.Input:focus': {
					boxShadow: 'none',
				},
				'.Label': {
					display: 'none',
				},
			},
		},
	} );

	const elementStyle = {
		base: {
			color: '#30313d',
			fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif',
			fontSize: '16px',
			fontSmoothing: 'antialiased',
			'::placeholder': {
				color: '#a3acb9',
			},
		},
		invalid: {
			color: '#df1b41',
		},
	};

	const cardNumber = elements.create( 'cardNumber', {
		style: elementStyle,
		showIcon: true,
		placeholder: '1234 1234 1234 1234',
	} );
	const cardExpiry = elements.create( 'cardExpiry', {
		style: elementStyle,
		placeholder: 'MM / YY',
	} );
	const cardCvc = elements.create( 'cardCvc', {
		style: elementStyle,
		placeholder: 'CVC',
	} );

	cardNumber.mount( numberMount );
	cardExpiry.mount( expiryMount );
	cardCvc.mount( cvcMount );

	const setError = ( message ) => {
		if ( ! errorEl ) {
			return;
		}

		errorEl.textContent = message || '';
		root.classList.toggle( 'display-error', !! message );
	};

	const handleChange = ( event ) => {
		if ( event.error ) {
			setError( event.error.message );
			return;
		}

		setError( '' );
		if ( intentInput ) {
			intentInput.value = '';
		}
	};

	const handleCardNumberChange = ( event ) => {
		if ( numberWrap ) {
			const brand = event.brand && 'unknown' !== event.brand ? event.brand : '';
			numberWrap.classList.toggle( 'has-card-brand', !! brand );
		}

		handleChange( event );
	};

	cardNumber.on( 'change', handleCardNumberChange );
	cardExpiry.on( 'change', handleChange );
	cardCvc.on( 'change', handleChange );

	root.gutenaStripe = {
		stripe,
		elements,
		cardNumber,
		cardExpiry,
		cardCvc,
		countrySelect,
		intentInput,
		fieldId,
		setError,
		createPaymentMethod: async ( billingDetails = {} ) => {
			setError( '' );

			const country = countrySelect?.value || '';
			const mergedBilling = {
				...billingDetails,
				address: {
					...( billingDetails.address || {} ),
					country: country || billingDetails.address?.country || '',
				},
			};

			const result = await stripe.createPaymentMethod( {
				type: 'card',
				card: cardNumber,
				billing_details: mergedBilling,
			} );

			if ( result.error ) {
				setError( result.error.message );
				throw result.error;
			}

			if ( hiddenInput && result.paymentMethod?.id ) {
				hiddenInput.value = result.paymentMethod.id;
			}

			return result.paymentMethod;
		},
		clear: () => {
			cardNumber.clear();
			cardExpiry.clear();
			cardCvc.clear();
			if ( numberWrap ) {
				numberWrap.classList.remove( 'has-card-brand' );
			}
			if ( countrySelect ) {
				countrySelect.selectedIndex = 0;
			}
			if ( hiddenInput ) {
				hiddenInput.value = '';
			}
			if ( intentInput ) {
				intentInput.value = '';
			}
			setError( '' );
		},
	};
}

/**
 * @param {HTMLElement} root
 */
async function initStripePaymentField( root ) {
	if ( root.getAttribute( ATTR_INIT ) === '1' ) {
		return;
	}

	bindAmountListeners( root );

	if ( typeof window.Stripe !== 'function' ) {
		return;
	}

	const config = await resolveStripeConfig( root );
	if ( ! config.publishableKey ) {
		root.setAttribute( ATTR_INIT, '1' );
		const errorEl = root.querySelector( '.gutena-forms-stripe-payment__error' );
		if ( errorEl ) {
			errorEl.textContent =
				'Payment form is unavailable. Configure your Stripe publishable key to accept payments.';
			root.classList.add( 'display-error' );
		}
		return;
	}

	root.setAttribute( ATTR_INIT, '1' );

	try {
		mountStripeElements( root, config );
	} catch ( error ) {
		const errorEl = root.querySelector( '.gutena-forms-stripe-payment__error' );
		const message =
			error?.message && /invalid api key/i.test( error.message )
				? 'Invalid Stripe publishable key. In Gutena Forms → Settings → Payment → Stripe, paste the pk_test_ key from your connected Stripe account dashboard (Developers → API keys).'
				: error?.message || 'Unable to load the payment form. Please refresh and try again.';

		if ( errorEl ) {
			errorEl.textContent = message;
			root.classList.add( 'display-error' );
		}
	}
}

/**
 * @param {HTMLFormElement} form
 * @returns {Record<string, string>}
 */
function collectFieldValues( form ) {
	/** @type {Record<string, string>} */
	const values = {};
	const formData = new FormData( form );

	formData.forEach( ( value, key ) => {
		if ( typeof value === 'string' ) {
			values[ key ] = value;
		}
	} );

	return values;
}

/**
 * @param {HTMLElement} root
 * @param {HTMLFormElement} form
 */
function resolveBillingDetails( root, form ) {
	/** @type {{email?: string, name?: string}} */
	const billing = {};

	const emailField = root.getAttribute( 'data-customer-email-field' ) || '';
	const nameField = root.getAttribute( 'data-customer-name-field' ) || '';

	if ( emailField ) {
		const emailInput = form.querySelector( `[name="${ emailField }"]` );
		if ( emailInput?.value ) {
			billing.email = emailInput.value;
		}
	}

	if ( nameField ) {
		const nameInput = form.querySelector( `[name="${ nameField }"]` );
		if ( nameInput?.value ) {
			billing.name = nameInput.value;
		}
	}

	return billing;
}

/**
 * Build Stripe field config from rendered payment block data attributes.
 *
 * @param {HTMLElement} root
 * @return {Record<string, string | number>}
 */
function collectStripeFieldConfig( root ) {
	const fieldId = root.getAttribute( 'data-stripe-field-id' ) || 'stripe_payment';
	const configInput = root.querySelector( '.gutena-forms-stripe-payment__config-input' );

	if ( configInput?.value ) {
		try {
			const parsed = JSON.parse( configInput.value );
			if ( parsed && typeof parsed === 'object' ) {
				return parsed;
			}
		} catch ( error ) {
			// Fall through to data attributes.
		}
	}

	return {
		nameAttr: fieldId,
		blockName: 'gutena/stripe-field',
		fieldType: 'stripe',
		paymentType: root.getAttribute( 'data-payment-type' ) || 'one_time',
		amountType: root.getAttribute( 'data-amount-type' ) || 'fixed',
		fixedAmount: parseFloat( root.getAttribute( 'data-fixed-amount' ) || '0' ),
		variableAmountField: root.getAttribute( 'data-variable-amount-field' ) || '',
		minimumAmount: parseFloat( root.getAttribute( 'data-minimum-amount' ) || '0' ),
		customerEmailField: root.getAttribute( 'data-customer-email-field' ) || '',
		customerNameField: root.getAttribute( 'data-customer-name-field' ) || '',
	};
}

/**
 * @param {HTMLElement} root
 * @param {string} formId
 * @param {string} fieldId
 * @param {string} paymentIntentId
 * @param {HTMLFormElement} form
 * @param {string} errorMessage
 * @returns {Promise<void>}
 */
async function logFailedPaymentAttempt( root, formId, fieldId, paymentIntentId, form, errorMessage ) {
	if ( ! paymentIntentId || ! formId ) {
		return;
	}

	try {
		await stripeRestRequest(
			'payments/stripe/log-attempt',
			{
				form_id: formId,
				stripe_field_id: fieldId,
				payment_intent_id: paymentIntentId,
				field_values: collectFieldValues( form ),
				stripe_field: collectStripeFieldConfig( root ),
				status: 'failed',
				error_message: errorMessage || '',
			},
			root
		);
	} catch ( logError ) {
		console.warn( 'Unable to log failed payment attempt', logError );
	}
}

/**
 * @param {HTMLFormElement} form
 * @param {FormData} formData
 * @returns {Promise<void>}
 */
async function processBeforeSubmit( form, formData ) {
	const root = form.querySelector( `[${ ATTR_ROOT }]` );
	if ( ! root?.gutenaStripe ) {
		return;
	}

	const { gutenaStripe } = root;
	const formId = root.getAttribute( 'data-form-id' ) || formData.get( 'formid' ) || '';
	const fieldId = gutenaStripe.fieldId || 'stripe_payment';

	if ( ! formId ) {
		throw new Error( 'Form ID is missing for payment processing.' );
	}

	const country = gutenaStripe.countrySelect?.value || '';
	if ( ! country ) {
		gutenaStripe.setError( 'Please select a country.' );
		root.classList.add( 'display-error' );
		throw new Error( 'Country is required.' );
	}

	gutenaStripe.setError( '' );
	root.classList.remove( 'display-error' );

	const intentResponse = await stripeRestRequest( 'payments/stripe/create-intent', {
		form_id: formId,
		stripe_field_id: fieldId,
		field_values: collectFieldValues( form ),
		stripe_field: collectStripeFieldConfig( root ),
	}, root );

	if ( ! intentResponse?.success || ! intentResponse?.intent?.client_secret ) {
		const message = intentResponse?.message || 'Unable to start payment. Please try again.';
		gutenaStripe.setError( message );
		root.classList.add( 'display-error' );
		throw new Error( message );
	}

	const { client_secret: clientSecret, payment_intent_id: paymentIntentId } = intentResponse.intent;
	const billingDetails = resolveBillingDetails( root, form );

	const confirmResult = await gutenaStripe.stripe.confirmCardPayment( clientSecret, {
		payment_method: {
			card: gutenaStripe.cardNumber,
			billing_details: {
				...billingDetails,
				address: {
					country,
				},
			},
		},
	} );

	if ( confirmResult.error ) {
		await logFailedPaymentAttempt(
			root,
			formId,
			fieldId,
			paymentIntentId || confirmResult.error.payment_intent?.id || '',
			form,
			confirmResult.error.message
		);
		gutenaStripe.setError( confirmResult.error.message );
		root.classList.add( 'display-error' );
		throw confirmResult.error;
	}

	const status = confirmResult.paymentIntent?.status || '';
	if ( ! [ 'succeeded', 'processing' ].includes( status ) ) {
		const message = 'Payment was not completed. Please check your card details.';
		await logFailedPaymentAttempt(
			root,
			formId,
			fieldId,
			confirmResult.paymentIntent?.id || paymentIntentId || '',
			form,
			message
		);
		gutenaStripe.setError( message );
		root.classList.add( 'display-error' );
		throw new Error( message );
	}

	const intentId = confirmResult.paymentIntent?.id || paymentIntentId || '';
	if ( gutenaStripe.intentInput ) {
		gutenaStripe.intentInput.value = intentId;
	}

	formData.set( `${ fieldId }_payment_intent`, intentId );
	formData.set( `${ fieldId }_country`, country );
}

function initAll() {
	document.querySelectorAll( `[${ ATTR_ROOT }]` ).forEach( ( root ) => {
		initStripePaymentField( root );
	} );
}

function waitForStripeAndInit( attempts = 0 ) {
	if ( typeof window.Stripe === 'function' ) {
		initAll();
		return;
	}

	if ( attempts >= 40 ) {
		initAll();
		return;
	}

	window.setTimeout( () => waitForStripeAndInit( attempts + 1 ), 250 );
}

window.gutenaFormsStripe = {
	hasPaymentField( form ) {
		return !! form?.querySelector?.( `[${ ATTR_ROOT }]` );
	},
	processBeforeSubmit,
};

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', waitForStripeAndInit );
} else {
	waitForStripeAndInit();
}

