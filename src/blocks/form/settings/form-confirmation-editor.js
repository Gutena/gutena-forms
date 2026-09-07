import { useState, useEffect, useRef, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { useInstanceId } from '@wordpress/compose';

const TAG_SECTIONS = [
	{
		title: 'Form input Tags',
		tags: [
			{ label: 'First Name', value: '{field:first_name}' },
			{ label: 'Last Name', value: '{field:last_name}' },
			{ label: 'Email', value: '{field:email}' },
			{ label: 'Message', value: '{field:message}' },
			{ label: 'Country', value: '{field:country}' },
			{ label: 'State', value: '{field:state}' },
			{ label: 'Number', value: '{field:number}' },
			{ label: 'URL', value: '{field:url}' },
			{ label: 'Textarea', value: '{field:textarea}' },
			{ label: 'Phone Number', value: '{field:phone}' },
			{ label: 'Checkbox', value: '{field:checkbox}' },
			{ label: 'All Data', value: '{all_data}' },
		],
	},
	{
		title: 'Generic Tags',
		tags: [
			{ label: 'Site URL', value: '{site_url}' },
			{ label: 'Admin Email', value: '{admin_email}' },
			{ label: 'Site Title', value: '{site_title}' },
			{ label: 'Form Title', value: '{form_title}' },
			{ label: 'User Email', value: '{user_email}' },
			{ label: 'User Name', value: '{user_name}' },
		],
	},
];

const WpVisualEditor = ( { value, onChange, rows = 6, editorRef: externalRef } ) => {
	const instanceId = useInstanceId( WpVisualEditor, 'gf-mce' );
	const editorRef = useRef( null );
	const onChangeRef = useRef( onChange );
	const valueRef = useRef( value );
	const ignoreChange = useRef( false );

	useEffect( () => { onChangeRef.current = onChange; }, [ onChange ] );
	useEffect( () => { valueRef.current = value; }, [ value ] );

	useEffect( () => {
		if ( ! window.wp || ! window.wp.editor ) {
			return;
		}

		let destroyed = false;
		const textarea = document.getElementById( instanceId );
		if ( ! textarea ) {
			return;
		}

		wp.editor.initialize( instanceId, {
			tinymce: {
				wpautop: true,
				toolbar1: 'formatselect bold italic bullist numlist blockquote alignleft aligncenter alignright link unlink wp_adv',
				toolbar2: 'strikethrough hr forecolor pastetext removeformat charmap outdent indent',
				wpme: false,
				resize: 'vertical',
			},
			quicktags: true,
			mediaButtons: false,
		} );

		const poll = setInterval( () => {
			if ( destroyed ) {
				clearInterval( poll );
				return;
			}
			const ed = tinymce.get( instanceId );
			if ( ed ) {
				clearInterval( poll );
				editorRef.current = ed;
				if ( externalRef ) {
					externalRef.current = ed;
				}

				if ( valueRef.current ) {
					ignoreChange.current = true;
					ed.setContent( valueRef.current );
					ignoreChange.current = false;
				}

				ed.on( 'change KeyUp', () => {
					if ( ! ignoreChange.current && onChangeRef.current ) {
						onChangeRef.current( ed.getContent() );
					}
				} );
			}
		}, 200 );

		return () => {
			destroyed = true;
			clearInterval( poll );
			try {
				wp.editor.remove( instanceId );
			} catch ( e ) {
				// ignore
			}
			editorRef.current = null;
			if ( externalRef ) {
				externalRef.current = null;
			}
		};
	}, [ instanceId, externalRef ] );

	useEffect( () => {
		const ed = editorRef.current;
		if ( ed && ! ed.isHidden() ) {
			const current = ed.getContent();
			if ( value && value !== current ) {
				ignoreChange.current = true;
				ed.setContent( value );
				ignoreChange.current = false;
			}
		}
	}, [ value ] );

	return <textarea id={ instanceId } rows={ rows } defaultValue={ value || '' } />;
};

const TagsPopup = ( { sections, onInsert, onClose } ) => {
	const popupRef = useRef( null );

	useEffect( () => {
		const handleClickOutside = ( e ) => {
			if ( popupRef.current && ! popupRef.current.contains( e.target ) ) {
				onClose();
			}
		};
		document.addEventListener( 'mousedown', handleClickOutside );
		return () => document.removeEventListener( 'mousedown', handleClickOutside );
	}, [ onClose ] );

	return (
		<div className="gf-tags-popup" ref={ popupRef }>
			{ sections.map( ( section ) => (
				<div key={ section.title } className="gf-tags-popup__section">
					<div className="gf-tags-popup__section-title">
						{ section.title }
					</div>
					<div className="gf-tags-popup__section-tags">
						{ section.tags.map( ( tag ) => (
							<div
								key={ tag.value }
								className="gf-tags-popup__tag"
								onClick={ () => {
									onInsert( tag.value );
									onClose();
								} }
							>
								<span className="gf-tags-popup__tag-label">
									{ tag.label }
								</span>
								<span className="gf-tags-popup__tag-value">
									{ tag.value }
								</span>
							</div>
						) ) }
					</div>
				</div>
			) ) }
		</div>
	);
};

const ConfirmationMessageEditor = ( {
	label,
	value,
	onChange,
	tags = [],
	rows = 6,
	placeholder,
} ) => {
	const [ mode, setMode ] = useState( 'visual' );
	const [ showTagsPopup, setShowTagsPopup ] = useState( false );
	const textareaRef = useRef( null );
	const mceRef = useRef( null );

	const safeValue = value || '';

	const insertAtCursor = useCallback(
		( content ) => {
			const el = textareaRef.current;
			if ( ! el ) {
				return;
			}
			const start = el.selectionStart ?? safeValue.length;
			const end = el.selectionEnd ?? start;
			onChange(
				safeValue.slice( 0, start ) + content + safeValue.slice( end )
			);
			requestAnimationFrame( () => {
				el.focus();
				el.setSelectionRange(
					start + content.length,
					start + content.length
				);
			} );
		},
		[ safeValue, onChange ]
	);

	const handleInsertTag = useCallback(
		( tag ) => {
			if ( mode === 'visual' && mceRef.current ) {
				mceRef.current.insertContent( tag );
			} else {
				insertAtCursor( tag );
			}
		},
		[ mode, insertAtCursor ]
	);

	return (
		<div className="gf-confirmation-editor">
			<div className="gf-confirmation-editor__header">
				<label className="gf-confirmation-editor__label">
					{ label }
				</label>
				<div className="gf-confirmation-editor__modes">
					<Button
						variant="tertiary"
						size="small"
						isPressed={ mode === 'visual' }
						onClick={ () => {
							setMode( 'visual' );
							setShowTagsPopup( false );
						} }
					>
						{ __( 'Visual', 'gutena-forms' ) }
					</Button>
					<Button
						variant="tertiary"
						size="small"
						isPressed={ mode === 'code' }
						onClick={ () => {
							setMode( 'code' );
							setShowTagsPopup( false );
						} }
					>
						{ __( 'Code', 'gutena-forms' ) }
					</Button>
					<Button
						variant="tertiary"
						size="small"
						isPressed={ showTagsPopup }
						onClick={ () => setShowTagsPopup( ( prev ) => ! prev ) }
						className="gf-form-tags-btn"
					>
						{ __( 'Form tags', 'gutena-forms' ) }
					</Button>
				</div>
				{ showTagsPopup && (
					<TagsPopup
						sections={ TAG_SECTIONS }
						onInsert={ handleInsertTag }
						onClose={ () => setShowTagsPopup( false ) }
					/>
				) }
			</div>

			{ mode === 'visual' && (
				<div className="gf-confirmation-editor__wp-visual">
					<WpVisualEditor
						value={ safeValue }
						onChange={ onChange }
						rows={ rows }
						editorRef={ mceRef }
					/>
				</div>
			) }

			{ mode === 'code' && (
				<textarea
					ref={ textareaRef }
					className="gf-confirmation-editor__textarea"
					rows={ rows }
					value={ safeValue }
					placeholder={ placeholder }
					onChange={ ( e ) => onChange( e.target.value ) }
				/>
			) }
		</div>
	);
};

export default ConfirmationMessageEditor;
