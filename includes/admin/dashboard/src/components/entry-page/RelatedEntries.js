import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { lockIcon } from '../icon';

const RelatedEntries = (props) => {
   
    return(
        <PanelBody 
            icon={ lockIcon() }
            iconPosition='right'
            className='related-section '
            title={ __( 'Related Entries' ) } 
            initialOpen={ true }
        >
            <p>{ __( 'The user who created this entry also submitted the entries below', 'gutena-forms-pro' ) }</p>
            <ul className='details'>
                <li>
                    <span >----------------</span>
                </li>
                <li>
                    <span >----------------</span>
                </li>
            </ul>
        </PanelBody>
    );
}

export default RelatedEntries;