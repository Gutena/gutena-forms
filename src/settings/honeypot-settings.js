import { __experimentalNumberControl as NumberControl, __experimentalVStack as VStack, PanelBody, ToggleControl } from '@wordpress/components';
import { gfIsEmpty } from '../helper';
import { __ } from '@wordpress/i18n';

const HoneypotSettings = ( { honeypot, setAttributes } ) => {

    const handleValueChange = ( key, value ) => {
        setAttributes( {
            honeypot: {
                ...honeypot,
                [ key ]: value,
                defaultSettings: false,
            }
        } );
    }

    return (
        <PanelBody title={ "Honeypot Field" } initialOpen={ false }>
            <VStack>
                <p>Honeypot field settings</p>
                <ToggleControl
                    label={ 'enable' }
                    checked={ honeypot?.enable }
                    onChange={ ( honeypot_status ) => handleValueChange( 'enable', honeypot_status ) }
                />

                {
                    ( ! gfIsEmpty( honeypot?.enable ) && honeypot?.enable ) &&
                    <>
                        <NumberControl
                            label={ __( 'Time limit (in seconds)', 'gutena-forms' ) }
                            value={ honeypot?.timeCheckValue }
                            min={ 1 }
                            onChange={ ( timeCheckValue ) => handleValueChange( 'timeCheckValue', timeCheckValue ) }
                            description={ 'Adds a time-based spam check that detects submissions made too quickly, a common bot behavior. The default threshold is 4 seconds, adjustable as needed. If unsure, leave it off.'}
                        />
                    </>
                }
            </VStack>
        </PanelBody>
    );
};

export default HoneypotSettings;