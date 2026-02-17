import { TextControl } from '@wordpress/components';
import { fetchAllIntegrations } from '../api/integrations';
import { useState, useEffect } from '@wordpress/element';
import Activecampaign from '../icons/activecampaign';
import Brevo from '../icons/brevo';
import Mailchimp from '../icons/mailchimp';
import GutenaFormsToggleField from '../components/fields/gutena-forms-toggle-field';
import Settings from '../icons/settings';


const GutenaFormsIntegrations = () => {

    const [ integrations, setIntegrations ] = useState( [] );
    const [ searchTerm, setSearchTerm ] = useState( '' );

    useEffect( () => {

        fetchAllIntegrations( searchTerm )
            .then( integrations => {
                setIntegrations( integrations );
            } )

    }, [ searchTerm ] );

    const IconMap = {
        'active-campaign': Activecampaign,
        'brevo': Brevo,
        'mailchimp': Mailchimp,
    };

    return (
        <div>
            <TextControl
                placeholder="Search available integrations..."
                value={ searchTerm }
                onChange={ ( value ) => setSearchTerm( value ) }
                style={ { marginBottom: '20px' } }
            />

            <div>
                { integrations.length ? (
                    <div className={ 'gutena-forms__integrations' }>
                        { integrations.map( ( integration ) => {

                            const IconComponent = IconMap[ integration.icon ];
                            return (
                                <div className={ 'gutena-forms__integration' }>
                                    <h3>
                                        { IconComponent && <IconComponent /> }
                                        { integration.title }
                                    </h3>
                                    <p>{ integration.desc }</p>

                                    <div className={ 'gutena-forms__integration-actions' }>
                                        <div>
                                            <GutenaFormsToggleField
                                                id={ integration.name }
                                                label={ '' }
                                                desc={ '' }
                                                checked={ integration.enabled }
                                                onChange={ () => {} }
                                            />
                                        </div>
                                        <div>
                                            <Settings />
                                        </div>
                                    </div>
                                </div>
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