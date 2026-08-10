/**
 * A single condition row inside the ConditionBuilderModal.
 *
 * Renders: [AND badge] [field select] [operator select] [value input] [trash].
 * Shows a warning Notice when the referenced field no longer exists among
 * siblings (user story section D).
 */

import { __ } from '@wordpress/i18n';
import { useMemo } from '@wordpress/element';
import { SelectControl, TextControl, FormTokenField, Notice, Button } from '@wordpress/components';
import {
	getOperatorOptions,
	operatorNeedsValue,
	operatorIsMulti,
	CHOICE_FIELD_TYPES,
} from './operators';

function noop() {}

export default function ConditionRow( {
	condition,
	siblings,
	onChange,
	onRemove,
	onAdd,
} ) {
	const { field, operator, value } = condition;

	const referenced = useMemo( () => {
		if ( ! field ) {
			return null;
		}
		return siblings.find( ( s ) => s.nameAttr === field ) || null;
	}, [ field, siblings ] );

	const isMissing = ! ! field && ! referenced;

	const fieldOptions = useMemo( () => {
		const opts = [
			{ value: '', label: __( 'Select field', 'gutena-forms' ) },
			...siblings.map( ( s ) => ( {
				value: s.nameAttr,
				label: s.fieldName || s.nameAttr,
			} ) ),
		];
		return opts;
	}, [ siblings ] );

	const operatorOptions = useMemo(
		() => getOperatorOptions( referenced ? referenced.fieldType : 'text' ),
		[ referenced ]
	);

	const showValue = operatorNeedsValue( operator );
	const isMulti = operatorIsMulti( operator );
	const isChoice = referenced && CHOICE_FIELD_TYPES.indexOf( referenced.fieldType ) !== -1;
	const choiceOptions = referenced && Array.isArray( referenced.options ) ? referenced.options : [];

	const handleFieldChange = ( newField ) => {
		const newRef = siblings.find( ( s ) => s.nameAttr === newField ) || null;
		const newFieldType = newRef ? newRef.fieldType : 'text';
		const ops = getOperatorOptions( newFieldType );
		const firstOp = ops.length ? ops[ 0 ].value : '';
		onChange( {
			field: newField,
			operator: firstOp,
			value: '',
		} );
	};

	const handleOperatorChange = ( newOperator ) => {
		// Reset value when toggling into / out of no-value operators.
		onChange( { operator: newOperator, value: operatorNeedsValue( newOperator ) ? value : '' } );
	};

	const handleValueChange = ( newValue ) => {
		onChange( { value: newValue } );
	};

	return (
		<div className="gf-condition-row">
			<div className="gf-condition-row__line">
				<SelectControl
					value={ field }
					options={ fieldOptions }
					onChange={ handleFieldChange }
					__nextHasNoMarginBottom
				/>

				<SelectControl
					value={ operator }
					options={ operatorOptions }
					onChange={ handleOperatorChange }
					__nextHasNoMarginBottom
				/>

				{ showValue && renderValueInput( {
					isMulti,
					isChoice,
					choiceOptions,
					value,
					onChange: handleValueChange,
				} ) }

				<Button
					variant="secondary"
					text={ __( 'And', 'gutena-forms' ) }
					onClick={ onAdd }
					className="gf-condition-row__and"
				/>

				<Button
					className="gf-condition-row__remove"
					icon="trash"
					label={ __( 'Remove condition', 'gutena-forms' ) }
					showTooltip
					onClick={ onRemove }
				/>
			</div>

			{ isMissing && (
				<Notice status="warning" isDismissible={ false } className="gf-condition-row__notice">
					{ __( 'The referenced field no longer exists. This rule will not match at runtime.', 'gutena-forms' ) }
				</Notice>
			) }
		</div>
	);
}

function renderValueInput( { isMulti, isChoice, choiceOptions, value, onChange } ) {
	if ( isMulti && isChoice && choiceOptions.length ) {
		// FormTokenField expects suggestions as object map { value: label }.
		const suggestions = choiceOptions.reduce( ( acc, opt ) => {
			acc[ opt ] = opt;
			return acc;
		}, {} );
		const tokens = Array.isArray( value ) ? value : ( value ? String( value ).split( ',' ).map( ( v ) => v.trim() ).filter( Boolean ) : [] );
		return (
			<FormTokenField
				value={ tokens }
				suggestions={ Object.keys( suggestions ) }
				label={ __( 'Value', 'gutena-forms' ) }
				onChange={ ( newTokens ) => onChange( newTokens.join( ', ' ) ) }
			/>
		);
	}

	if ( isChoice && choiceOptions.length ) {
		const opts = [
			{ value: '', label: __( 'Select value', 'gutena-forms' ) },
			...choiceOptions.map( ( opt ) => ( { value: opt, label: opt } ) ),
		];
		return (
			<SelectControl
				value={ value }
				options={ opts }
				onChange={ onChange }
				__nextHasNoMarginBottom
			/>
		);
	}

	return (
		<TextControl
			value={ value }
			onChange={ onChange }
			placeholder={ __( 'Value', 'gutena-forms' ) }
			__nextHasNoMarginBottom
		/>
	);
}
