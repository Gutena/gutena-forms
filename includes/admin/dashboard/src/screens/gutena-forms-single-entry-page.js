import { ArrowLeft } from '../icons/arrow';
import { useState, useEffect } from '@wordpress/element';
import { Link, useNavigate } from 'react-router';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { toast } from 'react-toastify';
import { gutenaFormsFetchPrevNextEntry, gutenaFormsDeleteEntry } from '../api/entries';
import { Bin } from '../icons/bin';

import GutenaFormsEntryData from '../components/entries/gutena-forms-entry-data';
import GutenaFormsEntryDetails from '../components/entries/gutena-forms-entry-details';
import GutenaFormsNotes from '../components/entries/gutena-forms-notes';
import GutenaFormsTags from '../components/entries/gutena-forms-tags';
import GutenaFormsRelatedEntries from '../components/entries/gutena-forms-related-entries';
import GutenaFormsStatus from '../components/entries/gutena-forms-status';


const GutenaFormsSingleEntryPage = ( { entryId } ) => {
	const navigate = useNavigate();
	const [ prevEntryId, setPrevEntryId ] = useState( null );
	const [ nextEntryId, setNextEntryId ] = useState( null );
	const [ totalEntries, setTotalEntries ] = useState( 0 );
	const [ loading, setLoading ] = useState( true );
	const [ currentEntry, setCurrentEntry ] = useState( 0 );

	const handleDeleteEntry = () => {
		if ( ! window.confirm( __( 'Move this entry to trash?', 'gutena-forms' ) ) ) {
			return;
		}
		gutenaFormsDeleteEntry( entryId )
			.then( () => {
				toast.success( __( 'Entry moved to trash successfully.', 'gutena-forms' ) );
				navigate( '/settings/entries' );
			} )
			.catch( () => {
				toast.error( __( 'Failed to delete entry.', 'gutena-forms' ) );
			} );
	};

	useEffect( () => {
		setLoading( true );

		gutenaFormsFetchPrevNextEntry( entryId )
			.then( ( response ) => {
				setPrevEntryId( response.prevEntryId );
				setNextEntryId( response.nextEntryId );
				setTotalEntries( response.totalEntries );
				setCurrentEntry( response.serialNo );

				setLoading( false );
			} );
	}, [ entryId ] );

	return (
		<div className={ 'gutena-forms__entry-screen' }>
			{ ! loading && (
				<>
					<div>
						<h2 className={ 'heading' } style={ { marginBottom: '30px' } }>
							{ prevEntryId && (
								<Link
									className={ 'gutena-forms__entry-nav-button' }
									to={ `/settings/entry/${ prevEntryId }` }
								>
									<ArrowLeft />
								</Link>
							) }
							&nbsp;
							Entry { currentEntry } / { totalEntries }
							&nbsp;
							{ nextEntryId && (
								<Link
									className={ 'gutena-forms__entry-nav-button' }
									to={ `/settings/entry/${ nextEntryId }` }
								>
									<span style={ { display: 'inline-block', transform: 'scaleX( -1 )' } }>
										<ArrowLeft />
									</span>
								</Link>
							) }
							&nbsp;
							<Button
								className={ 'gutena-forms__entry-delete-button' }
								onClick={ handleDeleteEntry }
								isDestructive
								variant={ 'secondary' }
							>
								<Bin />
								{ __( 'Trash', 'gutena-forms' ) }
							</Button>
						</h2>
					</div>

					<div className={ 'gutena-forms__entry-screen-container' }>
						<div className={ 'gutena-forms__col-70' }>
							<GutenaFormsEntryData entryId={ entryId } />
							<GutenaFormsEntryDetails entryId={ entryId } />
						</div>
						<div className={ 'gutena-forms__col-30' }>
							<GutenaFormsNotes entryId={ entryId } />
							<GutenaFormsStatus entryId={ entryId } />
							<GutenaFormsTags entryId={ entryId } />
							<GutenaFormsRelatedEntries entryId={ entryId } />
						</div>
					</div>
				</>
			) }
		</div>
	);
};

export default GutenaFormsSingleEntryPage;
