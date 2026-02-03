import { useState, useEffect } from '@wordpress/element';
import {Link, useParams} from 'react-router';
import { gutenaFormsFetchEntriesByFormId } from '../api/entries';
import GutenaFormsDatatable from '../components/gutena-forms-datatable';
import { gutenaFormsInArray } from '../utils/functions';
import Eye from "../icons/eye";
import {Button} from "@wordpress/components";
import {Bin} from "../icons/bin";

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

	}, [ tableHeaders ] );

	return (
		<div>
			{ ! loading && tableData && tableHeaders && (
				<>
					<GutenaFormsDatatable
						data={ tableData }
						headers={ tableHeaders }
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
											<Button>
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
