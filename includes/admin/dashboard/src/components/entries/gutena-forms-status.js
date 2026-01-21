import { __ } from '@wordpress/i18n';

const GutenaFormsStatus = ( { entryId } ) => {
	return (
		<div className={ 'gutena-froms__entry-meta-box' }>
			<h2 className={ 'heading' }>{ __( 'Status', 'gutena-forms' ) }</h2>

		</div>
	);
};

export default GutenaFormsStatus;
