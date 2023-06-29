import { __ } from '@wordpress/i18n';
import domReady from '@wordpress/dom-ready';
import {createRoot } from '@wordpress/element';
//Form Entry page
import  EntryPage from './components/entry-page/EntryPage';
//Form settings page
import  SettingsPage from './components/settings/SettingsPage';

import { gfpIsEmpty } from './helper';
import './style.scss';

//Set dashboard at HTML id echo by Gutena_Kit_Admin class
domReady( () => {
    let domID = '';
    //Page: Entry View 
    if ( 'undefined' !== typeof gutenaFormsEntryDetails ) {
        domID = document.getElementById("gfp-entry-view");
        if ( ! gfpIsEmpty( domID ) ) {
            const root = createRoot( domID );
            root.render(
                <EntryPage />
            );
        }
    }

    //Page: Settings
    domID = document.getElementById("gfp-page-settings");
    if ( ! gfpIsEmpty( domID ) ) {
        const root = createRoot( domID );
        root.render(
            <SettingsPage />
        );
    }
    
});