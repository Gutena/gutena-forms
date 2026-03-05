import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';

const GutenaFormsTags = ( { entryId, showProPopupHandler } ) => {

	const { TagsComponent } = applyFilters( 'gutenaFormsFree.core.pro-components', {} );

	return (
		<TagsComponent
			entryId={ entryId }
			onClick={ showProPopupHandler }
		/>
	);
};

export default GutenaFormsTags;
