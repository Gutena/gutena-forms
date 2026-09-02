/**
 * Frontend Square card field initialization and payment processing.
 *
 * @package Gutena Forms
 */

( function () {
	'use strict';

	const CURRENCY_SYMBOLS = {
		USD: '$',
		EUR: '€',
		GBP: '£',
		AUD: '$',
		CAD: '$',
		INR: '₹',
		BDT: '৳',
		JPY: '¥',
		BRL: 'R$',
		MYR: 'RM',
		SGD: '$',
		HKD: '$',
		NZD: '$',
		MXN: '$',
		TWD: '$',
		CHF: 'CHF',
		TRY: '₺',
		THB: '฿',
		ILS: '₪',
		KRW: '₩',
		AED: 'د.إ',
		SAR: 'ر.س',
		PLN: 'zł',
		CZK: 'Kč',
	};

	function formatAmount( currency, amount ) {
		const symbol = CURRENCY_SYMBOLS[ currency ] || `${ currency } `;
		return `${ symbol }${ Number( amount ).toFixed( 2 ) }`;
	}

	function updateAmountHint( root ) {
		const hintEl = root.querySelector( '.gutena-forms-square-payment__amount-hint' );
		if ( ! hintEl ) {
			return;
		}

		const paymentType = root.getAttribute( 'data-square-payment-type' ) || 'one_time';
		if ( 'subscription' === paymentType ) {
			return;
		}

		const amountType = root.getAttribute( 'data-square-amount-type' ) || 'fixed';
		const currency = root.getAttribute( 'data-square-currency' ) || 'USD';
		const fixedAmount = parseFloat( root.getAttribute( 'data-square-fixed-amount' ) || '0' );
		const variableField = root.getAttribute( 'data-square-variable-amount-field' ) || '';

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

	function bindAmountListeners( root ) {
		updateAmountHint( root );

		const variableField = root.getAttribute( 'data-square-variable-amount-field' ) || '';
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

	async function initializeSquareFields() {
		const squareRoots = document.querySelectorAll( '.wp-block-gutena-square-field:not([data-square-initialized])' );
		if ( ! squareRoots.length ) {
			return;
		}

		if ( typeof window.Square === 'undefined' ) {
			// If SDK script is still loading, wait a moment.
			setTimeout( initializeSquareFields, 200 );
			return;
		}

		for ( const root of squareRoots ) {
			root.setAttribute( 'data-square-initialized', 'true' );
			bindAmountListeners( root );
			await initSquareField( root );
		}
	}

	async function initSquareField( root ) {
		const fieldId = root.getAttribute( 'data-square-field-id' ) || 'square_payment';
		const appId = root.getAttribute( 'data-square-application-id' );
		const locationId = root.getAttribute( 'data-square-location-id' );
		const container = root.querySelector( '.gutena-forms-square-payment__container' ) || document.getElementById( 'gutena-square-payment-' + fieldId );
		const errorEl = root.querySelector( '.gutena-forms-square-payment__error' );
		const tokenInput = root.querySelector( 'input[name="' + fieldId + '_payment_token"]' ) || root.querySelector( '.gutena-forms-square-payment__token-input' );

		if ( ! container ) {
			return;
		}

		if ( ! locationId ) {
			if ( errorEl ) {
				errorEl.textContent = 'Square Payment: No Business Location selected. Please choose a location in Gutena Forms → Settings → Square.';
				errorEl.style.display = 'block';
			}
			return;
		}

		if ( ! appId ) {
			if ( errorEl ) {
				errorEl.textContent = 'Square Payment: Missing Square Application ID.';
				errorEl.style.display = 'block';
			}
			return;
		}

		try {
			const payments = window.Square.payments( appId, locationId );
			const card = await payments.card();
			await card.attach( container );

			root._gutenaSquareCard = card;
			root.gutenaSquare = {
				card,
				fieldId,
				errorEl,
				tokenInput,
				clear() {
					if ( tokenInput ) {
						tokenInput.value = '';
					}
					if ( errorEl ) {
						errorEl.textContent = '';
						errorEl.style.display = 'none';
					}
				},
				setError( msg ) {
					if ( errorEl ) {
						errorEl.textContent = msg || '';
						errorEl.style.display = msg ? 'block' : 'none';
					}
				},
			};
		} catch ( error ) {
			console.error( 'Square payment card initialization failed:', error );
			if ( errorEl ) {
				let msg = ( error && error.message ) ? error.message : 'Could not load payment card input.';
				if ( window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1' ) {
					msg += ' (Note: Square Web Payments SDK requires a secure HTTPS connection or localhost)';
				}
				errorEl.textContent = msg;
				errorEl.style.display = 'block';
			}
		}
	}

	async function processBeforeSubmit( form, formData ) {
		const root = form.querySelector( '.wp-block-gutena-square-field' );
		if ( ! root ) {
			return;
		}

		if ( ! root._gutenaSquareCard ) {
			throw new Error( 'Square card input is not initialized. Please check your Square connection.' );
		}

		const fieldId = root.getAttribute( 'data-square-field-id' ) || 'square_payment';
		const errorEl = root.querySelector( '.gutena-forms-square-payment__error' );
		const tokenInput = root.querySelector( 'input[name="' + fieldId + '_payment_token"]' ) || root.querySelector( '.gutena-forms-square-payment__token-input' );

		if ( errorEl ) {
			errorEl.textContent = '';
			errorEl.style.display = 'none';
		}

		const tokenResult = await root._gutenaSquareCard.tokenize();
		if ( tokenResult.status === 'OK' && tokenResult.token ) {
			if ( tokenInput ) {
				tokenInput.value = tokenResult.token;
			}
			formData.set( fieldId + '_payment_token', tokenResult.token );

			const configInput = root.querySelector( 'input[name="' + fieldId + '_square_config"]' );
			if ( configInput && configInput.value ) {
				formData.set( fieldId + '_square_config', configInput.value );
			}
		} else {
			let errorMessage = 'Failed to process card. Please check your card details.';
			if ( tokenResult.errors && tokenResult.errors.length ) {
				errorMessage = tokenResult.errors.map( function ( err ) {
					return err.message;
				} ).join( ' ' );
			}
			if ( errorEl ) {
				errorEl.textContent = errorMessage;
				errorEl.style.display = 'block';
			}
			throw new Error( errorMessage );
		}
	}

	window.gutenaFormsSquare = {
		hasPaymentField( form ) {
			return !! form?.querySelector?.( '.wp-block-gutena-square-field' );
		},
		processBeforeSubmit,
	};

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initializeSquareFields );
	} else {
		initializeSquareFields();
	}
} )();
