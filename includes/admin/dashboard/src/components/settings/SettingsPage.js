import { __ } from '@wordpress/i18n';
import { TabPanel } from '@wordpress/components';
import StatusTab  from './StatusTab';
import UserAccessTab from './UserAccessTab';
import TagsTab from './TagsTab';
import { lockIcon } from '../icon';
import { gfpIsEmpty } from '../../helper';

const SettingsPage = (props) => {

    return(
        <div className='gfp-settings-page' >
            <TabPanel
                className="gfp-tab-panel"
                activeClass="active-tab"
                onSelect={ () => {} }
                tabs={ [
                    {
                        name:'status',
                        title:__( 'Status', 'gutena-forms' ),
                        description:__( 'Manage status of your form entries from here', 'gutena-forms' ),
                        className: 'status',
                        component:StatusTab
                    },
                    {
                        name:'tags',
                        title:__( 'Tags', 'gutena-forms' ),
                        description:__( 'Manage tags of your form entries from here', 'gutena-forms' ),
                        className: 'integrations',
                        component:TagsTab
                    },
                    {
                        name:'useraccess',
                        title:__( 'User Access', 'gutena-forms' ),
                        description:__( 'Manage user access for your form entries from here', 'gutena-forms' ),
                        className: 'useraccess',
                        component:UserAccessTab
                    },
                    
                ] }
            >
                { ( tab ) => {
                    return(
                        <>
                            <h2 className='title'>{ tab.title } { lockIcon() }</h2>
                            <p className='description' >{ tab.description }</p>
                            <div 
                            onClick={ () => {
                                let modalEl = document.querySelector('#gutena-forms-go-pro-modal');
                                if ( ! gfpIsEmpty( modalEl ) ) {
                                    modalEl.style.display = "block";
                                }
                            } }
                            >
                            <tab.component />
                            </div>
                        </>
                    )
                } }
            </TabPanel>
        </div>
    );
}

export default SettingsPage;