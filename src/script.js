window.onload = () => {
	const ready = () => {
		form_sumbit();
		field_validation_on_input();
	};

	//Check Empty
	const isEmpty = ( data ) => {
		return 'undefined' === data || null === data || '' == data;
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

					fetch( gutenaFormsBlock.ajax_url, {
						method: 'POST',
						credentials: 'same-origin', // <-- make sure to include credentials
						body: form_data,
					} )
						.then( ( response ) => response.json() )
						.then( ( response ) => {
							submitButton[ i ].disabled = false;
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
				} );
			}
		}
	};

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

		let input_value = form_field.value;
		let is_required = hasClass( form_field, 'required-field' );
		let field_group = form_field.parentNode.parentNode.parentNode;

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
};
