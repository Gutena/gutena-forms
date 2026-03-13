import { PanelBody, __experimentalVStack as VStack, TextControl, ToggleControl } from '@wordpress/components';
import { gfIsEmpty } from '../helper';
import { __ } from '@wordpress/i18n';

const CloudflareSettings = ( { cloudflareTurnstile, setAttributes } ) => {

    const handleValueChange = ( key, value ) => {
        setAttributes( {
            cloudflareTurnstile: {
                ...cloudflareTurnstile,
                [ key ]: value,
                defaultSettings: false,
            }
        } );
    }

    return (
        <PanelBody title="Cloudflare Turnstile" initialOpen={ false }>
            <VStack>
                <p>
                    <a>Cloudflare Turnstile</a>
                </p>

                <ToggleControl
                    label={ 'Enable' }
                    checked={ cloudflareTurnstile?.enable }
                    onChange={ ( turnstile_status ) => handleValueChange( 'enable', turnstile_status ) }
                />

                {
                    ( ! gfIsEmpty( cloudflareTurnstile?.enable ) && cloudflareTurnstile?.enable ) &&
                    <>
                        <TextControl
                            label={ __( 'Site Key', 'gutena-forms' ) }
                            value={ cloudflareTurnstile?.site_key }
                            onChange={ ( site_key ) => handleValueChange( 'site_key', site_key ) }
                        />

                        <TextControl
                            label={ __( 'Secret Key', 'gutena-forms' ) }
                            value={ cloudflareTurnstile?.secret_key }
                            onChange={ ( secret_key ) => handleValueChange( 'secret_key', secret_key ) }
                        />
                    </>
                }

            </VStack>
        </PanelBody>
    );
};

export default CloudflareSettings;