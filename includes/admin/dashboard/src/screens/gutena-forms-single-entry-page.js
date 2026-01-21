import { ArrowLeft } from '../icons/arrow';
import { useState, useEffect } from '@wordpress/element';
import {} from '../api/entries';

import GutenaFormsEntryData from '../components/entries/gutena-forms-entry-data';
import GutenaFormsEntryDetails from '../components/entries/gutena-forms-entry-details';
import GutenaFormsNotes from '../components/entries/gutena-forms-notes';
import GutenaFormsTags from '../components/entries/gutena-forms-tags';
import GutenaFormsRelatedEntries from '../components/entries/gutena-forms-related-entries';
import GutenaFormsStatus from '../components/entries/gutena-forms-status';


const GutenaFormsSingleEntryPage = ( { entryId } ) => {

	useEffect( () => {}, [ entryId ] )

	return (
		<div className={ 'gutena-forms__entry-screen' }>
			<div>
				<h2 className={ 'heading' } style={ { marginBottom: '30px' } }>
					<ArrowLeft /> Entry 2 / 2
				</h2>
			</div>

			<div className={ 'gutena-forms__entry-screen-container' }>
				<div className={ 'gutena-forms__col-70' }>
					<GutenaFormsEntryData entryId={ entryId } />
					<GutenaFormsEntryDetails entryId={ entryId } />
				</div>
				<div className={ 'gutena-forms__col-30' }>
					<GutenaFormsNotes entryId={ entryId } />
					<GutenaFormsStatus entryId={ entryId } />
					<GutenaFormsTags entryId={ entryId } />
					<GutenaFormsRelatedEntries entryId={ entryId } />
				</div>
			</div>
		</div>
	);
};

export default GutenaFormsSingleEntryPage;
