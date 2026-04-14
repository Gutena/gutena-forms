import { useEffect, useState } from '@wordpress/element';
import { RadioControl } from '@wordpress/components';

const GutenaFormsRadioGroup = ( { id, desc, label, value, options, onChange } ) => {

    const [ selectedValue, setSelectedValue ] = useState( '' );

    useEffect( () => {
        setSelectedValue( value );
    }, [] );

    const handleChange = ( newValue ) => {

        setSelectedValue( newValue );
        if ( onChange ) {
            onChange( newValue );
        }
    }

    return (
        <div className={ 'gutena-forms__radio-group-control' }>
            { label && (
                <label htmlFor={ id } className={ 'gutena-forms__field-label' }>
                    { label }
                </label>
            ) }

            { options && (
                <div
                    className={ 'gutena-forms__radio-group-options' }
                >
                    { Object.keys( options ).map( ( optionKey, index ) => {
                        return (
                            <div
                                key={ index }
                                className={ 'gutena-forms__radio-option' }
                            >
                                <input
                                    className={ 'gutena-forms__radio-option-input' }
                                    type="radio"
                                    id={ optionKey }
                                    name={ id }
                                    value={ optionKey }
                                    checked={ selectedValue === optionKey }
                                    onChange={ ( e ) => handleChange( e.target.value ) }
                                />
                                <label
                                    className={ 'gutena-forms__radio-option-label' }
                                    htmlFor={ optionKey }
                                >{ options[ optionKey ] }</label>
                            </div>
                        );
                    } ) }
                </div>
            ) }

            { desc && (
                <p className={ 'gutena-forms__field-description' }>{ desc }</p>
            ) }
        </div>
    );
};

export default GutenaFormsRadioGroup;