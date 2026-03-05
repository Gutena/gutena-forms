import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';

const GutenaFormsNotes = ( { entryId, showProPopupHandler } ) => {

	const { NotesComponent } = applyFilters( 'gutenaFormsFree.core.pro-components', {} );

	return (
		<NotesComponent
			onClick={ showProPopupHandler }
			entryId={ entryId }
		/>
	);
};

export default GutenaFormsNotes;
