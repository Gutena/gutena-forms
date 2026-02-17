import { addQueryArgs } from '@wordpress/url';
import apiFetch from '@wordpress/api-fetch';
import { GutenaFormsRestConfiguration } from '../index';

export async function fetchAllIntegrations( searchTerm ) {
    const response = await apiFetch( {
        path: `${GutenaFormsRestConfiguration.namespace}integrations/get-all`,
        method: 'GET',
    } );


    let integrations = [
        {
            title: 'Mailchimp',
            desc: 'Connect your forms to Mailchimp and automatically add new contacts to your email lists.',
            icon: 'mailchimp',
            enabled: true,
            name: 'mailchimp',
        },
        {
            title: 'Active Campaign',
            desc: 'Integrate with Active Campaign to manage your contacts and automate your marketing efforts.',
            icon: 'active-campaign',
            enabled: false,
            name: 'active-campaign',
        },
        {
            title: 'Brevo',
            desc: 'Connect to Brevo (formerly Sendinblue) to sync your form submissions with your email marketing campaigns.',
            icon: 'brevo',
            enabled: true,
            name: 'brevo',
        }
    ];

    return integrations.filter( integration => {
        if ( String( searchTerm ).trim().length ) {
            return (
                integration.title.toLowerCase().includes( searchTerm.toLowerCase() ) || integration.description.toLowerCase().includes( searchTerm.toLowerCase() )
            );
        }
        return true;
    } );
}
