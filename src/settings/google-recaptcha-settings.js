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

    const getValue = ( type, version ) => {
        if ( recaptcha?.defaultSettings ) {
            if ( gfIsEmpty( gutenaFormsBlock?.grecaptcha[ `${ version }_${ type }` ] ) ) {
                return gutenaFormsBlock?.grecaptcha?.site_key;
            } else {
                return gutenaFormsBlock?.grecaptcha[ `${ version }_${ type }` ];
            }
        } else {
            if ( gfIsEmpty( recaptcha[ `${ version }_${ type }` ] ) ) {
                return recaptcha?.site_key;
            } else {
                return recaptcha[ `${ version }_${ type }` ];
            }
        }
    }

    return (
        <PanelBody title="Google reCAPTCHA" initialOpen={ false }>
            <VStack >
                <p><a href="https://gutenaforms.com/how-to-generate-google-recaptcha-site-key-and-secret-key" target="_blank">{ __( 'reCAPTCHA', 'gutena-forms' ) }</a> { __( ' v3 and v2 help you protect your sites from fraudulent activities, spam, and abuse. By using this integration in your forms, you can block spam form submissions.', 'gutena-forms' ) } </p>

                <ToggleControl
                    label={ __( 'Enable', 'gutena-forms' ) }
                    checked={ recaptcha?.defaultSettings ? gutenaFormsBlock?.grecaptcha?.enable : recaptcha?.enable }
                    onChange={ ( recaptcha_status ) => handleValueChange( 'enable', recaptcha_status ) }
                />
                {  ( recaptcha?.defaultSettings ? ( ! gfIsEmpty( gutenaFormsBlock?.grecaptcha?.enable ) && gutenaFormsBlock?.grecaptcha?.enable ) : ( ! gfIsEmpty( recaptcha?.enable ) && recaptcha?.enable ) ) &&
                    <>
                        <RadioControl
                            className="gutena-forms-horizontal-radio"
                            label={ __( 'reCAPTCHA Type', 'gutena-forms' ) }
                            selected={ recaptcha?.defaultSettings ? gutenaFormsBlock?.grecaptcha?.type : recaptcha?.type }
                            options={ [
                                { label: 'v2', value: 'v2' },
                                { label: 'v3', value: 'v3' },
                            ] }
                            onChange={ ( type ) => handleValueChange( 'type', type ) }
                        />

                        { ( 'v2' === recaptcha?.type ) && (
                            <>
                                <TextControl
                                    label={ __( 'V2 Site Key', 'gutena-forms' ) }
                                    value={  getValue( 'site_key', 'v2' ) }
                                    onChange={ ( site_key ) => handleValueChange( 'v2_site_key', site_key )}
                                />
                                <TextControl
                                    label={ __( 'V2 Secret key', 'gutena-forms' ) }
                                    value={  getValue( 'secret_key', 'v2' ) }
                                    onChange={ ( secret_key ) => handleValueChange( 'v2_secret_key', secret_key )}
                                />
                            </>
                        ) }

                        { ( 'v3' === recaptcha.type ) && (
                            <>
                                <TextControl
                                    label={ __( 'V3 Site Key', 'gutena-forms' ) }
                                    value={  getValue( 'site_key', 'v3' ) }
                                    onChange={ ( site_key ) => handleValueChange( 'v3_site_key', site_key )}
                                />
                                <TextControl
                                    label={ __( 'V3 Secret key', 'gutena-forms' ) }
                                    value={  getValue( 'secret_key', 'v3' ) }
                                    onChange={ ( secret_key ) => handleValueChange( 'v3_secret_key', secret_key )}
                                />
                            </>
                        ) }
                    </>
                }
            </VStack>
        </PanelBody>
    );
};

export default GoogleRecaptchaSettings;