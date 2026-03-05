import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';

const GutenaFormsStatus = ( { entryId, showProPopupHandler } ) => {
	const { StatusComponent } = applyFilters( 'gutenaFormsFree.core.pro-components', {} );

	return (
		<StatusComponent
			entryId={ entryId }
			onClick={ showProPopupHandler }
		/>
	);
};

export default GutenaFormsStatus;
