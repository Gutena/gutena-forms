import { useCallback, useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import GutenaFormsNotificationMergeTagPopover from '../../../../shared/components/GutenaFormsNotificationMergeTagPopover';
import {
	BoldIcon,
	ItalicIcon,
	UnderlineIcon,
	DelIcon,
	AlignLeftIcon,
	AlignCenterIcon,
	AlignRightIcon,
	OrderedListIcon,
	UnorderedListIcon,
	LinkIcon,
} from '../../../../shared/components/EditorIcons';

const execCmd = ( command, value = null ) => {
	document.execCommand( command, false, value );
};

const ToolbarButton = ( { onClick, isActive, title, children, disabled } ) => (
	<button
		type="button"
		className={ `gf-editor-toolbar__btn${ isActive ? ' is-active' : '' }` }
		onMouseDown={ ( e ) => {
			e.preventDefault();
			onClick();
		} }
		title={ title }
		disabled={ disabled }
	>
		{ children }
	</button>
);

const ToolbarSeparator = () => (
	<span className="gf-editor-toolbar__separator" />
);

const FormConfirmationMessageField = ( {
	id,
	label,
	value,
	onChange,
	placeholder = '',
	formTagItems = [],
	disabled = false,
} ) => {
	const [ viewMode, setViewMode ] = useState( 'visual' );
	const [ activeFormats, setActiveFormats ] = useState( {} );
	const editorRef = useRef( null );
	const lastSyncedValue = useRef( null );

	const insertAtCursor = useCallback(
		( text ) => {
			if ( disabled ) {
				return;
			}

			if ( 'code' === viewMode ) {
				const element = document.getElementById( `${ id }-code` );
				const currentValue = value || '';
				const start =
					element && typeof element.selectionStart === 'number'
						? element.selectionStart
						: currentValue.length;
				const end =
					element && typeof element.selectionEnd === 'number'
						? element.selectionEnd
						: start;
				const nextValue = `${ currentValue.slice(
					0,
					start
				) }${ text }${ currentValue.slice( end ) }`;
				onChange( nextValue );
				requestAnimationFrame( () => {
					if ( element ) {
						element.focus();
						const cursor = start + text.length;
						element.setSelectionRange( cursor, cursor );
					}
				} );
				return;
			}

			if ( editorRef.current ) {
				editorRef.current.focus();
				const selection = window.getSelection();
				if ( selection.rangeCount > 0 ) {
					const range = selection.getRangeAt( 0 );
					range.deleteContents();
					const textNode = document.createTextNode( text );
					range.insertNode( textNode );
					range.setStartAfter( textNode );
					range.setEndAfter( textNode );
					selection.removeAllRanges();
					selection.addRange( range );
				} else {
					editorRef.current.textContent += text;
				}
				const html = editorRef.current.innerHTML;
				lastSyncedValue.current = html;
				onChange( html );
			}
		},
		[ disabled, id, onChange, value, viewMode ]
	);

	const checkActiveFormats = useCallback( () => {
		setActiveFormats( {
			bold: document.queryCommandState( 'bold' ),
			italic: document.queryCommandState( 'italic' ),
			underline: document.queryCommandState( 'underline' ),
			strikeThrough: document.queryCommandState( 'strikeThrough' ),
			justifyLeft: document.queryCommandState( 'justifyLeft' ),
			justifyCenter: document.queryCommandState( 'justifyCenter' ),
			justifyRight: document.queryCommandState( 'justifyRight' ),
			insertOrderedList: document.queryCommandState( 'insertOrderedList' ),
			insertUnorderedList: document.queryCommandState(
				'insertUnorderedList'
			),
		} );
	}, [] );

	const handleEditorInput = useCallback( () => {
		if ( editorRef.current ) {
			const html = editorRef.current.innerHTML;
			lastSyncedValue.current = html;
			onChange( html );
			checkActiveFormats();
		}
	}, [ onChange, checkActiveFormats ] );

	const handleEditorKeyDown = useCallback(
		( e ) => {
			if ( e.key === 'Tab' ) {
				e.preventDefault();
				execCmd( 'insertHTML', '&emsp;' );
				const html = editorRef.current?.innerHTML || '';
				lastSyncedValue.current = html;
				onChange( html );
			}
		},
		[ onChange ]
	);

	useEffect( () => {
		if ( 'visual' !== viewMode || ! editorRef.current ) {
			if ( 'code' === viewMode ) {
				lastSyncedValue.current = null;
			}
			return;
		}

		const editor = editorRef.current;

		editor.innerHTML = value || '';
		lastSyncedValue.current = value || '';

		editor.addEventListener( 'keyup', checkActiveFormats );
		editor.addEventListener( 'mouseup', checkActiveFormats );
		document.addEventListener( 'selectionchange', checkActiveFormats );

		return () => {
			editor.removeEventListener( 'keyup', checkActiveFormats );
			editor.removeEventListener( 'mouseup', checkActiveFormats );
			document.removeEventListener( 'selectionchange', checkActiveFormats );
		};
	}, [ viewMode, value, checkActiveFormats ] );

	const handleFormat = ( command, valueArg ) => {
		editorRef.current?.focus();
		execCmd( command, valueArg );
		checkActiveFormats();
		const html = editorRef.current?.innerHTML || '';
		lastSyncedValue.current = html;
		onChange( html );
	};

	const handleLink = () => {
		const url = window.prompt( __( 'Enter URL:', 'gutena-forms' ) );
		if ( url ) {
			handleFormat( 'createLink', url );
		}
	};

	const handleCodeChange = ( event ) => {
		onChange( event.target.value );
	};

	return (
		<div
			className={ `gutena-forms-form-confirmation-message-field${
				disabled ? ' is-disabled' : ''
			}` }
		>
			<div className="gutena-forms-form-confirmation-message-field__header">
				<label
					className="gutena-forms-form-confirmation-message-field__label"
					htmlFor={ id }
				>
					{ label }
				</label>

				<div className="gutena-forms-form-confirmation-message-field__toolbar">
					<button
						type="button"
						className={ `gutena-forms-form-confirmation-message-field__mode${
							'visual' === viewMode ? ' is-active' : ''
						}` }
						onClick={ () => setViewMode( 'visual' ) }
						disabled={ disabled }
					>
						{ __( 'Visual', 'gutena-forms' ) }
					</button>
					<button
						type="button"
						className={ `gutena-forms-form-confirmation-message-field__mode${
							'code' === viewMode ? ' is-active' : ''
						}` }
						onClick={ () => setViewMode( 'code' ) }
						disabled={ disabled }
					>
						{ __( 'Code', 'gutena-forms' ) }
					</button>
					<GutenaFormsNotificationMergeTagPopover
						variant="form-tags"
						tagItems={ formTagItems }
						onInsert={ insertAtCursor }
						buttonLabel={ __( 'Form tags', 'gutena-forms' ) }
						popoverTitle={ __( 'Form input Tags', 'gutena-forms' ) }
						disabled={ disabled }
					/>
				</div>
			</div>

			<div className="gutena-forms-form-confirmation-message-field__editor">
				{ 'visual' === viewMode && (
					<div className="gf-editor-toolbar">
						<span className="gf-editor-toolbar__format-label">
							{ __( 'Normal', 'gutena-forms' ) }
						</span>
						<ToolbarSeparator />
						<ToolbarButton
							onClick={ () => handleFormat( 'bold' ) }
							isActive={ activeFormats.bold }
							title={ __( 'Bold', 'gutena-forms' ) }
							disabled={ disabled }
						>
							<BoldIcon />
						</ToolbarButton>
						<ToolbarButton
							onClick={ () => handleFormat( 'italic' ) }
							isActive={ activeFormats.italic }
							title={ __( 'Italic', 'gutena-forms' ) }
							disabled={ disabled }
						>
							<ItalicIcon />
						</ToolbarButton>
						<ToolbarButton
							onClick={ () => handleFormat( 'underline' ) }
							isActive={ activeFormats.underline }
							title={ __( 'Underline', 'gutena-forms' ) }
							disabled={ disabled }
						>
							<UnderlineIcon />
						</ToolbarButton>
						<ToolbarButton
							onClick={ () => handleFormat( 'strikeThrough' ) }
							isActive={ activeFormats.strikeThrough }
							title={ __( 'Strikethrough', 'gutena-forms' ) }
							disabled={ disabled }
						>
							<DelIcon />
						</ToolbarButton>
						<ToolbarSeparator />
						<ToolbarButton
							onClick={ () => handleFormat( 'justifyLeft' ) }
							isActive={ activeFormats.justifyLeft }
							title={ __( 'Align left', 'gutena-forms' ) }
							disabled={ disabled }
						>
							<AlignLeftIcon />
						</ToolbarButton>
						<ToolbarButton
							onClick={ () => handleFormat( 'justifyCenter' ) }
							isActive={ activeFormats.justifyCenter }
							title={ __( 'Align center', 'gutena-forms' ) }
							disabled={ disabled }
						>
							<AlignCenterIcon />
						</ToolbarButton>
						<ToolbarButton
							onClick={ () => handleFormat( 'justifyRight' ) }
							isActive={ activeFormats.justifyRight }
							title={ __( 'Align right', 'gutena-forms' ) }
							disabled={ disabled }
						>
							<AlignRightIcon />
						</ToolbarButton>
						<ToolbarSeparator />
						<ToolbarButton
							onClick={ () => handleFormat( 'insertOrderedList' ) }
							isActive={ activeFormats.insertOrderedList }
							title={ __( 'Ordered list', 'gutena-forms' ) }
							disabled={ disabled }
						>
							<OrderedListIcon />
						</ToolbarButton>
						<ToolbarButton
							onClick={ () =>
								handleFormat( 'insertUnorderedList' )
							}
							isActive={ activeFormats.insertUnorderedList }
							title={ __( 'Unordered list', 'gutena-forms' ) }
							disabled={ disabled }
						>
							<UnorderedListIcon />
						</ToolbarButton>
						<ToolbarSeparator />
						<ToolbarButton
							onClick={ handleLink }
							title={ __( 'Link', 'gutena-forms' ) }
							disabled={ disabled }
						>
							<LinkIcon />
						</ToolbarButton>
					</div>
				) }

				{ 'visual' === viewMode ? (
					<div
						ref={ editorRef }
						id={ id }
						className="gutena-forms-form-confirmation-message-field__contenteditable"
						contentEditable={ ! disabled }
						onInput={ handleEditorInput }
						onKeyDown={ handleEditorKeyDown }
						suppressContentEditableWarning
						role="textbox"
						aria-multiline="true"
						aria-label={ label }
					/>
				) : (
					<textarea
						id={ `${ id }-code` }
						className="gutena-forms-form-confirmation-message-field__textarea is-code"
						value={ value || '' }
						onChange={ handleCodeChange }
						placeholder={ placeholder }
						disabled={ disabled }
						rows={ 6 }
					/>
				) }
			</div>
		</div>
	);
};

export default FormConfirmationMessageField;
