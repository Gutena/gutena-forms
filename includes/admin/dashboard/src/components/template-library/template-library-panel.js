import { __, sprintf } from '@wordpress/i18n';
import { useTemplateLibrary } from '../../hooks/use-template-library';
import TemplateLibraryCategories from './template-library-categories';
import TemplateLibrarySearch from './template-library-search';
import TemplateLibraryGrid from './template-library-grid';
import TemplatePreviewModal from './template-preview-modal';
import { getActiveCategoryLabel } from '../../utils/template-library-utils';

/**
 * Reusable Template Library picker (categories, search, grid, preview modal).
 *
 * @param {Object}   props
 * @param {Function} props.showProPopupHandler Upgrade popup handler.
 * @param {Function} props.onStartUseTemplate    Called when user chooses Use Template.
 * @param {string}   props.previewReturnContext  Preview back-link context.
 * @param {boolean}  [props.showSearch=true]     Whether to render the search field.
 */
const TemplateLibraryPanel = ( {
	showProPopupHandler,
	onStartUseTemplate,
	previewReturnContext,
	showSearch = true,
} ) => {
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
		onStartUseTemplate,
	} );

	const activeCategoryLabel = getActiveCategoryLabel( category, categoryItems );
	const resultCount = pagination?.total ?? 0;

	return (
		<>
			{ showSearch && (
				<div className="gutena-forms-template-library__search-row">
					<TemplateLibrarySearch value={ search } onChange={ handleSearchChange } />
				</div>
			) }

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
		</>
	);
};

export default TemplateLibraryPanel;
