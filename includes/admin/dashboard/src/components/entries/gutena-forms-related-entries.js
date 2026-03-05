import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { gutenaFormsFetchRelatedEntries } from '../../api/entries';
import { Link } from 'react-router';

const GutenaFormsRelatedEntries = ( { entryId } ) => {

	const [ relatedEntries, setRelatedEntries ] = useState( false );
	const [ loading, setLoading ] = useState( false );

	useEffect( () => {
		setLoading( true );
		gutenaFormsFetchRelatedEntries( entryId )
			.then( response => {
				setRelatedEntries( response );
				setLoading( false );
			} );
	}, [ entryId ] );

	return (
		<div className={ 'gutena-froms__entry-meta-box' }>
			<h2 className={ 'heading' }>{ __( 'Related Entries', 'gutena-forms' ) }</h2>
			<p className="desc">
				The user who created this entry also submitted the entries below.
			</p>

			{ ! loading && relatedEntries && relatedEntries.length > 0 && (
				<ul className={ 'gutena-forms__list-unstyled' }>
					{ Object.keys( relatedEntries ).map( ( entryKey, key ) => {

						return (
							<li key={ key }>
								<Link
									to={ `/settings/entry/${ relatedEntries[ entryKey ].entry_id }` }
								>
									{ relatedEntries[ entryKey ].added_time }
								</Link>
							</li>
						);
					} ) }
				</ul>
			) }
		</div>
	);
};

export default GutenaFormsRelatedEntries;
