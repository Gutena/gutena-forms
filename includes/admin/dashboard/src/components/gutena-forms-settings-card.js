import GutenaFormsDescWrapper from "./gutena-forms-desc-wrapper";
import GutenaFormsToggleField from "./fields/gutena-forms-toggle-field";
import { Button } from '@wordpress/components';
import Settings from "../icons/settings";
import { useState, useEffect } from '@wordpress/element';

const GutenaFormsSettingsCard = ( { title, desc, isEnabled, icon, name, handleSettingsEnable } ) => {

    const [ enabled, setEnabled ] = useState( false );

    useEffect( () => {
        setEnabled( isEnabled );
    }, [] );

    const handleToggleChange = ( value ) => {
        setEnabled( value );

        handleSettingsEnable( value, name );
    }

    return (
        <div className={ 'gutena-forms__integration' }>
            <h3>
                { icon }
                { title }
            </h3>
            <p>
                <GutenaFormsDescWrapper
                    desc={ desc }
                />
            </p>

            <div className={ 'gutena-forms__integration-actions' }>
                <div>
                    <GutenaFormsToggleField
                        id={ name }
                        checked={ enabled }
                        onChange={ handleToggleChange }
                    />
                </div>
                <div>
                    { enabled ? (
                        <Button>
                            <Settings />
                        </Button>
                    ) : <Settings disabled={ true } /> }
                </div>
            </div>
        </div>
    );
};

export default GutenaFormsSettingsCard;