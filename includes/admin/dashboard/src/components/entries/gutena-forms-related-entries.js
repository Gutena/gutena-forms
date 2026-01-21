import { __ } from '@wordpress/i18n';

const GutenaFormsRelatedEntries = ( { entryId } ) => {

	return (
		<div className={ 'gutena-froms__entry-meta-box' }>
			<h2 className={ 'heading' }>{ __( 'Related Entries', 'gutena-forms' ) }</h2>
			<p className="desc">
				The user who created this entry also submitted the entries below.
			</p>

		</div>
	);
};

export default GutenaFormsRelatedEntries;
