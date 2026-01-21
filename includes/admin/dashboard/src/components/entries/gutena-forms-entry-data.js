import { __ } from '@wordpress/i18n';

const GutenaFormsEntryData = ( { entryId } ) => {

	return (
		<div className={ 'gutena-froms__entry-meta-box' }>
			<h2 className={ 'heading' }>{ __( 'Entry Data', 'gutena-froms' ) }</h2>
		</div>
	);
};

export default GutenaFormsEntryData;
