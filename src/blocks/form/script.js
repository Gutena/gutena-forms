document.addEventListener("DOMContentLoaded", function(){
	const ready = () => {
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

	//validate Email
	const validateEmail = ( email ) => {
		return email.match(
			/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
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

	//enqueue recaptcha if not enqueued 
	const check_and_load_grecaptcha = () => {
		if ( 'undefined' !== typeof gutenaFormsBlock && ! isEmpty( gutenaFormsBlock.grecaptcha_type ) && ! isEmpty( gutenaFormsBlock.grecaptcha_site_key ) ) {
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
								grecaptcha_url = 'https://www.google.com/recaptcha/api.js';
								if ( 'v3' === gutenaFormsBlock.grecaptcha_type ) {
									grecaptcha_url += '?render='+gutenaFormsBlock.grecaptcha_site_key
								}
								grecaptcha_script_html.src = grecaptcha_url;
								//insert before form script
								document.head.insertBefore( grecaptcha_script_html, gutena_forms_script_html );
								//console.log("recaptcha loaded");
							}
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
					if ( ! isEmpty( gutenaFormsBlock.grecaptcha_type ) && 'v3' === gutenaFormsBlock.grecaptcha_type && ! isEmpty( gutenaFormsBlock.grecaptcha_site_key ) ) {
						let grecaptcha_enable  = gutena_forms.querySelector(
							'input[name="recaptcha_enable"]'
						);
						if ( ! isEmpty( grecaptcha_enable ) && 0 != grecaptcha_enable.length && grecaptcha_enable.value ) {
							if ( 'undefined' === typeof grecaptcha || null === grecaptcha ) {
								console.log("grecaptcha not defined");
								save_gutena_forms( gutena_forms,  form_data, submitButton[ i ], submitBtnLink, submitBtnLinkHtml  );
							} else {
								grecaptcha.ready(function() {
									grecaptcha.execute( gutenaFormsBlock.grecaptcha_site_key, {action: 'submit'}).then( function( token ) {
										/* for v3 - append g-recaptcha-response input 
										 for v2 - already present */
										form_data.append('g-recaptcha-response', token);
	
										// Add your logic to submit to your backend server here.
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
		//console.log( "gutena_forms,  form_data, submitButton, submitBtnLink, submitBtnLinkHtml");
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

				if (
					hasClass(
						gutena_forms,
						'hide-form-after-submit'
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

			//error message
			let error_msg = gutenaFormsBlock.required_msg;

			if ( hasClass( form_field, 'select-field' ) ) {
				error_msg = gutenaFormsBlock.required_msg_select;
			}

			if ( hasClass( form_field, 'optin-field' ) ) {
				error_msg = gutenaFormsBlock.required_msg_optin;
			} else if ( isCheckboxOrRadio ) {
				error_msg = gutenaFormsBlock.required_msg_check;
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

			//error message
			errorHTML.innerHTML = gutenaFormsBlock.invalid_email_msg;

			return false;
		}

		//Number Validation : Minimum and maximum value
		if ( ! isEmpty( input_value ) && hasClass( form_field, 'number-field' ) ) {
			let minValue = form_field.getAttribute('min');
			let maxValue = form_field.getAttribute('max');

			//if input value is less than minimum
			if ( ! isEmpty( minValue ) && input_value < minValue ) {
				//Add class in field_group element to display error contained in child element
				field_group.classList.add( 'display-error' );
				//error message
				errorHTML.innerHTML = gutenaFormsBlock.min_value_msg+' '+minValue;
				return false;
			}

			//if input value is greater than maximum
			if ( ! isEmpty( maxValue ) && input_value > maxValue ) {
				//Add class in field_group element to display error contained in child element
				field_group.classList.add( 'display-error' );
				//error message
				errorHTML.innerHTML = gutenaFormsBlock.max_value_msg+' '+maxValue;
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

	ready();
});
