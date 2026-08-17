document.addEventListener("DOMContentLoaded", function(){
	const ready = () => {
		sync_hide_form_after_submit_from_embedded();
		range_slider_onchange();
		field_validation_on_input();
		form_sumbit();

		setTimeout(() => {
			//check if grecaptcha is loaded or not
			check_and_load_grecaptcha();
			check_and_load_cloudflare_turnstile();

		}, 2000);
	};

	//Check Empty
	const isEmpty = ( data ) => {
		return 'undefined' === typeof data || null === data || '' == data;
	};

	//Check class
	const hasClass = ( element, className ) => {
		return (
			( ' ' + element.className + ' ' ).indexOf( ' ' + className + ' ' ) >
			-1
		);
	};

	// Existing Forms embeds can lose hide-form-after-submit on the inner form tag (nested <form>).
	// Marker from PHP means selected form had hide enabled — copy class onto the parent form.
	const sync_hide_form_after_submit_from_embedded = () => {
		let forms = document.querySelectorAll(
			'form.wp-block-gutena-forms'
		);
		for ( let i = 0; i < forms.length; i++ ) {
			if (
				forms[ i ].querySelector(
					'[data-gutena-hide-form-after-submit]'
				) &&
				! hasClass( forms[ i ], 'hide-form-after-submit' )
			) {
				forms[ i ].classList.add( 'hide-form-after-submit' );
			}
		}
	};

	//validate Email
	const validateEmail = ( email ) => {
		return email.match(
			/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
		);
	};

	/**
	 * Validates URL / domain input.
	 * Allows: http(s)://localhost, http(s)://domain.tld, domain.tld
	 * Rejects: bare words without TLD (e.g. "google"), spaces, invalid hosts.
	 */
	const validateUrl = ( value ) => {
		if ( isEmpty( value ) ) {
			return true;
		}

		const trimmed = String( value ).trim();
		if ( /\s/.test( trimmed ) ) {
			return false;
		}

		let hostAndRest = trimmed;
		const protocolMatch = trimmed.match( /^https?:\/\/(.+)$/i );
		if ( protocolMatch ) {
			hostAndRest = protocolMatch[1];
		} else if ( trimmed.includes( '://' ) ) {
			return false;
		}

		const host = hostAndRest.split( /[/?#]/ )[0];
		const hostWithoutPort = host.split( ':' )[0];

		if ( 'localhost' === hostWithoutPort.toLowerCase() ) {
			return true;
		}

		if ( ! hostWithoutPort.includes( '.' ) ) {
			return false;
		}

		return /^([a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/.test(
			hostWithoutPort
		);
	};

	//Get parent HTML elemnet
	const getParents = ( el, query ) => {
		let parents = [];
		while ( el.parentNode !== document.body ) {
			el.matches( query ) && parents.push( el );
			el = el.parentNode;
		}
		return parents;
	};

	const resolveRecaptchaSiteKey = ( grecaptcha ) => {
		if ( isEmpty( grecaptcha ) ) {
			return '';
		}
		const type = ! isEmpty( grecaptcha.type ) ? grecaptcha.type : 'v2';
		if ( ! isEmpty( grecaptcha[ type + '_site_key' ] ) ) {
			return grecaptcha[ type + '_site_key' ];
		}
		return ! isEmpty( grecaptcha.site_key ) ? grecaptcha.site_key : '';
	};

	const getFormRecaptchaConfig = ( gutena_forms ) => {
		let type = gutena_forms.getAttribute( 'data-recaptcha-type' );
		let siteKey = gutena_forms.getAttribute( 'data-recaptcha-site-key' );

		if ( isEmpty( siteKey ) && 'undefined' !== typeof gutenaFormsBlock && ! isEmpty( gutenaFormsBlock.grecaptcha ) ) {
			type = gutenaFormsBlock.grecaptcha.type;
			siteKey = resolveRecaptchaSiteKey( gutenaFormsBlock.grecaptcha );
		}

		return {
			type: type,
			siteKey: siteKey,
		};
	};

	//enqueue recaptcha if not enqueued 
	const check_and_load_grecaptcha = () => {
		if ( 'undefined' === typeof gutenaFormsBlock || isEmpty( gutenaFormsBlock.grecaptcha ) ) {
			return;
		}

		const grecaptchaConfig = gutenaFormsBlock.grecaptcha;
		const grecaptchaSiteKey = resolveRecaptchaSiteKey( grecaptchaConfig );
		const grecaptchaType = ! isEmpty( grecaptchaConfig.type ) ? grecaptchaConfig.type : 'v2';

		if ( isEmpty( grecaptchaSiteKey ) ) {
			return;
		}

		//gutena form block
		let gutena_form_0 = document.querySelector(
			'.wp-block-gutena-forms'
		);
		if ( ! isEmpty( gutena_form_0 ) ) {
			//check if recaptcha is enabled
			let grecaptcha_enable  = gutena_form_0.querySelector(
				'input[name="recaptcha_enable"]'
			);
			if ( ! isEmpty( grecaptcha_enable ) && 0 != grecaptcha_enable.length && grecaptcha_enable.value ) {
				//check if grecaptcha is defined or not
				if ( 'undefined' === typeof grecaptcha || null === grecaptcha ) {
					//check if grecaptcha script is loading or not
					let grecaptcha_script_html = document.getElementById('google-recaptcha-js');
					if ( isEmpty( grecaptcha_script_html ) ) {
						//form script
						let gutena_forms_script_html = document.getElementById('gutena-forms-script-js');
						if ( ! isEmpty( gutena_forms_script_html ) ) {
							grecaptcha_script_html = document.createElement('script');
							grecaptcha_script_html.id = 'google-recaptcha-js';
							let grecaptcha_url = 'https://www.google.com/recaptcha/api.js';
							if ( 'v3' === grecaptchaType ) {
								grecaptcha_url += '?render=' + grecaptchaSiteKey;
							}
							grecaptcha_script_html.src = grecaptcha_url;
							//insert before form script
							document.head.insertBefore( grecaptcha_script_html, gutena_forms_script_html );
						}
					}
				}
			}
		}
	}

	/**
	 * Check and load Cloudflare Turnstile
	 * Fallback for Cloudflare Turnstile
	 * 
	 * @since 1.3.0
	 */
	const check_and_load_cloudflare_turnstile = () => {
		// function to handle turnstile success
	}

	const form_sumbit = () => {
		let submitButton = document.querySelectorAll(
			'.wp-block-gutena-forms .gutena-forms-submit-button'
		);
		if ( 0 < submitButton.length ) {
			for ( let i = 0; i < submitButton.length; i++ ) {
				submitButton[ i ].addEventListener( 'click', function ( e ) {
					e.preventDefault();
					let gutena_forms = getParents(
						this,
						'.wp-block-gutena-forms'
					);

					if ( 'undefined' === typeof gutena_forms ) {
						console.log( 'Form not defined' );
						return;
					}
					gutena_forms = gutena_forms[ 0 ];

					/*****************************
                     Form Validation :START
                     *****************************/
					let form_fields = gutena_forms.querySelectorAll(
						'.gutena-forms-field'
					);
					if ( 0 === form_fields.length ) {
						console.log( 'No input fields found' );
						return;
					}

					let formCheck = true;
					let error_field = form_fields[ 0 ];

					//Check for validation
					for ( let j = 0; j < form_fields.length; j++ ) {
						//Validate form field
						if ( false === field_validation( form_fields[ j ] ) ) {
							//get first error field for scroll into view
							if ( true === formCheck ) {
								error_field = form_fields[ j ];
							}
							formCheck = false;
						}
					}

					//exit and scroll to error field
					if ( false === formCheck ) {
						//Show error message at the form bottom
						gutena_forms.classList.add( 'display-error-message' );
						//scroll to element
						error_field.scrollIntoView( {
							behavior: 'smooth',
						} );

						return;
					}

					/*****************************
                     Form Validation :END
                     *****************************/
					gutena_forms.classList.add( 'form-progress' );
					let submitBtnLink = this.querySelector(
						'.wp-block-button__link'
					);

					let submitBtnLinkHtml = submitBtnLink.innerHTML;
					this.disabled = true;
					submitBtnLink.innerHTML =
						'<div class="gutena-forms-btn-progress"><div></div><div></div><div></div><div></div></div>';
					let form_data = new FormData( gutena_forms );
					form_data.append( 'nonce', gutenaFormsBlock.nonce );
					form_data.append(
						'action',
						gutenaFormsBlock.submit_action
					);

					//Hide error before submit
					gutena_forms.classList.remove( 'display-error-message' );
					gutena_forms.classList.remove( 'display-success-message' );


					//Google recaptcha
					const formRecaptcha = getFormRecaptchaConfig( gutena_forms );
					if ( ! isEmpty( formRecaptcha.type ) && 'v3' === formRecaptcha.type && ! isEmpty( formRecaptcha.siteKey ) ) {
						let grecaptcha_enable  = gutena_forms.querySelector(
							'input[name="recaptcha_enable"]'
						);
						if ( ! isEmpty( grecaptcha_enable ) && 0 != grecaptcha_enable.length && grecaptcha_enable.value ) {
							if ( 'undefined' === typeof grecaptcha || null === grecaptcha ) {
								console.log("grecaptcha not defined");
								save_gutena_forms( gutena_forms,  form_data, submitButton[ i ], submitBtnLink, submitBtnLinkHtml  );
							} else {
								grecaptcha.ready(function() {
									grecaptcha.execute( formRecaptcha.siteKey, {action: 'submit'}).then( function( token ) {
										form_data.append('g-recaptcha-response', token);
										save_gutena_forms( gutena_forms,  form_data, submitButton[ i ], submitBtnLink, submitBtnLinkHtml  );
									});
								});
							}
						} else {
							save_gutena_forms( gutena_forms,  form_data, submitButton[ i ], submitBtnLink, submitBtnLinkHtml  );
						}
					} else {
						//recaptcha not enabled or configured
						save_gutena_forms( gutena_forms,  form_data, submitButton[ i ], submitBtnLink, submitBtnLinkHtml  );
					}
				} );
			}
		}
	};

	const save_gutena_forms = ( gutena_forms,  form_data, submitButton, submitBtnLink, submitBtnLinkHtml ) => { 
		fetch( gutenaFormsBlock.ajax_url, {
			method: 'POST',
			credentials: 'same-origin', // <-- make sure to include credentials
			body: form_data,
		} )
		.then( ( response ) => response.json() )
		.then( ( response ) => {
			submitButton.disabled = false;
			submitBtnLink.innerHTML = submitBtnLinkHtml;
			gutena_forms.classList.remove( 'form-progress' );
			if (
				! isEmpty( response ) &&
				'error' === response.status
			) {
				gutena_forms.classList.add(
					'display-error-message'
				);

				//Get form error message block first paragraph
				let errorMsgElement =
					gutena_forms.querySelector(
						'.wp-block-gutena-form-error-msg .gutena-forms-error-text'
					);

				//check if element is exist
				if (
					isEmpty( errorMsgElement ) ||
					0 === errorMsgElement.length
				) {
					console.log( 'errorMsgElement not found' );
				}

				//Insert message
				errorMsgElement.innerHTML = response.message;

				console.log( 'Form Message', response );
			} else {
				//Reset Form
				gutena_forms.reset();

				gutena_forms.classList.add(
					'display-success-message'
				);

				gutena_forms.dispatchEvent(
					new CustomEvent( 'gutena-forms-submit-success', {
						bubbles: false,
					} )
				);

				if (
					hasClass(
						gutena_forms,
						'hide-form-after-submit'
					) ||
					gutena_forms.querySelector(
						'[data-gutena-hide-form-after-submit]'
					)
				) {
					gutena_forms.classList.add(
						'hide-form-now'
					);
				}

				//Check for redirection
				if (
					hasClass(
						gutena_forms,
						'after_submit_redirect_url'
					)
				) {
					//get redirect_url
					let redirect_url =
						gutena_forms.querySelector(
							'input[name="redirect_url"]'
						);
					//check if element is exist
					if (
						isEmpty( redirect_url ) ||
						0 === redirect_url.length
					) {
						console.log( 'redirect_url not found' );
					}

					redirect_url = redirect_url.value;

					if ( ! isEmpty( redirect_url ) ) {
						//redirect to redirect_url
						setTimeout( () => {
							location.href = redirect_url;
						}, 2000 );
					} else {
						console.log(
							'redirect_url',
							redirect_url
						);
					}
				}
			}
		} );
	}

	const field_validation_on_input = () => {
		let formField = document.querySelectorAll(
			'.wp-block-gutena-forms .gutena-forms-field'
		);
		if ( 0 < formField.length ) {
			for ( let i = 0; i < formField.length; i++ ) {
				formField[ i ].addEventListener( 'input', function () {
					field_validation( formField[ i ] );
				} );
			}
		}
	};

	//Form field validation
	const field_validation = ( form_field ) => {
		if ( isEmpty( form_field ) ) {
			console.log( 'No input fields found' );
			return false;
		}

		//get gutena_forms
		let gutena_forms = getParents(
			form_field,
			'.wp-block-gutena-forms'
		);

		if ( isEmpty( gutena_forms ) ) {
			console.log( 'Form not defined' );
			return false;
		}
		gutena_forms = gutena_forms[0];

		//Get custom validation messages for this form
		let customMessages = gutena_forms.getAttribute('data-validation-messages');
		if ( ! isEmpty( customMessages ) ) {
			try {
				customMessages = JSON.parse( customMessages );
			} catch ( error ) {
				console.log( 'Error parsing custom validation messages', error );
				customMessages = {};
			}
		} else {
			customMessages = {};
		}
		
		let input_value = '';
		let is_required = hasClass( form_field, 'required-field' );
		
		//get field group 
		let field_group = getParents(
			form_field,
			'.wp-block-gutena-field-group'
		);
		
		//return false if field group not exists
		if ( isEmpty( field_group ) ) {
			console.log( 'field_group not defined' );
			return false;
		}

		field_group = field_group[0];
		
		let isCheckboxOrRadio =  hasClass( form_field, 'checkbox-field' ) || hasClass( form_field, 'radio-field' ) || hasClass( form_field, 'optin-field' );

		if ( isCheckboxOrRadio ) {
			let	checkboxRadioHtml =	form_field.querySelectorAll('input');
			
			if ( isEmpty( checkboxRadioHtml ) ) {
				console.log( 'checkboxRadioHtml not defined' );
				return false;
			}
			//Check for value
			for ( let k = 0; k < checkboxRadioHtml.length; k++ ) {
				if ( checkboxRadioHtml[k].checked ) {
					input_value = checkboxRadioHtml[k].value;
					break;
				}
			}
		} else if ( hasClass( form_field, 'rating-field' ) ) {
			const hiddenInput = 'INPUT' === form_field.tagName && 'hidden' === form_field.type
				? form_field
				: form_field.querySelector( 'input[type="hidden"]' );
			input_value = ! isEmpty( hiddenInput ) ? hiddenInput.value : form_field.value;
		} else {
			input_value = form_field.value;
		}

		let errorHTML = field_group.querySelector(
			'.gutena-forms-field-error-msg'
		);

		if ( isEmpty( errorHTML ) ) {
			console.log( 'errorHTML not defined' );
			return false;
		}

		//Remove class in field_group element to hide error contained in child element
		field_group.classList.remove( 'display-error' );

		//check required validation
		if (
			is_required &&
			( isEmpty( input_value ) ||
				( hasClass( form_field, 'select-field' ) &&
					'select' === input_value ) )
		) {
			//Add class in field_group element to display error contained in child element
			field_group.classList.add( 'display-error' );

			//error message hierarchy: Block-specific > Global Settings
			let error_msg = ! isEmpty( customMessages.required_msg ) ? customMessages.required_msg : gutenaFormsBlock.required_msg;

			if ( hasClass( form_field, 'select-field' ) ) {
				error_msg = ! isEmpty( customMessages.required_msg_select ) ? customMessages.required_msg_select : gutenaFormsBlock.required_msg_select;
			}

			if ( hasClass( form_field, 'optin-field' ) ) {
				error_msg = ! isEmpty( customMessages.required_msg_optin ) ? customMessages.required_msg_optin : gutenaFormsBlock.required_msg_optin;
			} else if ( isCheckboxOrRadio ) {
				error_msg = ! isEmpty( customMessages.required_msg_check ) ? customMessages.required_msg_check : gutenaFormsBlock.required_msg_check;
			}

			errorHTML.innerHTML = error_msg;

			return false;
		}

		//Email Validation
		if (
			! isEmpty( input_value ) &&
			hasClass( form_field, 'email-field' ) &&
			! validateEmail( input_value )
		) {
			//Add class in field_group element to display error contained in child element
			field_group.classList.add( 'display-error' );

			//error message hierarchy: Block-specific > Global Settings
			errorHTML.innerHTML = ! isEmpty( customMessages.invalid_email_msg ) ? customMessages.invalid_email_msg : gutenaFormsBlock.invalid_email_msg;

			return false;
		}

		//URL Validation
		if (
			! isEmpty( input_value ) &&
			hasClass( form_field, 'url-field' ) &&
			! validateUrl( input_value )
		) {
			field_group.classList.add( 'display-error' );

			const invalidUrlMsg =
				! isEmpty( customMessages.invalid_url_msg )
					? customMessages.invalid_url_msg
					: ( ! isEmpty( gutenaFormsBlock.invalid_url_msg )
						? gutenaFormsBlock.invalid_url_msg
						: 'Please enter a valid URL' );

			errorHTML.innerHTML = invalidUrlMsg;

			return false;
		}

		//Number Validation : Minimum and maximum value
		if ( ! isEmpty( input_value ) && hasClass( form_field, 'number-field' ) ) {
			let minValue = form_field.getAttribute('min');
			let maxValue = form_field.getAttribute('max');
			const numericValue = Number( input_value );
			const numericMin = Number( minValue );
			const numericMax = Number( maxValue );
			const hasNumericValue = ! Number.isNaN( numericValue );

			//if input value is less than minimum
			if (
				! isEmpty( minValue ) &&
				hasNumericValue &&
				! Number.isNaN( numericMin ) &&
				numericValue < numericMin
			) {
				//Add class in field_group element to display error contained in child element
				field_group.classList.add( 'display-error' );
				//error message hierarchy: Block-specific > Global Settings
				let min_msg = ! isEmpty( customMessages.min_value_msg ) ? customMessages.min_value_msg : gutenaFormsBlock.min_value_msg;
				errorHTML.innerHTML = min_msg + ' ' + minValue;
				return false;
			}

			//if input value is greater than maximum
			if (
				! isEmpty( maxValue ) &&
				hasNumericValue &&
				! Number.isNaN( numericMax ) &&
				numericValue > numericMax
			) {
				//Add class in field_group element to display error contained in child element
				field_group.classList.add( 'display-error' );
				//error message hierarchy: Block-specific > Global Settings
				let max_msg = ! isEmpty( customMessages.max_value_msg ) ? customMessages.max_value_msg : gutenaFormsBlock.max_value_msg;
				errorHTML.innerHTML = max_msg + ' ' + maxValue;
				return false;
			}
		}

		return true;
	};

	const show_range_value = ( field_group, value ) => {
		//check if exist
		if ( ! isEmpty( field_group ) ) {
			let rangeValueElement = field_group.querySelector(
				'.range-input-value'
			);
			//check if exist
			if ( ! isEmpty( field_group ) ) {
				rangeValueElement.innerHTML = value;
			}
		}
	}

	//Htnl input range slider on change show value
	const range_slider_onchange = () => {
		let rangeField = document.querySelectorAll(
			'.wp-block-gutena-forms .range-field'
		);
		if ( 0 < rangeField.length ) {
			for ( let i = 0; i < rangeField.length; i++ ) {
				//show initially
				show_range_value( rangeField[ i ].parentNode, rangeField[ i ].value );
				
				//show on change
				rangeField[ i ].addEventListener( 'input', function () {
					show_range_value( this.parentNode, this.value );
				} );
			}
		}
	}	

	window.gutenaFormsValidation = {
		isEmpty,
		hasClass,
		getParents,
		validateEmail,
		validateUrl,
		field_validation,
	};

	ready();
});
