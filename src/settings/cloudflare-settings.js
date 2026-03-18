import { PanelBody, __experimentalVStack as VStack, TextControl, ToggleControl } from '@wordpress/components';
import { gfIsEmpty } from '../helper';
import { __ } from '@wordpress/i18n';

const CloudflareSettings = ( { cloudflareTurnstile, setAttributes, cloudflareTurnstileDefaults = {} } ) => {

    const handleValueChange = ( key, value ) => {
        setAttributes( {
            cloudflareTurnstile: {
                ...cloudflareTurnstile,
                [ key ]: value,
                defaultSettings: key === 'enable' ? ( cloudflareTurnstile?.defaultSettings ?? true ) : false,
            }
        } );
    };

    const useDefaultSettings = cloudflareTurnstile?.defaultSettings !== false;
    const defaultHasKeys = ! gfIsEmpty( cloudflareTurnstileDefaults?.site_key ) && ! gfIsEmpty( cloudflareTurnstileDefaults?.secret_key );
    const showKeyFields = ! gfIsEmpty( cloudflareTurnstile?.enable ) && cloudflareTurnstile?.enable && ( ! useDefaultSettings || ! defaultHasKeys );

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
                    checked={ cloudflareTurnstile?.enable }
                    onChange={ ( turnstile_status ) => handleValueChange( 'enable', turnstile_status ) }
                />

                { ( ! gfIsEmpty( cloudflareTurnstile?.enable ) && cloudflareTurnstile?.enable ) && useDefaultSettings && defaultHasKeys && (
                    <p className="description">
                        { __( 'Using default settings from Forms Settings.', 'gutena-forms' ) }
                        { cloudflareTurnstileDefaults?.site_key && (
                            <span> { __( 'Site Key:', 'gutena-forms' ) } { String( cloudflareTurnstileDefaults.site_key ).slice( 0, 12 ) }…</span>
                        ) }
                    </p>
                ) }

                { showKeyFields && (
                    <>
                        <TextControl
                            label={ __( 'Site Key', 'gutena-forms' ) }
                            value={ cloudflareTurnstile?.site_key ?? '' }
                            onChange={ ( site_key ) => handleValueChange( 'site_key', site_key ) }
                            placeholder={ useDefaultSettings && cloudflareTurnstileDefaults?.site_key ? cloudflareTurnstileDefaults.site_key : undefined }
                        />
                        <TextControl
                            label={ __( 'Secret Key', 'gutena-forms' ) }
                            value={ cloudflareTurnstile?.secret_key ?? '' }
                            onChange={ ( secret_key ) => handleValueChange( 'secret_key', secret_key ) }
                            placeholder={ useDefaultSettings && cloudflareTurnstileDefaults?.secret_key ? '••••••••' : undefined }
                        />
                    </>
                ) }

            </VStack>
        </PanelBody>
    );
};

export default CloudflareSettings;