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
import { doAction, applyFilters } from '@wordpress/hooks';

const GutenaFormsFormEntries = ( { showProPopupHandler } ) => {

	const { id } = useParams();

	const [ loading, setLoading ] = useState( true );
	const [ tableHeaders, setTableHeaders ] = useState( false );
	const [ tableData, setTableData ] = useState( false );
	const [ capabilities, setCapabilities ] = useState( [] );
	const [ statuses, setStatuses ] = useState( [] );

	useEffect( () => {

		setLoading( true );

		doAction( 'gutenaForms.core.functions.fetchAllStatuses', setStatuses );

		gutenaFormsFetchEntriesByFormId( id, 'headers' )
			.then( ( { headers } )=> {
				setTableHeaders( headers );

				setLoading( false );
			} );

	}, [ id ] );

	useEffect( () => {
		setLoading( true );

		if ( tableHeaders ) {
			gutenaFormsFetchEntriesByFormId( id, 'data' )
				.then( ( { data, capabilities } ) => {

					setCapabilities( capabilities );

					var tableData = data;

					const newTableData = {};

					let i = 0;
					for ( const entry of tableData ) {
						newTableData[ i ] = {
							entry_id: entry.entry_id,
							datetime: entry.added_time,
							status: entry.entry_status,
						};

						let entryData = entry.entry_data;

						for ( const header of tableHeaders ) {
							if ( ! gutenaFormsInArray( header.key, [ 'checkbox', 'entry_id', 'datetime', 'actions', 'status' ] ) ) {
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
			.then( ( { data, capabilities } ) => {

				setCapabilities( capabilities );

				var tableData = data;


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

		gutenaFormsDeleteEntry( row.entry_id )
			.then( () => {
				toast.success( __( 'Entry deleted successfully.', 'gutena-forms' ) );
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

	const bulkActionOptions = [
		{ label: __( 'Bulk Actions', 'gutena-forms' ), value: 'bulk_actions' },
		...( Array.isArray( capabilities ) && capabilities.includes( 'delete' ) ? [ { label: __( 'Delete', 'gutena-forms' ), value: 'delete' } ] : [] ),
	];

	return (
		<div>
			{ ! loading && tableData && tableHeaders && (
				<>
					<GutenaFormsDatatable
						data={ tableData }
						headers={ tableHeaders }
						handleBulkAction={ handleBulkAction }
						bulkActionOptions={ bulkActionOptions }
						tableChildren={ {
							body: {
								status: ( { row, header, index } ) => {
									return applyFilters( 'gutenaForms.entries.status', null, { row, header, index }, statuses, showProPopupHandler );
								},

								actions: ( { row } ) => {

									return (
										<div className={ 'gutena-forms-datatable__action' }>

											{
												capabilities && capabilities.map( cap => {
													if ( 'view' === cap ) {
														return (
															<>
																<Link
																	to={ `/settings/entry/${ row.entry_id }` }
																>
																	<Eye />
																</Link>
															</>
														);
													}

													if ( 'delete' === cap ) {
														return (
															<>
																<Button
																	onClick={ () => handleDeleteEntry( row ) }
																>
																	<Bin />
																</Button>
															</>
														);
													}
												} )
											}

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
