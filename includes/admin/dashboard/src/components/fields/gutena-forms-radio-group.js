import { useEffect, useState } from '@wordpress/element';

const GutenaFormsRadioGroup = ( { id, desc, label, value, options, onChange, disabled = false } ) => {

    const [ selectedValue, setSelectedValue ] = useState( '' );

    useEffect( () => {
        setSelectedValue( value );
    }, [ value ] );

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
                        const isSelected = selectedValue === optionKey;
                        return (
                            <div
                                key={ index }
                                className={
                                    'gutena-forms__radio-option' +
                                    ( isSelected ? ' is-selected' : '' )
                                }
                            >
                                <span className="gutena-forms__radio-dot">
                                    { isSelected && (
                                        <span className="gutena-forms__radio-dot-inner" />
                                    ) }
                                </span>
                                <label
                                    className={ 'gutena-forms__radio-option-label' }
                                    htmlFor={ `${ id }-${ optionKey }` }
                                >{ options[ optionKey ] }</label>
                                <input
                                    className={ 'gutena-forms__radio-option-input' }
                                    type="radio"
                                    id={ `${ id }-${ optionKey }` }
                                    name={ id }
                                    value={ optionKey }
                                    checked={ isSelected }
                                    onChange={ ( e ) => handleChange( e.target.value ) }
                                    disabled={ disabled }
                                />
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