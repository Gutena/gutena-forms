import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import { useNavigate, useParams } from 'react-router';
import { toast } from 'react-toastify';
import TemplateCategorySidebar from '../components/templates/TemplateCategorySidebar';
import TemplateGrid from '../components/templates/TemplateGrid';
import TemplatePreviewModal from '../components/templates/TemplatePreviewModal';
import { gutenaFormsFetchTemplates } from '../utils/form-templates-api';

const GutenaFormsTemplateLibrary = ( { showProPopupHandler } ) => {
	const navigate = useNavigate();
	const { category: routeCategory } = useParams();
	const [ categories, setCategories ] = useState( [] );
	const [ templates, setTemplates ] = useState( [] );
	const [ total, setTotal ] = useState( 0 );
	const [ loading, setLoading ] = useState( true );
	const [ search, setSearch ] = useState( '' );
	const [ activeCategory, setActiveCategory ] = useState( routeCategory || 'all' );
	const [ selectedTemplate, setSelectedTemplate ] = useState( null );

	useEffect( () => {
		if ( ! routeCategory ) {
			setActiveCategory( 'all' );
			return;
		}

		if ( 'request-template' === routeCategory ) {
			navigate( '/templates', { replace: true } );
			setActiveCategory( 'all' );
			return;
		}

		setActiveCategory( routeCategory );
	}, [ routeCategory, navigate ] );

	useEffect( () => {
		if ( ! routeCategory || ! categories.length ) {
			return;
		}

		const isValidFilter = categories.some(
			( cat ) => cat.slug === routeCategory && cat.type !== 'link'
		);

		if ( ! isValidFilter ) {
			navigate( '/templates', { replace: true } );
			setActiveCategory( 'all' );
		}
	}, [ categories, routeCategory, navigate ] );

	useEffect( () => {
		setLoading( true );
		gutenaFormsFetchTemplates( { category: activeCategory, search } )
			.then( ( response ) => {
				setCategories( response.categories || [] );
				setTemplates( response.templates || [] );
				setTotal( response.total || 0 );
				setLoading( false );
			} )
			.catch( () => {
				setLoading( false );
				toast.error( __( 'Failed to load templates.', 'gutena-forms' ) );
			} );
	}, [ activeCategory, search ] );

	const activeCategoryLabel =
		categories.find(
			( cat ) => cat.slug === activeCategory && cat.type !== 'link'
		)?.title || __( 'All Form Templates', 'gutena-forms' );

	const handleCategorySelect = ( slug ) => {
		setActiveCategory( slug );
		if ( 'all' === slug ) {
			navigate( '/templates' );
		} else {
			navigate( `/templates/${ slug }` );
		}
	};

	return (
		<div className="gutena-forms__template-library">
			<div className="gutena-forms__template-library-header">
				<div>
					<button
						type="button"
						className="gutena-forms__template-back-link"
						onClick={ () => navigate( '/create' ) }
					>
						{ __( '← Back', 'gutena-forms' ) }
					</button>
					<h1>{ __( 'Template Library', 'gutena-forms' ) }</h1>
					<p>
						{ total } { __( 'templates in', 'gutena-forms' ) } { activeCategoryLabel }
					</p>
				</div>
				<div className="gutena-forms__template-search">
					<input
						type="search"
						placeholder={ __( 'Search Form', 'gutena-forms' ) }
						value={ search }
						onChange={ ( e ) => setSearch( e.target.value ) }
					/>
				</div>
			</div>

			<div className="gutena-forms__template-library-body">
				<TemplateCategorySidebar
					categories={ categories }
					activeCategory={ activeCategory }
					onSelect={ handleCategorySelect }
				/>

				<div className="gutena-forms__template-library-content">
					{ loading ? (
						<div className="gutena-forms__template-loading">
							<p>{ __( 'Loading templates...', 'gutena-forms' ) }</p>
						</div>
					) : (
						<TemplateGrid
							templates={ templates }
							onSelect={ setSelectedTemplate }
						/>
					) }
				</div>
			</div>

			<TemplatePreviewModal
				template={ selectedTemplate }
				isOpen={ !! selectedTemplate }
				onClose={ () => setSelectedTemplate( null ) }
				showProPopupHandler={ showProPopupHandler }
			/>
		</div>
	);
};

export default GutenaFormsTemplateLibrary;
