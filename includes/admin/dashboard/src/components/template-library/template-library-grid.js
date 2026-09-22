import TemplateLibraryCard from './template-library-card';
import TemplateLibraryEmptyState from './template-library-empty-state';
import TemplateLibraryPagination from './template-library-pagination';

const TemplateLibraryGrid = ( {
	templates,
	search,
	pagination,
	loading,
	onPreview,
	onClear,
	onPageChange,
} ) => {
	if ( loading ) {
		return (
			<div className="gutena-forms-template-library__grid gutena-forms-template-library__grid--loading" aria-busy="true">
				{ Array.from( { length: 6 } ).map( ( _, index ) => (
					<div key={ index } className="gutena-forms-template-library__card-skeleton" />
				) ) }
			</div>
		);
	}

	if ( ! templates.length ) {
		return <TemplateLibraryEmptyState search={ search } onClear={ onClear } />;
	}

	return (
		<>
			<div className="gutena-forms-template-library__grid" role="list">
				{ templates.map( ( template ) => (
					<div key={ template.id } role="listitem">
						<TemplateLibraryCard template={ template } onPreview={ onPreview } />
					</div>
				) ) }
			</div>
			<TemplateLibraryPagination pagination={ pagination } onPageChange={ onPageChange } />
		</>
	);
};

export default TemplateLibraryGrid;
