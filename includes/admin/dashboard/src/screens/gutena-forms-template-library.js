import { useCallback, useEffect } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { Link, useNavigate } from 'react-router';
import { ArrowLeft } from '../icons/arrow';
import { activateLeftMenu } from '../utils/functions';
import { useTemplateLibrary } from '../hooks/use-template-library';
import TemplateLibraryCategories from '../components/template-library/template-library-categories';
import TemplateLibrarySearch from '../components/template-library/template-library-search';
import TemplateLibraryGrid from '../components/template-library/template-library-grid';
import TemplatePreviewModal from '../components/template-library/template-preview-modal';
import { getActiveCategoryLabel } from '../utils/template-library-utils';

const GutenaFormsTemplateLibrary = ( {
	setActiveMenu,
	showProPopupHandler,
	previewReturnContext,
} ) => {
	const navigate = useNavigate();

	const handleStartUseTemplate = useCallback( ( template ) => {
		navigate( `/settings/templates/form-ready/${ template.id }` );
	}, [ navigate ] );

	const {
		category,
		search,
		templates,
		categoryCounts,
		categoryItems,
		pagination,
		loading,
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
	} = useTemplateLibrary( {
		onUpgrade: showProPopupHandler,
		onStartUseTemplate: handleStartUseTemplate,
	} );

	useEffect( () => {
		setActiveMenu( '/templates' );
		activateLeftMenu( 2 );
	}, [ setActiveMenu ] );

	const activeCategoryLabel = getActiveCategoryLabel( category, categoryItems );
	const resultCount = pagination?.total ?? 0;

	return (
		<div className="gutena-forms-template-library">
			<div className="gutena-forms-template-library__top">
				<div className="gutena-forms-template-library__heading-group">
					<Link
						to="/settings/forms"
						className="gutena-forms-template-library__back-link"
						onClick={ () => setActiveMenu( '/forms' ) }
					>
						<ArrowLeft color="#2C3338" />
						<span className="screen-reader-text">{ __( 'Back to forms', 'gutena-forms' ) }</span>
					</Link>
					<div>
						<p className="gutena-forms-template-library__brand">{ __( 'Gutena Forms', 'gutena-forms' ) }</p>
						<h1 className="gutena-forms-template-library__title">{ __( 'Template Library', 'gutena-forms' ) }</h1>
						<p className="gutena-forms-template-library__helper">
							{ __( 'To get started, choose from our collection of pre-built form templates.', 'gutena-forms' ) }
						</p>
					</div>
				</div>
				<TemplateLibrarySearch value={ search } onChange={ handleSearchChange } />
			</div>

			<div className="gutena-forms-template-library__layout">
				<TemplateLibraryCategories
					activeCategory={ category }
					categoryItems={ categoryItems }
					categoryCounts={ categoryCounts }
					onCategoryChange={ handleCategoryChange }
				/>

				<div className="gutena-forms-template-library__main">
					<p className="gutena-forms-template-library__results-summary">
						{ sprintf(
							/* translators: 1: number of templates, 2: category name */
							__( '%1$s templates in %2$s', 'gutena-forms' ),
							resultCount,
							activeCategoryLabel
						) }
					</p>

					<TemplateLibraryGrid
						templates={ templates }
						search={ search }
						pagination={ pagination }
						loading={ loading }
						onPreview={ openPreview }
						onClear={ handleClearFilters }
						onPageChange={ handlePageChange }
					/>
				</div>
			</div>

			<TemplatePreviewModal
				isOpen={ isPreviewOpen }
				template={ previewTemplate }
				loading={ previewLoading }
				error={ previewError }
				onClose={ closePreview }
				onUseTemplate={ handleUseTemplate }
				onUpgrade={ handleUpgrade }
				returnContext={ previewReturnContext }
			/>
		</div>
	);
};

export default GutenaFormsTemplateLibrary;
