/**
 * Range Unit control :
 * unit selection with set space attributes
 */
import { __ } from '@wordpress/i18n';
import {
    RangeControl,
    __experimentalUseCustomUnits as useCustomUnits,
    __experimentalUnitControl as UnitControl,
    __experimentalParseQuantityAndUnitFromRawValue as parseQuantityAndUnitFromRawValue,
} from '@wordpress/components';
import { gfIsEmpty } from '../helper';
 
const RangeControlUnit = ( props ) => {
    const {
        rangeLabel,
        attrValue,
        onChangeFunc,
        rangeMin,
        rangeMax,
        rangeStep,
        attrUnits = [ 'px', 'em', 'rem', 'vh', 'vw' ]
    } = props;

    const units = useCustomUnits( {
        availableUnits: attrUnits,
        defaultValues: { px: 0, em: 0, rem: 0, vh: 0, vw: 0, '%': 0 },
    } );

    const getQtyOrunit = ( rawUnit, quantityOrUnit = 'unit' ) => {
        const [ quantityToReturn, unitToReturn ] = parseQuantityAndUnitFromRawValue( rawUnit );

        let unit =
            'undefined' === typeof unitToReturn || null === unitToReturn
                ? 'px'
                : unitToReturn;

        return 'unit' === quantityOrUnit ? unit : quantityToReturn;
    };

    return (
        <>
            <fieldset className="components-border-radius-control">
                <legend>{ gfIsEmpty( rangeLabel ) ? '' : rangeLabel }</legend>
                <div className="components-border-radius-control__wrapper">
                    <UnitControl
                        units={ units }
                        value={ attrValue }
                        onChange={ ( attrValue ) => onChangeFunc( attrValue ) }
                        className="components-border-radius-control__unit-control"
                        size={ '__unstable-large' }
                    /> 
                    <RangeControl
                        value={ getQtyOrunit( attrValue, 'Qty' ) }
                        withInputField={ false }
                        onChange={ ( qty ) => onChangeFunc( qty + getQtyOrunit( attrValue ) ) }
                        min={ rangeMin }
                        max={ rangeMax[ getQtyOrunit( attrValue ) ] }
                        step={ rangeStep }
                        className="components-border-radius-control__range-control"
                    />
                </div>
            </fieldset>
        </>
    );
};

export default RangeControlUnit;