import { TextControl, Button } from '@wordpress/components';
import { fetchAllIntegrations, toggleIntegration } from '../api/integrations';
import { useState, useEffect } from '@wordpress/element';
import Activecampaign from '../icons/activecampaign';
import Brevo from '../icons/brevo';
import Mailchimp from '../icons/mailchimp';
import GutenaFormsSettingsCard from "../components/gutena-forms-settings-card";
import { toast } from 'react-toastify';


const GutenaFormsIntegrations = () => {

    const [ integrations, setIntegrations ] = useState( [] );
    const [ tempIntegrations, setTempIntegrations ] = useState( [] );

    useEffect( () => {

        fetchAllIntegrations()
            .then( integrations => {
                setIntegrations( integrations );
                setTempIntegrations( integrations );
            } )

    }, [] );

    const IconMap = {
        'active-campaign': Activecampaign,
        'brevo': Brevo,
        'mailchimp': Mailchimp,
    };

    const handleSearchIntegrations = ( value ) => {
        if ( String( value ).trim().length ) {
            const filtered = integrations.filter( integration => {
                return integration.title.toLowerCase().includes( value.toLowerCase() );
            } );
            setTempIntegrations( filtered );
        } else {
            setTempIntegrations( integrations );
        }
    };

    const handleEnableIntegration = ( value, name ) => {
        toggleIntegration( value, name )
            .then( response => {
                toast.success( response.message );
            } );
    }

    return (
        <div>
            <TextControl
                placeholder="Search available integrations..."
                onChange={ handleSearchIntegrations }
                style={ { marginBottom: '20px' } }
            />

            <div>
                { tempIntegrations.length ? (
                    <div className={ 'gutena-forms__integrations' }>
                        { tempIntegrations.map( ( integration, key ) => {
                            const IconComponent = IconMap[ integration.icon ];

                            return (
                                    <GutenaFormsSettingsCard
                                        title={ integration.title }
                                        desc={ integration.desc }
                                        isEnabled={ integration.enabled }
                                        name={ integration.name }
                                        icon={ IconComponent ? <IconComponent /> : null }
                                        handleSettingsEnable={ handleEnableIntegration }
                                    />
                            );
                        } ) }
                    </div>
                ) : (
                    <p>No integrations found.</p>
                ) }
            </div>
        </div>
    );
};

export default GutenaFormsIntegrations;