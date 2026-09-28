/**
 * Editor-only contextual tour trigger host.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { useEditorContextualTourTrigger } from '../hooks/useEditorContextualTourTrigger';

/**
 * @param {Object} props
 * @param {Object} props.state
 * @param {Function} props.resumeContextualTourAt
 */
const EditorContextualTourTriggers = ( {
	state,
	resumeContextualTourAt,
} ) => {
	useEditorContextualTourTrigger( {
		runtime: 'editor',
		state,
		resumeContextualTourAt,
	} );

	return null;
};

export default EditorContextualTourTriggers;
