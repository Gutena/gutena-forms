import { __ } from '@wordpress/i18n';

const GutenaFormsTags = ( { entryId } ) => {

	return (
		<div className={ 'gutena-froms__entry-meta-box' }>
			<h2 className={ 'heading' }>{ __( 'Tags', 'gutena-forms' ) }</h2>
			<p className="desc">
				Separate with commas or the Enter key.
			</p>

		</div>
	);
};

export default GutenaFormsTags;
