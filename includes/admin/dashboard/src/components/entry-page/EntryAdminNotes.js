import { __ } from '@wordpress/i18n';
import { PanelBody, Button, TextareaControl, Modal } from '@wordpress/components';
import { lockIcon } from '../icon';

const EntryAdminNotes = ( props ) => {
    

    return(
        <div className='notes-section no-panel-section' >
        <h2 className='title' >{ __( 'Notes' ) } { lockIcon() }</h2>
            <Button 
                label={ __( 'Add Notes', 'gutena-forms-pro' ) }
                variant="secondary"
                onClick={ () => {} }
                disabled={ true }
            >
                    { __( 'Add Notes', 'gutena-forms-pro' ) }
            </Button>
        </div>
    );
}

export default EntryAdminNotes;