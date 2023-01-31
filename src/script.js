document.addEventListener("DOMContentLoaded", function(){
	const ready = () => {
		form_sumbit();
		field_validation_on_input();
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
						if ( 0 != grecaptcha_enable.length && grecaptcha_enable.value ) {
							grecaptcha.ready(function() {
								grecaptcha.execute( gutenaFormsBlock.grecaptcha_site_key, {action: 'submit'}).then( function( token ) {
									/* for v3 - append g-recaptcha-response input 
									 for v2 - already present */
									form_data.append('g-recaptcha-response', token);

									// Add your logic to submit to your backend server here.
									save_gutena_forms( gutena_forms,  form_data, submitButton[ i ], submitBtnLink, submitBtnLinkHtml  );
								});
							});
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
		console.log( "gutena_forms,  form_data, submitButton, submitBtnLink, submitBtnLinkHtml");
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

	const field_validation = ( form_field ) => {
		if ( isEmpty( form_field ) ) {
			console.log( 'No input fields found' );
			return false;
		}

		
		let input_value = '';
		let is_required = hasClass( form_field, 'required-field' );
		let field_group = form_field.parentNode.parentNode.parentNode;
		let isCheckboxOrRadio =  hasClass( form_field, 'checkbox-field' ) || hasClass( form_field, 'radio-field' );

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

		if ( isEmpty( field_group ) ) {
			console.log( 'field_group not defined' );
			return false;
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

			if ( isCheckboxOrRadio ) {
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

		return true;
	};

	ready();
});
