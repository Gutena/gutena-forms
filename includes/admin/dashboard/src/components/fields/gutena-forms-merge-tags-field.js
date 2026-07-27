import { __ } from '@wordpress/i18n';

const GutenaFormsMergeTagsField = ( { tags = [], onInsert, disabled = false } ) => {
	if ( ! tags.length ) {
		return null;
	}

	return (
		<div className={ `gutena-forms__merge-tags${ disabled ? ' is-disabled' : '' }` }>
			<p className="gutena-forms__merge-tags-label">{ __( 'Merge Tags:', 'gutena-forms' ) }</p>
			<div className="gutena-forms__merge-tags-list">
				{ tags.map( ( tag ) => (
					<button
						key={ tag }
						type="button"
						className="gutena-forms__merge-tag-pill"
						disabled={ disabled }
						onClick={ () => onInsert( tag ) }
					>
						{ tag }
					</button>
				) ) }
			</div>
		</div>
	);
};

export default GutenaFormsMergeTagsField;
