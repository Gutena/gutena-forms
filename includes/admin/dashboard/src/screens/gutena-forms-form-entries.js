import { useState, useEffect } from '@wordpress/element';
import { useParams } from 'react-router';
import { gutenaFormsFetchEntriesByFormId } from '../api/entries';
import GutenaFormsDatatable from '../components/gutena-forms-datatable';
import { gutenaFormsInArray } from '../utils/functions';

const GutenaFormsFormEntries = ( {  } ) => {

	const { id } = useParams();

	const [ loading, setLoading ] = useState( true );
	const [ tableHeaders, setTableHeaders ] = useState( false );
	const [ tableData, setTableData ] = useState( false );

	useEffect( () => {

		setLoading( true );

		gutenaFormsFetchEntriesByFormId( id, 'headers' )
			.then( tableHeaders => {
				setLoading( false );

				setTableHeaders( tableHeaders );
			} );



	}, [ id ] );

	useEffect( () => {
		setLoading( true );
		gutenaFormsFetchEntriesByFormId( id, 'data' )
			.then( tableData => {
				const newTableData = [];
				Object.keys( tableData ).map( ( key ) => {
					const current = tableData[ key ];

					let currentTableData = {
						entry_id: current.entry_id,
						datetime: current.added_time
					};

					Object.keys( tableHeaders ).map( ( key ) => {

						const currentHeader = tableHeaders[ key ];
						if ( ! gutenaFormsInArray( currentHeader.key, [ 'checkbox', 'entry_id', 'datetime', 'actions' ] ) ) {
							currentTableData[ currentHeader.key ] = current.entry_data[ currentHeader.key ].value;
						}
					} );

					newTableData.push( currentTableData );
					console.log( newTableData )
				} );
				setTableData( newTableData );

				setLoading( false )
			} );
	}, [ tableHeaders ] );

	return (
		<div>
			{ ! loading && tableData && tableHeaders && (
				<>
					<GutenaFormsDatatable
						data={ tableData }
						headers={ tableHeaders }
						tableChildren={ {} }
					/>
				</>
			) }
		</div>
	);
};

export default GutenaFormsFormEntries;
