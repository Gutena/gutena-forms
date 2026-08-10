/**
 * Conditional logic operators keyed by source field type.
 *
 * Each entry is { label, needsValue, multi }:
 *  - label:       i18n label shown in the operator dropdown.
 *  - needsValue:   false for operators that take no value (is empty / is checked ...).
 *  - multi:        true for operators that accept multiple values (is any of ...).
 *
 * The operator keys are stable strings used both in the editor attribute and
 * by the frontend runtime evaluator (view.js). Keep them in sync with
 * `OPERATOR_TESTS` in src/blocks/form/view.js.
 */

export const OPERATORS = {
	// Text-like operators
	is: { label: 'is', needsValue: true, multi: false },
	is_not: { label: 'is not', needsValue: true, multi: false },
	contains: { label: 'contains', needsValue: true, multi: false },
	does_not_contain: { label: 'does not contain', needsValue: true, multi: false },
	starts_with: { label: 'starts with', needsValue: true, multi: false },
	does_not_start_with: { label: 'does not start with', needsValue: true, multi: false },
	ends_with: { label: 'ends with', needsValue: true, multi: false },
	does_not_end_with: { label: 'does not end with', needsValue: true, multi: false },
	is_empty: { label: 'is empty', needsValue: false, multi: false },
	is_not_empty: { label: 'is not empty', needsValue: false, multi: false },

	// Comparison operators (number / range / rating / date / time)
	is_equal_to: { label: 'is equal to', needsValue: true, multi: false },
	is_not_equal_to: { label: 'is not equal to', needsValue: true, multi: false },
	greater_than: { label: 'greater than', needsValue: true, multi: false },
	less_than: { label: 'less than', needsValue: true, multi: false },
	greater_than_or_equal_to: { label: 'greater than or equal to', needsValue: true, multi: false },
	less_than_or_equal_to: { label: 'less than or equal to', needsValue: true, multi: false },
	is_before: { label: 'is before', needsValue: true, multi: false },
	is_after: { label: 'is after', needsValue: true, multi: false },

	// Choice multi-value operators
	is_any_of: { label: 'is any of', needsValue: true, multi: true },
	is_not_any_of: { label: 'is not any of', needsValue: true, multi: true },
	is_every_of: { label: 'is every of', needsValue: true, multi: true },

	// Opt-in
	is_checked: { label: 'is checked', needsValue: false, multi: false },
	is_not_checked: { label: 'is not checked', needsValue: false, multi: false },
};

/**
 * Operators available for each source field type.
 * Keys mirror the `fieldType` attribute stored on each field block.
 */
export const OPERATORS_BY_FIELD_TYPE = {
	text: [ 'is', 'is_not', 'contains', 'does_not_contain', 'starts_with', 'does_not_start_with', 'ends_with', 'does_not_end_with', 'is_empty', 'is_not_empty' ],
	textarea: [ 'is', 'is_not', 'contains', 'does_not_contain', 'starts_with', 'does_not_start_with', 'ends_with', 'does_not_end_with', 'is_empty', 'is_not_empty' ],
	email: [ 'is', 'is_not', 'contains', 'does_not_contain', 'starts_with', 'does_not_start_with', 'ends_with', 'does_not_end_with', 'is_empty', 'is_not_empty' ],
	url: [ 'is', 'is_not', 'contains', 'does_not_contain', 'starts_with', 'does_not_start_with', 'ends_with', 'does_not_end_with', 'is_empty', 'is_not_empty' ],
	phone: [ 'is', 'is_not', 'contains', 'does_not_contain', 'starts_with', 'does_not_start_with', 'ends_with', 'does_not_end_with', 'is_empty', 'is_not_empty' ],
	password: [ 'is', 'is_not', 'is_empty', 'is_not_empty' ],

	date: [ 'is', 'is_not', 'is_before', 'is_after', 'is_empty', 'is_not_empty' ],
	time: [ 'is', 'is_not', 'is_before', 'is_after', 'is_empty', 'is_not_empty' ],

	number: [ 'is_equal_to', 'is_not_equal_to', 'greater_than', 'less_than', 'greater_than_or_equal_to', 'less_than_or_equal_to', 'is_empty', 'is_not_empty' ],
	range: [ 'is_equal_to', 'is_not_equal_to', 'greater_than', 'less_than', 'greater_than_or_equal_to', 'less_than_or_equal_to', 'is_empty', 'is_not_empty' ],
	rating: [ 'is_equal_to', 'is_not_equal_to', 'greater_than', 'less_than', 'greater_than_or_equal_to', 'less_than_or_equal_to', 'is_empty', 'is_not_empty' ],

	select: [ 'contains', 'does_not_contain', 'is_empty', 'is_not_empty', 'is_any_of', 'is_not_any_of', 'is_every_of' ],
	checkbox: [ 'contains', 'does_not_contain', 'is_empty', 'is_not_empty', 'is_any_of', 'is_not_any_of', 'is_every_of' ],
	radio: [ 'contains', 'does_not_contain', 'is_empty', 'is_not_empty', 'is_any_of', 'is_not_any_of', 'is_every_of' ],

	file: [ 'is_empty', 'is_not_empty' ],

	optin: [ 'is_checked', 'is_not_checked' ],

	country: [ 'is', 'is_not', 'is_empty', 'is_not_empty', 'is_any_of', 'is_not_any_of' ],
	state: [ 'is', 'is_not', 'is_empty', 'is_not_empty', 'is_any_of', 'is_not_any_of' ],
};

/**
 * Field types whose value input should be a choice list sourced from the
 * referenced field's options rather than a free-text input.
 */
export const CHOICE_FIELD_TYPES = [ 'select', 'checkbox', 'radio', 'country', 'state' ];

/**
 * Field types that are eligible to carry conditional logic.
 * Hidden field, confirmation message, error message and the root form block
 * are intentionally excluded (user story section A).
 */
export const ELIGIBLE_FIELD_TYPES = [
	'text', 'textarea', 'email', 'url', 'phone', 'password',
	'date', 'time', 'number', 'range', 'rating',
	'select', 'checkbox', 'radio', 'file', 'optin', 'country', 'state',
];

/**
 * Get the operator definitions for a given source field type.
 *
 * @param {string} fieldType Source field type.
 * @return {Array} Array of { value, label } for SelectControl.
 */
export function getOperatorOptions( fieldType ) {
	const keys = OPERATORS_BY_FIELD_TYPE[ fieldType ] || OPERATORS_BY_FIELD_TYPE.text;
	return keys.map( ( key ) => ( {
		value: key,
		label: OPERATORS[ key ] ? OPERATORS[ key ].label : key,
	} ) );
}

/**
 * Whether an operator needs a value input.
 *
 * @param {string} operator Operator key.
 * @return {boolean}
 */
export function operatorNeedsValue( operator ) {
	return ! ! ( OPERATORS[ operator ] && OPERATORS[ operator ].needsValue );
}

/**
 * Whether an operator accepts multiple values.
 *
 * @param {string} operator Operator key.
 * @return {boolean}
 */
export function operatorIsMulti( operator ) {
	return ! ! ( OPERATORS[ operator ] && OPERATORS[ operator ].multi );
}
