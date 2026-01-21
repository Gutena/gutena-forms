import { __ } from '@wordpress/i18n';

const GutenaFormsNotes = ( { entryId } ) => {

	return (
		<div className={ 'gutena-froms__entry-meta-box' }>
			<h2 className={ 'heading' }>{ __( 'Notes', 'gutena-forms' ) }</h2>

		</div>
	);
};

export default GutenaFormsNotes;
