/**
 * Controlled state and local validation for template form previews.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

import { useCallback, useEffect, useMemo, useState } from '@wordpress/element';
import { normalizeTemplateFields } from '../utils/template-field-utils';
import {
	getInitialPreviewValues,
	validatePreviewForm,
} from '../utils/template-preview-validation';

/**
 * @param {Array} fields Template fields from API.
 * @returns {Object}
 */
export function useTemplateFormPreview( fields = [] ) {
	const normalizedFields = useMemo( () => normalizeTemplateFields( fields ), [ fields ] );
	const [ values, setValues ] = useState( () => getInitialPreviewValues( fields ) );
	const [ errors, setErrors ] = useState( {} );
	const [ showSuccess, setShowSuccess ] = useState( false );

	useEffect( () => {
		setValues( getInitialPreviewValues( fields ) );
		setErrors( {} );
		setShowSuccess( false );
	}, [ fields ] );

	const setFieldValue = useCallback( ( fieldId, value ) => {
		setValues( ( previous ) => ( {
			...previous,
			[ fieldId ]: value,
		} ) );

		setErrors( ( previous ) => {
			if ( ! previous[ fieldId ] ) {
				return previous;
			}

			const next = { ...previous };
			delete next[ fieldId ];
			return next;
		} );

		if ( showSuccess ) {
			setShowSuccess( false );
		}
	}, [ showSuccess ] );

	const resetPreviewForm = useCallback( () => {
		setValues( getInitialPreviewValues( fields ) );
		setErrors( {} );
		setShowSuccess( false );
	}, [ fields ] );

	const handleSubmit = useCallback( ( event ) => {
		event.preventDefault();
		event.stopPropagation();

		const validationErrors = validatePreviewForm( normalizedFields, values );

		if ( Object.keys( validationErrors ).length > 0 ) {
			setErrors( validationErrors );
			setShowSuccess( false );
			return false;
		}

		setErrors( {} );
		setShowSuccess( true );
		setValues( getInitialPreviewValues( fields ) );
		return true;
	}, [ fields, normalizedFields, values ] );

	return {
		normalizedFields,
		values,
		errors,
		showSuccess,
		setFieldValue,
		resetPreviewForm,
		handleSubmit,
	};
}
