import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { gutenaFormsFetchEntryDetails } from '../../api/entries';

const GutenaFormsEntryDetails = ( { entryId } ) => {

	const [ entryDetails, setEntryDetails ] = useState( null );
	const [ loading, setLoading ] = useState( true );

	useEffect( () => {
		setLoading( true );
		gutenaFormsFetchEntryDetails( entryId )
			.then( entryDetails => {
				setEntryDetails( entryDetails );
				setLoading( false );
			} );
	}, [ entryId ] );

	console.log( entryDetails );
	return (
		<div className={ 'gutena-froms__entry-meta-box' }>
			<h2 className={ 'heading' }>{ __( 'Entry Details', 'gutena-forms' ) }</h2>

			{ ! loading && entryDetails && (
				<div className={ 'gutena-forms__entry-data' }>
					<div className={ 'gutena-forms__entry-data-row' }>
						<div className={ 'label' }>{ __( 'Entry ID', 'gutena-forms' ) }</div>
						<div className={ 'value' }>{ entryDetails.entry_id }</div>
					</div>

					<div className={ 'gutena-forms__entry-data-row' }>
						<div className={ 'label' }>{ __( 'Form Name', 'gutena-forms' ) }</div>
						<div className={ 'value' }>{ entryDetails.form_name }</div>
					</div>

					<div className={ 'gutena-forms__entry-data-row' }>
						<div className={ 'label' }>{ __( 'Submitted On', 'gutena-forms' ) }</div>
						<div className={ 'value' }>{ entryDetails.added_time }</div>
					</div>

					<div className={ 'gutena-forms__entry-data-row' }>
						<div className={ 'label' }>{ __( 'Status', 'gutena-forms' ) }</div>
						<div className={ 'value' }>{ entryDetails.entry_status }</div>
					</div>

					<div className={ 'gutena-forms__entry-data-row' }>
						<div className={ 'label' }>{ __( 'User', 'gutena-forms' ) }</div>
						<div className={ 'value' }>{ entryDetails.user_name }</div>
					</div>
				</div>
			) }
		</div>
	);
};

export default GutenaFormsEntryDetails;
