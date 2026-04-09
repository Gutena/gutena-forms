import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import { select } from '@wordpress/data';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	PanelRow,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { gfIsEmpty, gfSanitizeName } from '../../shared/utils/helper';

const isFieldNameAttrReserved = ( nameAttrCheck, clientIdCheck ) => {
	const blocksClientIds = select( 'core/block-editor' ).getClientIdsWithDescendants();
	return gfIsEmpty( blocksClientIds )
		? false
		: blocksClientIds.some( ( blockClientId ) => {
				const attrs = select( 'core/block-editor' ).getBlockAttributes( blockClientId );
				return (
					clientIdCheck !== blockClientId &&
					! gfIsEmpty( attrs?.nameAttr ) &&
					attrs.nameAttr === nameAttrCheck
				);
		  } );
};

function numAttr( v ) {
	if ( gfIsEmpty( v ) && v !== 0 && v !== '0' ) {
		return undefined;
	}
	return v;
}

function getRangeValue( defaultValue, minMaxStep ) {
	if ( ! gfIsEmpty( defaultValue ) || defaultValue === '0' ) {
		return String( defaultValue );
	}
	if ( ! gfIsEmpty( minMaxStep?.min ) || minMaxStep?.min === 0 || minMaxStep?.min === '0' ) {
		return String( minMaxStep.min );
	}
	return '';
}

export default function Edit( { attributes, setAttributes, clientId } ) {
	const {
		nameAttr,
		fieldName,
		isRequired,
		defaultValue,
		minMaxStep,
		preFix,
		sufFix,
		description,
	} = attributes;

	const [ rangeVal, setRangeVal ] = useState( () => getRangeValue( defaultValue, minMaxStep ) );

	useEffect( () => {
		setRangeVal( getRangeValue( defaultValue, minMaxStep ) );
	}, [ defaultValue, minMaxStep ] );

	useEffect( () => {
		if ( ! gfIsEmpty( nameAttr ) && ! isFieldNameAttrReserved( nameAttr, clientId ) ) {
			return;
		}

		for ( let index = 0; index < 5000; index++ ) {
			const nextName = `f_${ index }`;
			if ( ! isFieldNameAttrReserved( nextName, clientId ) ) {
				setAttributes( { nameAttr: nextName } );
				break;
			}
		}
	}, [] );

	const blockProps = useBlockProps( {
		className: 'wp-block-gutena-field-group wp-block-gutena-range-field field-group-type-range standalone-range-field',
	} );

	const min = numAttr( minMaxStep?.min );
	const max = numAttr( minMaxStep?.max );
	const step = numAttr( minMaxStep?.step );

	const fieldClass = `gutena-forms-field range-field ${ isRequired ? 'required-field' : '' }`.trim();

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Field settings', 'gutena-forms' ) } initialOpen={ true }>
					<TextControl
						label={ __( 'Label', 'gutena-forms' ) + ' *' }
						value={ fieldName ?? '' }
						onChange={ ( nextLabel ) => {
							const updates = { fieldName: nextLabel };
							if ( gfIsEmpty( nameAttr ) || 0 === nameAttr.indexOf( 'f_' ) ) {
								updates.nameAttr = gfSanitizeName( nextLabel );
							}
							setAttributes( updates );
						} }
					/>
					<TextControl
						label={ __( 'Name attribute', 'gutena-forms' ) + ' *' }
						value={ nameAttr ?? '' }
						onChange={ ( nextNameAttr ) => setAttributes( { nameAttr: gfSanitizeName( nextNameAttr ) } ) }
						help={ __( 'Used as input name in form submission.', 'gutena-forms' ) }
					/>
					<h2 className="block-editor-block-card__title gf-mt-1">{ __( 'Value', 'gutena-forms' ) }</h2>
					<PanelRow>
						<TextControl
							label={ __( 'Minimum', 'gutena-forms' ) }
							value={ minMaxStep?.min }
							type="number"
							onChange={ ( nextMin ) =>
								setAttributes( {
									minMaxStep: {
										...minMaxStep,
										min: nextMin,
									},
								} )
							}
						/>
						<TextControl
							label={ __( 'Maximum', 'gutena-forms' ) }
							value={ minMaxStep?.max }
							type="number"
							onChange={ ( nextMax ) =>
								setAttributes( {
									minMaxStep: {
										...minMaxStep,
										max: nextMax,
									},
								} )
							}
						/>
						<TextControl
							label={ __( 'Step', 'gutena-forms' ) }
							value={ minMaxStep?.step }
							type="number"
							onChange={ ( nextStep ) =>
								setAttributes( {
									minMaxStep: {
										...minMaxStep,
										step: nextStep,
									},
								} )
							}
						/>
					</PanelRow>
					<PanelRow>
						<TextControl
							label={ __( 'Prefix', 'gutena-forms' ) }
							value={ preFix ?? '' }
							onChange={ ( v ) => setAttributes( { preFix: v } ) }
						/>
						<TextControl
							label={ __( 'Suffix', 'gutena-forms' ) }
							value={ sufFix ?? '' }
							onChange={ ( v ) => setAttributes( { sufFix: v } ) }
						/>
					</PanelRow>
					<TextControl
						label={ __( 'Default value', 'gutena-forms' ) }
						value={ defaultValue ?? '' }
						onChange={ ( v ) => setAttributes( { defaultValue: v } ) }
					/>
					<ToggleControl
						label={ __( 'Required', 'gutena-forms' ) }
						checked={ !! isRequired }
						onChange={ ( nextRequired ) => setAttributes( { isRequired: nextRequired } ) }
					/>
					<TextControl
						label={ __( 'Help text', 'gutena-forms' ) }
						value={ description ?? '' }
						onChange={ ( nextDescription ) => setAttributes( { description: nextDescription } ) }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<label htmlFor={ nameAttr } className="heading-input-label-gutena">
					{ fieldName }
					{ isRequired ? ' *' : '' }
				</label>
				<div className="gf-range-container">
					<input
						id={ nameAttr }
						name={ nameAttr }
						type="range"
						className={ fieldClass }
						value={ rangeVal }
						min={ min }
						max={ max }
						step={ step }
						onChange={ ( e ) => {
							const v = e.target.value;
							setRangeVal( v );
							setAttributes( { defaultValue: v } );
						} }
					/>
					<p className="gf-range-values">
						{ ! gfIsEmpty( minMaxStep?.min ) || minMaxStep?.min === 0 || minMaxStep?.min === '0' ? (
							<span className="gf-prefix-value-wrapper">
								<span className="gf-prefix">{ preFix || '' }</span>
								<span className="gf-value">{ minMaxStep.min }</span>
								<span className="gf-suffix">{ sufFix || '' }</span>
							</span>
						) : null }
						<span className="gf-prefix-value-wrapper">
							<span className="gf-prefix">{ preFix || '' }</span>
							<span className="gf-value range-input-value">{ rangeVal }</span>
							<span className="gf-suffix">{ sufFix || '' }</span>
						</span>
						{ ! gfIsEmpty( minMaxStep?.max ) || minMaxStep?.max === 0 || minMaxStep?.max === '0' ? (
							<span className="gf-prefix-value-wrapper">
								<span className="gf-prefix">{ preFix || '' }</span>
								<span className="gf-value">{ minMaxStep.max }</span>
								<span className="gf-suffix">{ sufFix || '' }</span>
							</span>
						) : null }
					</p>
				</div>
				{ ! gfIsEmpty( description ) && <p className="gutena-forms-range-field-description">{ description }</p> }
				<p className="gutena-forms-field-error-msg" />
			</div>
		</>
	);
}
