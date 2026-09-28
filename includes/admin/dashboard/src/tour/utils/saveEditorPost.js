/**
 * Save the current Gutenberg post before tour navigation leaves the editor.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

/**
 * @returns {Promise<void>}
 */
export async function saveEditorPostBeforeNavigation() {
	const data = window.wp?.data;

	if ( ! data?.select || ! data?.dispatch ) {
		return;
	}

	const editorSelect = data.select( 'core/editor' );
	const editorDispatch = data.dispatch( 'core/editor' );

	if (
		! editorSelect ||
		typeof editorSelect.isEditedPostDirty !== 'function' ||
		typeof editorDispatch?.savePost !== 'function'
	) {
		return;
	}

	if ( ! editorSelect.isEditedPostDirty() ) {
		return;
	}

	try {
		await editorDispatch.savePost();
	} catch {
		// Continue navigation even when autosave fails.
	}
}
