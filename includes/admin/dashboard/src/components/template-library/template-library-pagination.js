import { __, sprintf } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import Previous from '../../icons/previous';
import Next from '../../icons/next';

const TemplateLibraryPagination = ( { pagination, onPageChange } ) => {
	if ( ! pagination || pagination.total_pages <= 1 ) {
		return null;
	}

	const { page, total_pages: totalPages } = pagination;

	return (
		<nav
			className="gutena-forms-template-library__pagination"
			aria-label={ __( 'Template library pagination', 'gutena-forms' ) }
		>
			<Button
				className="gutena-forms__pagination-button prev-button"
				onClick={ () => onPageChange( page - 1 ) }
				disabled={ page <= 1 }
				aria-label={ __( 'Previous page', 'gutena-forms' ) }
			>
				<Previous />
			</Button>
			<Button
				className="gutena-forms__pagination-button current"
				disabled={ true }
				aria-current="page"
				aria-label={ sprintf(
					/* translators: %d: current page number */
					__( 'Page %d', 'gutena-forms' ),
					page
				) }
			>
				{ page }
			</Button>
			<Button
				className="gutena-forms__pagination-button next-button"
				onClick={ () => onPageChange( page + 1 ) }
				disabled={ page >= totalPages }
				aria-label={ __( 'Next page', 'gutena-forms' ) }
			>
				<Next />
			</Button>
		</nav>
	);
};

export default TemplateLibraryPagination;
