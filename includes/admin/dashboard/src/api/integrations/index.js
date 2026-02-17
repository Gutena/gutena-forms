import { addQueryArgs } from '@wordpress/url';
import apiFetch from '@wordpress/api-fetch';
import { GutenaFormsRestConfiguration } from '../index';

export async function fetchAllIntegrations() {
    const response = await apiFetch( {
        path: `${GutenaFormsRestConfiguration.namespace}integrations/get-all`,
        method: 'GET',
    } );

    if ( ! response || ! response.integrations ) {
        throw new Error( 'Failed to fetch integrations. Please try again later.' );
    }
    return Object.values( response.integrations );
}

export async function toggleIntegration( toggle, integration ) {
    const response = await apiFetch( {
        path: addQueryArgs( `${GutenaFormsRestConfiguration.namespace}integrations/toggle`, {
            integration: integration,
            toggle: toggle,
        } ),
        method: 'POST',
    } );

    if ( ! response || ! response.success ) {
        throw new Error( 'Failed to update integration status. Please try again later.' );
    }

    return response;
}
