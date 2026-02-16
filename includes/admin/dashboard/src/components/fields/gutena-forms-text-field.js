import { TextControl } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';

const GutenaFormsTextField = ( { onChange, label, id, desc, value, placeholder } ) => {

    const [ fieldValue, setFieldValue ] = useState( '' );

    useEffect( () => {
        setFieldValue( value || '' );
    }, [] );

    const handleChange = ( newValue ) => {
        setFieldValue( newValue );
        if ( onChange ) {
            onChange( newValue );
        }
    }

    return (
        <div className={ 'gutena-forms__text-control' }>
            <TextControl
                className={ 'gutena-forms__text-control-input' }
                id={ id }
                label={ label }
                value={ fieldValue }
                onChange={ handleChange }
                placeholder={ placeholder }
            />
            { desc && (
                <p className={ 'gutena-forms__field-description' }>{ desc }</p>
            ) }
        </div>
    );
};

export default GutenaFormsTextField;