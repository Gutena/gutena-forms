import { useBlockProps } from '@wordpress/block-editor';
import { gfIsEmpty } from '../../shared/utils/helper';

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

export default function Save( { attributes } ) {
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

	const blockProps = useBlockProps.save( {
		className: 'wp-block-gutena-field-group wp-block-gutena-range-field field-group-type-range standalone-range-field',
	} );

	const min = numAttr( minMaxStep?.min );
	const max = numAttr( minMaxStep?.max );
	const step = numAttr( minMaxStep?.step );
	const rangeValue = getRangeValue( defaultValue, minMaxStep );

	const fieldClass = `gutena-forms-field range-field ${ isRequired ? 'required-field' : '' }`.trim();

	return (
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
					defaultValue={ rangeValue }
					min={ min }
					max={ max }
					step={ step }
					required={ isRequired ? 'required' : undefined }
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
						<span className="gf-value range-input-value"></span>
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
	);
}
