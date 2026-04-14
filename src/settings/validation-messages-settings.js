import { PanelBody, TextControl } from '@wordpress/components';
import { gfIsEmpty } from '../helper';
import { __ } from '@wordpress/i18n';

const ValidationMessagesSettings = ( { messages, setAttributes } ) => {

    const handleValueChange = ( key, value ) => {
        setAttributes( {
            messages: {
                ...messages,
                [ key ]: value,
                defaultSettings: false,
            }
        } );
    }

    return (
        <PanelBody title={__( 'Messages', 'gutena-forms' ) } initialOpen={ false }>
            <TextControl
                type="text"
                label={ __( 'Required Field', 'gutena-forms' ) }
                value={ gfIsEmpty( messages?.required_msg ) ? '' : messages?.required_msg }
                onChange={ ( required_msg ) => handleValueChange( 'required_msg', required_msg ) }
                placeholder={ gutenaFormsBlock?.required_msg }
            />
            <TextControl
                type="text"
                label={ __( 'Required Select Field', 'gutena-forms' ) }
                value={ gfIsEmpty( messages?.required_msg_select ) ? '' : messages?.required_msg_select }
                onChange={ ( required_msg_select ) => handleValueChange( 'required_msg_select', required_msg_select ) }
                placeholder={ gutenaFormsBlock?.required_msg_select }
            />
            <TextControl
                type="text"
                label={ __( 'Required Checkbox or Radio Field', 'gutena-forms' ) }
                value={ gfIsEmpty( messages?.required_msg_check ) ? '' : messages?.required_msg_check }
                onChange={ ( required_msg_check ) => handleValueChange( 'required_msg_check', required_msg_check ) }
                placeholder={ gutenaFormsBlock?.required_msg_check }
            />
            <TextControl
                type="text"
                label={ __( 'Required Opt-in checkbox', 'gutena-forms' ) }
                value={ gfIsEmpty( messages?.required_msg_optin ) ? '' : messages?.required_msg_optin }
                onChange={ ( required_msg_optin ) => handleValueChange( 'required_msg_optin', required_msg_optin ) }
                help={ __( 'Privacy policy, Terms', 'gutena-forms' ) }
                placeholder={ gutenaFormsBlock?.required_msg_optin }
            />
            <TextControl
                type="text"
                label={ __( 'Invalid Email', 'gutena-forms' ) }
                value={ gfIsEmpty( messages?.invalid_email_msg ) ? '' : messages?.invalid_email_msg }
                onChange={ ( invalid_email_msg ) => handleValueChange( 'invalid_email_msg', invalid_email_msg ) }
                placeholder={ gutenaFormsBlock?.invalid_email_msg }
            />
            <TextControl
                type="text"
                label={ __( 'Minimum value', 'gutena-forms' ) }
                value={ gfIsEmpty( messages?.min_value_msg ) ? '' : messages?.min_value_msg }
                onChange={ ( min_value_msg ) => handleValueChange( 'min_value_msg', min_value_msg ) }
                placeholder={ gutenaFormsBlock?.min_value_msg }
            />
            <TextControl
                type="text"
                label={ __( 'Maximum value', 'gutena-forms' ) }
                value={ gfIsEmpty( messages?.max_value_msg ) ? '' : messages?.max_value_msg }
                onChange={ ( max_value_msg ) => handleValueChange( 'max_value_msg', max_value_msg ) }
                placeholder={ gutenaFormsBlock?.max_value_msg }
            />
        </PanelBody>
    );
};

export default ValidationMessagesSettings;