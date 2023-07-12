import { __ } from '@wordpress/i18n';
import { rightArrowBox, leftArrowBox } from '../icon';
import { gfpIsEmpty } from '../../helper';

const EntryViewEdit = (props) => {
    const entryData = gutenaFormsEntryDetails?.entry_data;
    const formID = entryData?.form_id;
    const entryViewUrl = gutenaFormsDashboard?.entry_view_url;
    const entry_list_url = gfpIsEmpty( gutenaFormsDashboard?.entry_list_url ) ? '#': gutenaFormsDashboard.entry_list_url+formID;
    
    const getEntryValue = ( entryDetails ) => {
        let value = entryDetails.value;
        if ( gfpIsEmpty( entryDetails.fieldType ) ) {
           return value; 
        }
        
        switch ( entryDetails.fieldType ) {
            case 'file':
                if ( false !== entryDetails.value.indexOf('://') ) {
                    let urls = entryDetails.value.split(',');
                    value = [];
                    urls.forEach( (url, index) => {
                        value.push( '<a target="_blank" href="'+url+'">FILE-'+( parseInt( index ) + 1 )+'</a>' );
                    } );
                    value = value.join(', ');
                }
                break;
        
            default:
                break;
        }
        return value;
    }

    return(
        <div className='view-section no-panel-section' >
        <div className='entries-navigation' >
            <span className='entries-count'> <a href={ entry_list_url } >{  __( 'Entry ', 'gutena-forms' ) }</a> {  entryData.current_entry_sno + ' / ' + entryData.total_entries }</span>
            {
                ! gfpIsEmpty( entryData.previous_entry_id ) && (
                    <a 
                    href={ entryViewUrl+''+entryData.previous_entry_id } className='previous-entry'
                    >
                        { leftArrowBox() }
                    </a>
                )
            }
            {
                ! gfpIsEmpty( entryData.next_entry_id ) && (
                    <a 
                    href={ entryViewUrl+''+entryData.next_entry_id } 
                    className='next-entry'
                    >
                        { rightArrowBox() }
                    </a>
                )
            }
            
        </div>
        <ul>
            { Object.values( entryData.entry_data ).map(( entryDetails, key  ) => (
                <li className='gfp-row' key={ 'view'+ key }>
                    <div className='gfp-field-label'>{  entryDetails.label }</div>
                    <div  >:</div>
                    <div className='gfp-field-value' dangerouslySetInnerHTML={ {__html: getEntryValue( entryDetails ) }} />
                </li>
            ) ) }
        </ul>
        </div>
    );
}

export default EntryViewEdit;