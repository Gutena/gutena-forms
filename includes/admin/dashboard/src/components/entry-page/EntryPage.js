import EntryViewEdit from './EntryViewEdit';
import EntryDetails from './EntryDetails';
import RelatedEntries from './RelatedEntries';
import EntryAdminNotes from './EntryAdminNotes';
import EntriesStatus from './EntriesStatus';
import EntriesTags from './EntriesTags';

const EntryPage = (props) => {
    
    return(
        <div className='gfp-entry-page' >
            <div className='left-section'>
                <EntryViewEdit />
                <a modalid="gutena-forms-go-pro-modal" href="#" className="gutena-forms-modal-btn"  >
                <EntryAdminNotes />
                </a>
            </div>
            <div className='right-section'>
                <EntryDetails />
                <a modalid="gutena-forms-go-pro-modal" href="#" className="gutena-forms-modal-btn"  >
                <EntriesStatus />
                <EntriesTags />
                <RelatedEntries />
                </a>
            </div>
        </div>
    );
}

export default EntryPage;