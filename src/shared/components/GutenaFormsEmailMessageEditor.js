import { useCallback, useEffect, useRef, useState } from '@wordpress/element';

const canUseWpEditor = () => {
	return (
		typeof window !== 'undefined' &&
		window.wp &&
		window.wp.editor &&
		typeof window.wp.editor.initialize === 'function'
	);
};

const getEditorContent = ( editorId ) => {
	if ( typeof window.wp?.editor?.getContent === 'function' ) {
		return window.wp.editor.getContent( editorId );
	}

	const tinymceEditor = window.tinymce?.get( editorId );
	if ( tinymceEditor && ! tinymceEditor.isHidden() ) {
		return tinymceEditor.getContent();
	}

	const textarea = document.getElementById( editorId );
	return textarea ? textarea.value : '';
};

const setEditorContent = ( editorId, content ) => {
	const normalized = content || '';
	const tinymceEditor = window.tinymce?.get( editorId );

	if ( tinymceEditor && ! tinymceEditor.isHidden() ) {
		tinymceEditor.setContent( normalized );
		return;
	}

	const textarea = document.getElementById( editorId );
	if ( textarea ) {
		textarea.value = normalized;
	}
};

const WP_EDITOR_READY_TIMEOUT = 2500;

const GutenaFormsEmailMessageEditor = ( {
	id,
	label,
	value = '',
	onChange,
	onFocus,
	disabled = false,
	placeholder = '',
	onRegisterInsert,
	rows = 8,
	useWpEditor = true,
} ) => {
	const textareaRef = useRef( null );
	const editorReady = useRef( false );
	const [ editorMode, setEditorMode ] = useState( () =>
		useWpEditor ? 'pending' : 'fallback'
	);

	const emitChange = useCallback(
		( nextValue ) => {
			if ( onChange ) {
				onChange( nextValue );
			}
		},
		[ onChange ]
	);

	const insertAtCursor = useCallback(
		( text ) => {
			if ( disabled ) {
				return;
			}

			if ( 'fallback' === editorMode && textareaRef.current ) {
				const element = textareaRef.current;
				const start = element.selectionStart ?? ( value || '' ).length;
				const end = element.selectionEnd ?? start;
				const currentValue = value || '';
				const nextValue = `${ currentValue.slice( 0, start ) }${ text }${ currentValue.slice( end ) }`;
				emitChange( nextValue );
				requestAnimationFrame( () => {
					element.focus();
					const cursor = start + text.length;
					element.setSelectionRange( cursor, cursor );
				} );
				return;
			}

			if ( window.tinymce?.get( id ) ) {
				window.tinymce.get( id ).insertContent( text );
				emitChange( getEditorContent( id ) );
				return;
			}

			if ( window.QTags ) {
				window.QTags.insertContent( text );
				emitChange( getEditorContent( id ) );
			}
		},
		[ disabled, editorMode, emitChange, id, value ]
	);

	useEffect( () => {
		if ( onRegisterInsert ) {
			onRegisterInsert( insertAtCursor );
		}
	}, [ insertAtCursor, onRegisterInsert ] );

	const switchToFallback = useCallback( () => {
		if ( window.wp?.editor?.remove ) {
			try {
				window.wp.editor.remove( id );
			} catch ( error ) {
				// Ignore teardown errors from partially initialized editors.
			}
		}
		editorReady.current = false;
		setEditorMode( 'fallback' );
	}, [ id ] );

	const hasVisibleWpEditor = useCallback( () => {
		const wrap = document.getElementById( `wp-${ id }-wrap` );
		if ( ! wrap ) {
			return false;
		}

		const tinymceEditor = window.tinymce?.get( id );
		if ( tinymceEditor?.getContainer?.() ) {
			const container = tinymceEditor.getContainer();
			return container.offsetHeight > 0 || container.offsetWidth > 0;
		}

		return !! wrap.querySelector( '.quicktags-toolbar' );
	}, [ id ] );

	useEffect( () => {
		if ( disabled || ! useWpEditor ) {
			setEditorMode( 'fallback' );
			return undefined;
		}

		if ( ! canUseWpEditor() ) {
			setEditorMode( 'fallback' );
			return undefined;
		}

		let initialized = false;
		let modeActivated = false;
		let cancelled = false;
		let frameId = 0;
		let readyTimeoutId = 0;

		const activateWpMode = () => {
			if ( cancelled || modeActivated ) {
				return;
			}

			modeActivated = true;
			editorReady.current = true;
			setEditorMode( 'wp' );

			if ( value ) {
				setEditorContent( id, value );
			}
		};

		const initializeEditor = () => {
			if ( ! textareaRef.current ) {
				switchToFallback();
				return;
			}

			try {
				window.wp.editor.initialize( id, {
					tinymce: {
						wpautop: true,
						plugins: 'lists link paste',
						toolbar1: 'bold italic underline bullist numlist link unlink',
						setup: ( editor ) => {
							editor.on( 'init', () => {
								activateWpMode();
							} );
							editor.on( 'change keyup SetContent', () => {
								emitChange( getEditorContent( id ) );
							} );
						},
					},
					quicktags: true,
					mediaButtons: false,
				} );
				initialized = true;

				readyTimeoutId = window.setTimeout( () => {
					if ( cancelled || modeActivated ) {
						return;
					}

					if ( hasVisibleWpEditor() ) {
						activateWpMode();
						return;
					}

					switchToFallback();
				}, WP_EDITOR_READY_TIMEOUT );
			} catch ( error ) {
				if ( initialized ) {
					switchToFallback();
					initialized = false;
					return;
				}
				setEditorMode( 'fallback' );
			}
		};

		frameId = window.requestAnimationFrame( initializeEditor );

		return () => {
			cancelled = true;
			window.cancelAnimationFrame( frameId );
			window.clearTimeout( readyTimeoutId );
			if ( initialized && window.wp?.editor?.remove ) {
				window.wp.editor.remove( id );
			}
			editorReady.current = false;
		};
	}, [ disabled, hasVisibleWpEditor, id, switchToFallback, useWpEditor ] );

	useEffect( () => {
		if ( 'wp' !== editorMode || ! editorReady.current || ! window.wp?.editor ) {
			return;
		}

		const current = getEditorContent( id );
		if ( ( value || '' ) !== current ) {
			setEditorContent( id, value || '' );
		}
	}, [ editorMode, id, value ] );

	const handleFallbackChange = ( event ) => {
		emitChange( event.target.value );
	};

	const modeClass =
		'wp' === editorMode
			? ' is-wp-editor'
			: 'fallback' === editorMode
				? ' is-fallback'
				: ' is-pending';

	return (
		<div
			className={ `gutena-forms__email-message-editor${ modeClass }${
				disabled ? ' is-disabled' : ''
			}` }
		>
			{ label && (
				<label className="gutena-forms__email-message-editor-label" htmlFor={ id }>
					{ label }
				</label>
			) }

			<textarea
				ref={ textareaRef }
				id={ id }
				className="gutena-forms__email-message-editor-textarea"
				value={ 'fallback' === editorMode ? value || '' : undefined }
				defaultValue={ 'fallback' === editorMode ? undefined : value || '' }
				onChange={ 'fallback' === editorMode ? handleFallbackChange : undefined }
				onFocus={ onFocus }
				placeholder={ placeholder }
				disabled={ disabled }
				rows={ rows }
			/>
		</div>
	);
};

export default GutenaFormsEmailMessageEditor;
