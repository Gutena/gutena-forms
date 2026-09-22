import { __, sprintf } from '@wordpress/i18n';
import { Button } from '@wordpress/components';

const TemplateLibraryEmptyState = ( { search, onClear } ) => {
	const hasSearch = Boolean( search?.trim() );

	return (
		<div className="gutena-forms-template-library__empty-state" role="status">
			<h3>{ __( 'No templates found', 'gutena-forms' ) }</h3>
			<p>
				{ hasSearch
					? sprintf(
						/* translators: %s: search query */
						__( 'We couldn\'t find anything matching “%s”. Try a different search term or category.', 'gutena-forms' ),
						search
					)
					: __( 'No templates match the current category. Try a different category or clear your filters.', 'gutena-forms' ) }
			</p>
			<Button
				variant="secondary"
				className="gutena-forms-template-library__clear-button"
				onClick={ onClear }
			>
				{ __( 'Clear', 'gutena-forms' ) }
			</Button>
		</div>
	);
};

export default TemplateLibraryEmptyState;
