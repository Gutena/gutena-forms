import { useState, useEffect } from '@wordpress/element';
import { Link, useParams } from 'react-router';
import { gutenaFormsFetchEntriesByFormId, gutenaFormsDeleteEntry, deleteMultipleEntries } from '../api/entries';
import GutenaFormsDatatable from '../components/gutena-forms-datatable';
import { gutenaFormsInArray } from '../utils/functions';
import { toast } from 'react-toastify';
import { __ } from '@wordpress/i18n';
import Eye from '../icons/eye';
import { Button } from '@wordpress/components';
import { Bin } from '../icons/bin';

const GutenaFormsFormEntries = ( {  } ) => {

	const { id } = useParams();

	const [ loading, setLoading ] = useState( true );
	const [ tableHeaders, setTableHeaders ] = useState( false );
	const [ tableData, setTableData ] = useState( false );

	useEffect( () => {

		setLoading( true );

		gutenaFormsFetchEntriesByFormId( id, 'headers' )
			.then( tableHeaders => {
				setTableHeaders( tableHeaders );

				setLoading( false );
			} );

	}, [ id ] );

	useEffect( () => {
		setLoading( true );

		if ( tableHeaders ) {
			gutenaFormsFetchEntriesByFormId( id, 'data' )
				.then( tableData => {
					const newTableData = {};

					let i = 0;
					for ( const entry of tableData ) {
						newTableData[ i ] = {
							entry_id: entry.entry_id,
							datetime: entry.added_time,
						};

						let entryData = entry.entry_data;

						for ( const header of tableHeaders ) {
							if ( ! gutenaFormsInArray( header.key, [ 'checkbox', 'entry_id', 'datetime', 'actions' ] ) ) {
								newTableData[ i ][ header.key ] = entryData[ header.key ].value;
							}
						}
						i++;
					}

					setTableData( Object.values( newTableData ) );
					setLoading( false )
				} );
		}

	}, [ tableHeaders, id ] );

	const refreshFormEntries = () => {
		setLoading( true );
		gutenaFormsFetchEntriesByFormId( id, 'data' )
			.then( tableData => {
				const newTableData = {};
				let i = 0;
				for ( const entry of tableData ) {
					newTableData[ i ] = {
						entry_id: entry.entry_id,
						datetime: entry.added_time,
					};
					const entryData = entry.entry_data;
					for ( const header of tableHeaders ) {
						if ( ! gutenaFormsInArray( header.key, [ 'checkbox', 'entry_id', 'datetime', 'actions' ] ) ) {
							newTableData[ i ][ header.key ] = entryData[ header.key ].value;
						}
					}
					i++;
				}
				setTableData( Object.values( newTableData ) );
				setLoading( false );
			} )
			.catch( () => setLoading( false ) );
	};

	const handleDeleteEntry = ( row ) => {
		if ( ! window.confirm( __( 'Move this entry to trash?', 'gutena-forms' ) ) ) {
			return;
		}
		gutenaFormsDeleteEntry( row.entry_id )
			.then( () => {
				toast.success( __( 'Entry moved to trash successfully.', 'gutena-forms' ) );
				refreshFormEntries();
			} )
			.catch( () => {
				toast.error( __( 'Failed to delete entry.', 'gutena-forms' ) );
			} );
	};

	const handleBulkAction = ( action, selectedData ) => {
		if ( 'delete' === action ) {
			deleteMultipleEntries( selectedData )
				.then( () => {
					toast.success( __( 'Selected entries moved to trash successfully.', 'gutena-forms' ) );
					refreshFormEntries();
				} )
				.catch( () => {
					toast.error( __( 'Failed to delete entries.', 'gutena-forms' ) );
				} );
		}
	};

	return (
		<div>
			{ ! loading && tableData && tableHeaders && (
				<>
					<GutenaFormsDatatable
						data={ tableData }
						headers={ tableHeaders }
						handleBulkAction={ handleBulkAction }
						tableChildren={ {
							body: {
								actions: ( { row, header, index } ) => {

									return (
										<div className={ 'gutena-forms-datatable__action' }>
											<Link
												to={ `/settings/entry/${ row.entry_id }` }
											>
												<Eye />
											</Link>
											<Button
												onClick={ () => handleDeleteEntry( row ) }
											>
												<Bin />
											</Button>
										</div>
									);
								}
							}
						} }
					/>
				</>
			) }
		</div>
	);
};

export default GutenaFormsFormEntries;
