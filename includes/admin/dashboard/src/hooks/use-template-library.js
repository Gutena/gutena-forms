/**
 * Data and state hook for the Template Library screen.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

import { useCallback, useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { toast } from 'react-toastify';
import {
	gutenaFormsFetchTemplate,
	gutenaFormsFetchTemplateCategories,
	gutenaFormsFetchTemplates,
} from '../api';
import {
	ALL_TEMPLATES_CATEGORY,
	COMING_SOON_CATEGORIES,
	TEMPLATES_PER_PAGE,
} from '../utils/template-library-constants';
import { buildCategoryNavItems } from '../utils/template-library-utils';

/**
 * @param {Object}   options Hook options.
 * @param {Function} [options.onUpgrade] Handler for Upgrade to Pro action.
 * @param {Function} [options.onStartUseTemplate] Navigate to Form Ready for a template.
 * @returns {Object} Template library state and handlers.
 */
export function useTemplateLibrary( { onUpgrade, onStartUseTemplate } = {} ) {
	const [ category, setCategory ] = useState( ALL_TEMPLATES_CATEGORY );
	const [ search, setSearch ] = useState( '' );
	const [ debouncedSearch, setDebouncedSearch ] = useState( '' );
	const [ page, setPage ] = useState( 1 );
	const [ categoriesFromApi, setCategoriesFromApi ] = useState( [] );
	const [ templates, setTemplates ] = useState( [] );
	const [ categoryCounts, setCategoryCounts ] = useState( {} );
	const [ pagination, setPagination ] = useState( {
		page: 1,
		per_page: TEMPLATES_PER_PAGE,
		total: 0,
		total_pages: 0,
	} );
	const [ loading, setLoading ] = useState( true );
	const [ categoriesLoading, setCategoriesLoading ] = useState( true );
	const [ previewTemplate, setPreviewTemplate ] = useState( null );
	const [ previewLoading, setPreviewLoading ] = useState( false );
	const [ previewError, setPreviewError ] = useState( false );
	const [ isPreviewOpen, setIsPreviewOpen ] = useState( false );
	const templatesRequestRef = useRef( 0 );
	const previewRequestRef = useRef( 0 );

	useEffect( () => {
		const timer = setTimeout( () => {
			setDebouncedSearch( search );
		}, 300 );

		return () => clearTimeout( timer );
	}, [ search ] );

	useEffect( () => {
		setCategoriesLoading( true );

		gutenaFormsFetchTemplateCategories()
			.then( ( categories ) => {
				setCategoriesFromApi( categories );
				setCategoriesLoading( false );
			} )
			.catch( () => {
				setCategoriesLoading( false );
				toast.error( __( 'Failed to load template categories.', 'gutena-forms' ) );
			} );
	}, [] );

	useEffect( () => {
		const requestId = ++templatesRequestRef.current;

		setLoading( true );

		gutenaFormsFetchTemplates( {
			category,
			search: debouncedSearch,
			page,
			per_page: TEMPLATES_PER_PAGE,
		} )
			.then( ( response ) => {
				if ( requestId !== templatesRequestRef.current ) {
					return;
				}

				setTemplates( response.templates );
				setCategoryCounts( response.category_counts );
				setPagination( response.pagination );
				setLoading( false );
			} )
			.catch( () => {
				if ( requestId !== templatesRequestRef.current ) {
					return;
				}

				setLoading( false );
				toast.error( __( 'Failed to load templates.', 'gutena-forms' ) );
			} );
	}, [ category, debouncedSearch, page ] );

	const categoryItems = buildCategoryNavItems( categoriesFromApi, categoryCounts );

	const handleCategoryChange = useCallback( ( nextCategory ) => {
		if ( COMING_SOON_CATEGORIES.includes( nextCategory ) ) {
			return;
		}

		setCategory( nextCategory );
		setPage( 1 );
	}, [] );

	const handleSearchChange = useCallback( ( value ) => {
		setSearch( value );
		setPage( 1 );
	}, [] );

	const handleClearFilters = useCallback( () => {
		setSearch( '' );
		setDebouncedSearch( '' );
		setCategory( ALL_TEMPLATES_CATEGORY );
		setPage( 1 );
	}, [] );

	const handlePageChange = useCallback( ( nextPage ) => {
		setPage( nextPage );
	}, [] );

	const openPreview = useCallback( ( template ) => {
		const requestId = ++previewRequestRef.current;

		setIsPreviewOpen( true );
		setPreviewTemplate( template );
		setPreviewLoading( true );
		setPreviewError( false );

		gutenaFormsFetchTemplate( template.id )
			.then( ( detail ) => {
				if ( requestId !== previewRequestRef.current ) {
					return;
				}

				setPreviewTemplate( detail );
				setPreviewLoading( false );
			} )
			.catch( () => {
				if ( requestId !== previewRequestRef.current ) {
					return;
				}

				setPreviewLoading( false );
				setPreviewError( true );
				setPreviewTemplate( null );
			} );
	}, [] );

	const closePreview = useCallback( () => {
		setIsPreviewOpen( false );
		setPreviewTemplate( null );
		setPreviewLoading( false );
		setPreviewError( false );
	}, [] );

	const handleUseTemplate = useCallback( () => {
		if ( ! previewTemplate?.id || ! previewTemplate.can_use ) {
			return;
		}

		if ( onStartUseTemplate ) {
			onStartUseTemplate( previewTemplate );
			closePreview();
		}
	}, [ previewTemplate, closePreview, onStartUseTemplate ] );

	const handleUpgrade = useCallback( () => {
		if ( onUpgrade ) {
			onUpgrade();
			return;
		}

		window.open(
			'https://gutenaforms.com/pricing/?utm_source=plugin&utm_medium=template_library&utm_campaign=upgrade_to_pro',
			'_blank',
			'noopener,noreferrer'
		);
	}, [ onUpgrade ] );

	return {
		category,
		search,
		page,
		templates,
		categoryCounts,
		categoryItems,
		pagination,
		loading,
		categoriesLoading,
		previewTemplate,
		previewLoading,
		previewError,
		isPreviewOpen,
		handleCategoryChange,
		handleSearchChange,
		handleClearFilters,
		handlePageChange,
		openPreview,
		closePreview,
		handleUseTemplate,
		handleUpgrade,
	};
}
