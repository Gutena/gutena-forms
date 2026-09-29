/**
 * SMTP marketing page — YouTube play + Install/Activate Post SMTP.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		initVideo();
		initInstallButtons();
	} );

	function initVideo() {
		var video = document.querySelector( '.gutena-forms-smtp__video' );
		if ( ! video ) {
			return;
		}

		var trigger = video.querySelector( '.gutena-forms-smtp__video-trigger' );
		if ( ! trigger ) {
			return;
		}

		trigger.addEventListener( 'click', function () {
			var videoId = video.getAttribute( 'data-youtube-id' );
			if ( ! videoId ) {
				return;
			}

			var iframe = document.createElement( 'iframe' );
			iframe.className = 'gutena-forms-smtp__video-iframe';
			iframe.src =
				'https://www.youtube.com/embed/' +
				encodeURIComponent( videoId ) +
				'?autoplay=1&mute=1&rel=0&playsinline=1';
			iframe.title = trigger.getAttribute( 'data-video-title' ) || 'YouTube video';
			iframe.setAttribute( 'frameborder', '0' );
			iframe.setAttribute( 'allowfullscreen', '' );
			iframe.setAttribute(
				'allow',
				'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share'
			);
			iframe.setAttribute( 'referrerpolicy', 'strict-origin-when-cross-origin' );

			video.innerHTML = '';
			video.appendChild( iframe );
			video.classList.add( 'is-playing' );
		} );
	}

	function initInstallButtons() {
		if ( typeof gutenaFormsSmtp === 'undefined' || typeof wp === 'undefined' || ! wp.updates ) {
			return;
		}

		var buttons = document.querySelectorAll( '.gutena-forms-smtp__btn[data-action]' );
		if ( ! buttons.length ) {
			return;
		}

		buttons.forEach( function ( button ) {
			button.addEventListener( 'click', async function ( e ) {
				e.preventDefault();

				if ( button.disabled ) {
					return;
				}

				var action = button.getAttribute( 'data-action' );
				setButtonsDisabled( buttons, true );

				try {
					if ( action === 'install-plugin_post-smtp' ) {
						setButtonsLabel( buttons, gutenaFormsSmtp.i18n.installing );
						await sendPostSMTPRequest( 'installed' );

						var response = await wp.updates.installPlugin( {
							slug: 'post-smtp',
						} );

						if ( response && response.activateUrl !== undefined && response.install === 'plugin' ) {
							await activatePostSMTP( buttons );
						} else {
							setButtonsLabel( buttons, gutenaFormsSmtp.i18n.error );
							setButtonsDisabled( buttons, false );
						}
					} else if ( action === 'activate-plugin_post-smtp' ) {
						await sendPostSMTPRequest( 'activated' );
						await activatePostSMTP( buttons );
					}
				} catch ( error ) {
					console.error( error );
					setButtonsLabel( buttons, gutenaFormsSmtp.i18n.error );
					setButtonsDisabled( buttons, false );
				}
			} );
		} );
	}

	function setButtonsDisabled( buttons, disabled ) {
		buttons.forEach( function ( button ) {
			button.disabled = disabled;
		} );
	}

	function setButtonsLabel( buttons, label ) {
		buttons.forEach( function ( button ) {
			button.textContent = label;
		} );
	}

	async function sendPostSMTPRequest( status ) {
		try {
			var formData = new URLSearchParams( {
				action: 'gutena_forms_post_smtp_request',
				status: status,
				nonce: gutenaFormsSmtp.ajaxNonce,
			} );

			var response = await fetch( gutenaFormsSmtp.ajaxURL, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: formData,
			} );

			if ( ! response.ok ) {
				return false;
			}

			var data = await response.json();
			return ! ! ( data && data.success );
		} catch ( error ) {
			console.error( 'Error sending Post SMTP AJAX request:', error );
			return false;
		}
	}

	async function activatePostSMTP( buttons ) {
		setButtonsLabel( buttons, gutenaFormsSmtp.i18n.activating );

		var activateResponse = await wp.updates.activatePlugin( {
			slug: 'post-smtp',
			name: 'Post SMTP',
			plugin: 'post-smtp/postman-smtp.php',
		} );

		if ( activateResponse ) {
			await sendPostSMTPRequest( 'activated' );
			setButtonsLabel( buttons, gutenaFormsSmtp.i18n.activated );
			setTimeout( function () {
				window.location.href = gutenaFormsSmtp.postSMTPURL;
			}, 1000 );
		} else {
			setButtonsLabel( buttons, gutenaFormsSmtp.i18n.error );
			setButtonsDisabled( buttons, false );
		}
	}
}() );
