import { __ } from '@wordpress/i18n';

const GutenaFormsEntryDetails = ( { entryId } ) => {

	return (
		<div className={ 'gutena-froms__entry-meta-box' }>
			<h2 className={ 'heading' }>{ __( 'Entry Details', 'gutena-forms' ) }</h2>
		</div>
	);
};

export default GutenaFormsEntryDetails;
