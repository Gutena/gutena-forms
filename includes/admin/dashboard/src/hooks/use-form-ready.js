/**
 * State and handlers for the Form Ready screen.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

import { useCallback, useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { toast } from 'react-toastify';
import {
	gutenaFormsCreateFromTemplate,
	gutenaFormsFetchTemplate,
} from '../api';
import { getDefaultFormName } from '../utils/template-library-utils';

/**
 * @param {string} templateId Template slug from route.
 * @param {Object} options Hook options.
 * @param {Function} [options.onUpgrade] Upgrade handler for Pro templates.
 * @returns {Object}
 */
export function useFormReady( templateId, { onUpgrade } = {} ) {
	const [ template, setTemplate ] = useState( null );
	const [ formName, setFormName ] = useState( '' );
	const [ loading, setLoading ] = useState( true );
	const [ notFound, setNotFound ] = useState( false );
	const [ proRequired, setProRequired ] = useState( false );
	const [ continueLoading, setContinueLoading ] = useState( false );
	const [ isPreviewOpen, setIsPreviewOpen ] = useState( false );

	useEffect( () => {
		if ( ! templateId ) {
			setNotFound( true );
			setLoading( false );
			return;
		}

		setLoading( true );
		setNotFound( false );
		setProRequired( false );

		gutenaFormsFetchTemplate( templateId )
			.then( ( detail ) => {
				setTemplate( detail );
				setFormName( getDefaultFormName( detail.title ) );
				setProRequired( detail.is_pro && ! detail.can_use );
				setLoading( false );
			} )
			.catch( () => {
				setTemplate( null );
				setNotFound( true );
				setLoading( false );
			} );
	}, [ templateId ] );

	const openPreview = useCallback( () => {
		setIsPreviewOpen( true );
	}, [] );

	const closePreview = useCallback( () => {
		setIsPreviewOpen( false );
	}, [] );

	const handleContinue = useCallback( () => {
		if ( ! templateId || ! template?.can_use ) {
			if ( proRequired && onUpgrade ) {
				onUpgrade();
			}
			return;
		}

		const trimmedName = formName.trim();

		if ( ! trimmedName ) {
			toast.error( __( 'Please enter a form name.', 'gutena-forms' ) );
			return;
		}

		setContinueLoading( true );

		gutenaFormsCreateFromTemplate( templateId, trimmedName )
			.then( ( form ) => {
				if ( form?.edit_url ) {
					window.location.href = form.edit_url;
					return;
				}

				setContinueLoading( false );
				toast.error( __( 'Form was created but the editor could not be opened.', 'gutena-forms' ) );
			} )
			.catch( ( error ) => {
				setContinueLoading( false );

				const code = error?.code || error?.data?.code;

				if ( 'gutena_forms_template_pro_required' === code ) {
					setProRequired( true );
					toast.error( __( 'This template requires Gutena Forms Pro.', 'gutena-forms' ) );

					if ( onUpgrade ) {
						onUpgrade();
					}
					return;
				}

				if ( 'gutena_forms_template_not_found' === code ) {
					setNotFound( true );
					setTemplate( null );
					toast.error( __( 'Template not found.', 'gutena-forms' ) );
					return;
				}

				toast.error( __( 'Failed to create form from template.', 'gutena-forms' ) );
			} );
	}, [ templateId, template, formName, proRequired, onUpgrade ] );

	const handleUpgrade = useCallback( () => {
		if ( onUpgrade ) {
			onUpgrade();
			return;
		}

		window.open(
			'https://gutenaforms.com/pricing/?utm_source=plugin&utm_medium=form_ready&utm_campaign=upgrade_to_pro',
			'_blank',
			'noopener,noreferrer'
		);
	}, [ onUpgrade ] );

	return {
		template,
		formName,
		setFormName,
		loading,
		notFound,
		proRequired,
		continueLoading,
		isPreviewOpen,
		openPreview,
		closePreview,
		handleContinue,
		handleUpgrade,
	};
}
