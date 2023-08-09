/**
 * Select Field Type :
 */
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import {
    Icon,
    SelectControl
} from '@wordpress/components';
import { lockIcon } from '../icon';
import { gfIsEmpty } from '../helper';
const noop = () => {};
const SelectFieldType = ( {
    fieldType,
    fieldTypes,
    newFieldTypes = undefined,
    onChangeFunc = noop
} ) => {
    const notANewFieldType = ( gfIsEmpty( newFieldTypes ) || 0 === newFieldTypes.length );
   
    const [ displayOption , setDisplayOption ] = useState( false );

    const proFields = [ 'Date', 'Time', 'Rating', 'Phone', 'Country', 'State', 'File Upload', 'Url', 'Hidden', 'Password' ];

    return (
         notANewFieldType ? 
        (
        <div className='gf-select-field-type-input'>
        <div className='gf-select-field-type-control' >
            <SelectControl
                value={ fieldType }
                options={ fieldTypes }
                onChange={ ( fieldType ) =>
                    onChangeFunc( fieldType )
                }
                help={ __(
                    'Select appropriate field type for input',
                    'gutena-forms'
                ) }
                __nextHasNoMarginBottom
            />
            <div className='gf-select-overlay'
            onClick={ ( e ) => {
                    e.preventDefault();
                    setDisplayOption( ! displayOption );
                }
            } ></div>
        </div>
        {
            displayOption && (
            <ul className='gf-select-field-types' >
                {
                    fieldTypes.map( ( item, index ) => (
                        <li 
                        key={ 'gf-select-option-'+index }
                        className='gf-select-option'
                        onClick={ () => {
                            onChangeFunc( item.value );
                            setDisplayOption( ! displayOption );
                         } }
                        >
                            { item.label }
                        </li>
                    ))
                }
                <li className='gf-seprator'></li>
                <li
                className='gf-select-pro-options'
                >
                    <div className='gf-title-link-wrapper'>
                        <div className='gf-title-icon'>
                            <span className='gf-title'>
                                { __( 'Pro', 'gutena-forms' ) }
                            </span>
                            <span className='gf-icon'>
                                { lockIcon() }
                            </span>
                        </div>
                        <div className='gf-link'>
                            <a 
                            href={ gutenaFormsBlock?.pricing_link } 
                            target='_blank'
                            >
                                { __( 'Upgrade Now', 'gutena-forms' ) }
                            </a>
                            <br />
                            <p className='gf-text-muted'>
                                { __( '14-day free trial', 'gutena-forms' ) }
                            </p>
                        </div>
                    </div> 
                    <ul className='gf-pro-fields'>
                        {
                            proFields.map( ( pitem, pindex ) => (
                                <li 
                                key={ 'gf-pro-fi-eld-option-'+pindex }
                                className='gf-text-muted'
                                >
                                    { pitem }
                                </li>
                            ))
                        }
                    </ul>
                </li>
            </ul>
            )
        }
        </div> 
        ):
        (
            <SelectControl
                value={ fieldType }
                options={ fieldTypes }
                onChange={ ( fieldType ) =>
                    onChangeFunc( fieldType )
                }
                help={ __(
                    'Select appropriate field type for input',
                    'gutena-forms'
                ) }
                __nextHasNoMarginBottom
            />
        )
    );
};

export default SelectFieldType;