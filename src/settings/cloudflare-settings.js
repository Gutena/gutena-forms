import { PanelBody, __experimentalVStack as VStack, TextControl, ToggleControl } from '@wordpress/components';
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
    };

    return (
        <PanelBody title={ __( 'Cloudflare Turnstile', 'gutena-forms' ) } initialOpen={ false }>
            <VStack>
                <p>
                    <a href="https://developers.cloudflare.com/turnstile/" target="_blank" rel="noreferrer">{ __( 'Cloudflare Turnstile', 'gutena-forms' ) }</a>
                    { ' ' }
                    { __( 'helps prevent bots. Use default settings from Forms Settings or enter keys below.', 'gutena-forms' ) }
                </p>

                <ToggleControl
                    label={ __( 'Enable', 'gutena-forms' ) }
                    checked={ cloudflareTurnstile.defaultSettings ? gutenaFormsBlock?.cloudflare_turnstile_defaults?.enable : cloudflareTurnstile?.enable }
                    onChange={ ( turnstile_status ) => handleValueChange( 'enable', turnstile_status ) }
                />

                { ( cloudflareTurnstile.defaultSettings ? gutenaFormsBlock?.cloudflare_turnstile_defaults?.enable : cloudflareTurnstile?.enable ) && (
                    <>
                        <TextControl
                            label={ __( 'Site Key', 'gutena-forms' ) }
                            value={ cloudflareTurnstile.defaultSettings ? gutenaFormsBlock?.cloudflare_turnstile_defaults?.site_key : cloudflareTurnstile?.site_key }
                            onChange={ ( site_key ) => handleValueChange( 'site_key', site_key ) }
                        />
                        <TextControl
                            label={ __( 'Secret Key', 'gutena-forms' ) }
                            value={ cloudflareTurnstile.defaultSettings ? gutenaFormsBlock?.cloudflare_turnstile_defaults?.secret_key : cloudflareTurnstile?.secret_key }
                            onChange={ ( secret_key ) => handleValueChange( 'secret_key', secret_key ) }
                        />
                    </>
                ) }

            </VStack>
        </PanelBody>
    );
};

export default CloudflareSettings;