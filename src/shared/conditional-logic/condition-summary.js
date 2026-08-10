/**
 * ConditionSummary — read-only recap of the saved conditional logic rules,
 * shown in the inspector when conditions exist (Figma state 5).
 *
 * Renders compact text such as:
 *   Show when
 *     Field A is "x" AND Field B is not empty
 *     OR
 *     Field C is any of "a, b"
 */

import { __ } from '@wordpress/i18n';
import { useMemo } from '@wordpress/element';
import { OPERATORS } from './operators';

export default function ConditionSummary( { action, groups, siblings } ) {
	const summary = useMemo( () => {
		if ( ! Array.isArray( groups ) || ! groups.length ) {
			return null;
		}
		return groups.map( ( group ) => describeGroup( group, siblings ) ).filter( Boolean );
	}, [ groups, siblings ] );

	if ( ! summary || ! summary.length ) {
		return null;
	}

	return (
		<div className="gf-condition-summary">
			<div className="gf-condition-summary__action">
				{ 'hide' === action
					? __( 'Hide when', 'gutena-forms' )
					: __( 'Show when', 'gutena-forms' ) }
			</div>
			{ summary.map( ( groupText, i ) => (
				<div className="gf-condition-summary__group" key={ i }>
					{ i > 0 && <div className="gf-condition-summary__or">{ __( 'OR', 'gutena-forms' ) }</div> }
					<div className="gf-condition-summary__line">{ groupText }</div>
				</div>
			) ) }
		</div>
	);
}

function describeGroup( group, siblings ) {
	if ( ! Array.isArray( group ) || ! group.length ) {
		return null;
	}
	const parts = group.map( ( c ) => describeCondition( c, siblings ) ).filter( Boolean );
	if ( ! parts.length ) {
		return null;
	}
	return parts.join( ' AND ' );
}

function describeCondition( condition, siblings ) {
	if ( ! condition || ! condition.field ) {
		return '';
	}
	const sibling = siblings.find( ( s ) => s.nameAttr === condition.field );
	const fieldLabel = sibling ? sibling.fieldName : condition.field;
	const opLabel = OPERATORS[ condition.operator ] ? OPERATORS[ condition.operator ].label : condition.operator;
	const valueLabel = condition.value ? ` "${ condition.value }"` : '';
	return `${ fieldLabel } ${ opLabel }${ valueLabel }`;
}
