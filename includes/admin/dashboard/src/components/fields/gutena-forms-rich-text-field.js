import { useState, useEffect, useRef, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
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

const WpVisualEditor = ( { value, onChange, id, rows = 7, disabled = false, editorRef: externalRef } ) => {
	const instanceId = useInstanceId( WpVisualEditor, 'gf-dashboard-mce' );
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

	return (
		<textarea
			id={ instanceId }
			rows={ rows }
			defaultValue={ value || '' }
			disabled={ disabled }
		/>
	);
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

const GutenaFormsRichTextField = ( {
	onChange,
	label,
	id,
	desc,
	value,
	attrs = {},
	disabled = false,
} ) => {
	const rows = attrs?.rows || 7;
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
		<div className="gutena-forms__rich-text-control">
			<div className="gutena-forms__rich-text-header">
				{ label && (
					<label className="gutena-forms__rich-text-label" htmlFor={ id }>
						{ label }
					</label>
				) }
				<div className="gutena-forms__rich-text-modes">
					<button
						type="button"
						className={ 'gutena-forms__rich-text-mode' + ( mode === 'visual' ? ' is-active' : '' ) }
						disabled={ disabled }
						onClick={ () => {
							setMode( 'visual' );
							setShowTagsPopup( false );
						} }
					>
						{ __( 'Visual', 'gutena-forms' ) }
					</button>
					<button
						type="button"
						className={ 'gutena-forms__rich-text-mode' + ( mode === 'code' ? ' is-active' : '' ) }
						disabled={ disabled }
						onClick={ () => {
							setMode( 'code' );
							setShowTagsPopup( false );
						} }
					>
						{ __( 'Code', 'gutena-forms' ) }
					</button>
					<button
						type="button"
						className={ 'gutena-forms__rich-text-mode' + ( showTagsPopup ? ' is-active' : '' ) }
						disabled={ disabled }
						onClick={ () => setShowTagsPopup( ( prev ) => ! prev ) }
					>
						{ __( 'Form tags', 'gutena-forms' ) }
					</button>
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
				<div className="gutena-forms__rich-text-wp-visual">
					<WpVisualEditor
						value={ safeValue }
						onChange={ onChange }
						id={ id }
						rows={ rows }
						disabled={ disabled }
						editorRef={ mceRef }
					/>
				</div>
			) }

			{ mode === 'code' && (
				<textarea
					ref={ textareaRef }
					id={ id }
					className="gutena-forms__rich-text-textarea"
					rows={ rows }
					value={ safeValue }
					disabled={ disabled }
					onChange={ ( e ) => onChange( e.target.value ) }
				/>
			) }

			{ desc && (
				<p
					className="gutena-forms__field-description"
					dangerouslySetInnerHTML={ { __html: desc } }
				/>
			) }
		</div>
	);
};

export default GutenaFormsRichTextField;
