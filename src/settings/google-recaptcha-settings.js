import { PanelBody, RadioControl, TextControl, ToggleControl, __experimentalVStack as VStack } from '@wordpress/components';
import { gfIsEmpty } from '../helper';
import { __ } from '@wordpress/i18n';

const GoogleRecaptchaSettings = ( { recaptcha, setAttributes } ) => {
    const handleValueChange = ( key, value ) => {
        setAttributes( {
            recaptcha: {
                ...recaptcha,
                [ key ]: value,
                defaultSettings: false,
            }
        } );
    };

    return (
        <PanelBody title="Google reCAPTCHA" initialOpen={ false }>
            <VStack >
                <p><a href="https://gutenaforms.com/how-to-generate-google-recaptcha-site-key-and-secret-key" target="_blank">{ __( 'reCAPTCHA', 'gutena-forms' ) }</a> { __( ' v3 and v2 help you protect your sites from fraudulent activities, spam, and abuse. By using this integration in your forms, you can block spam form submissions.', 'gutena-forms' ) } </p>

                <ToggleControl
                    label={ __( 'Enable', 'gutena-forms' ) }
                    checked={ recaptcha?.enable }
                    onChange={ ( recaptcha_status ) => handleValueChange( 'enable', recaptcha_status ) }
                />
                { ( ! gfIsEmpty( recaptcha?.enable ) && recaptcha?.enable ) &&
                    <>
                        <RadioControl
                            className="gutena-forms-horizontal-radio"
                            label={ __( 'reCAPTCHA Type', 'gutena-forms' ) }
                            selected={ recaptcha?.type }
                            options={ [
                                { label: 'v2', value: 'v2' },
                                { label: 'v3', value: 'v3' },
                            ] }
                            onChange={ ( type ) => handleValueChange( 'type', type ) }
                        />

                        <TextControl
                            label={ __( 'Site Key', 'gutena-forms' ) }
                            value={ recaptcha?.site_key }
                            onChange={ ( site_key ) => handleValueChange( 'site_key', site_key )}
                        />
                        <TextControl
                            label={ __( 'Secret key', 'gutena-forms' ) }
                            value={ recaptcha?.secret_key }
                            onChange={ ( secret_key ) => handleValueChange( 'secret_key', secret_key )}
                        />
                    </>
                }
            </VStack>
        </PanelBody>
    );
};

export default GoogleRecaptchaSettings;