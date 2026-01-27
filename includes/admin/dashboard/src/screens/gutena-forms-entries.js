import { useState, useEffect } from '@wordpress/element';
import { SelectControl, Button } from '@wordpress/components';
import GutenaFormsDatatable from '../components/gutena-forms-datatable';
import GutenaFormsSingleEntryPage from './gutena-forms-single-entry-page';
import GutenaFormsFormEntries from './gutena-forms-form-entries';
import { gutenaFormsFetchAllEntries, gutenaFromsIdTitle, gutenaFormsFetchEntriesByFormId } from '../api';
import { Link, useParams } from 'react-router';
import Eye from '../icons/eye';
import { Bin } from '../icons/bin';

const GutenaFormsEntries = () => {

	const { id, slug } = useParams();
	const [ entries, setEntries ] = useState( [] );
	const [ loading, setLoading ] = useState( true );
	const [ formsFilter, setFormsFilter ] = useState( [] );
	const [ selectedFormFilter, setSelectedFormFilter ] = useState( 'all' );


	useEffect( () => {
		setLoading( true );

		gutenaFormsFetchAllEntries()
			.then( entries => {
				setLoading( false );
				setEntries( entries );
			} );

		gutenaFromsIdTitle()
			.then( formsFilter => {
				let formFilterOptions = [ { label: 'All Forms', value: 'all' } ];
				Object.keys( formsFilter ).map( ( formFilterKey ) => {
					formFilterOptions.push( { label: formsFilter[ formFilterKey ].title, value: formsFilter[ formFilterKey ].id } );
				} );
				setFormsFilter( formFilterOptions );
			} );
	}, [] );

	const FormFilter = () => {

		const onChangeFormName = ( value ) => {
			setSelectedFormFilter( value );
			setLoading( true );

			if ( 'all' === value ) {
				gutenaFormsFetchAllEntries()
					.then( entries => {
						setLoading( false );
						setEntries( entries );
					} );
			} else {
				gutenaFormsFetchEntriesByFormId( value )
					.then( entries => {
						setLoading( false );
						setEntries( entries );
					} );
			}
		}

		return (
			<div>
				<SelectControl
					options={ formsFilter }
					onChange={ onChangeFormName }
					value={ selectedFormFilter }
				/>
			</div>
		);
	};

	return (
		<div>
			{ ! loading && (
				<div>
					{ id && 'entry' === slug && (
						<div>
							<GutenaFormsSingleEntryPage entryId={ id } />
						</div>
					) }

					{ id && 'entries' === slug && (
						<GutenaFormsFormEntries />
					) }

					{ ! id && (
						<div>
							<GutenaFormsDatatable
								headers={ [
									{
										key: 'checkbox',
										value: 'entry_id',
										width: '25px',
									},
									{
										key: 'entry_id',
										value: 'Entry ID',
										width: '100px',
									},
									{
										key: 'form_name',
										value: 'Form Name',
										width: '200px',
									},
									{
										key: 'entry_data',
										value: 'First Value',
									},
									{
										key: 'datetime',
										value: 'Date & Time',
										width: '150px',
									},
									{
										key: 'actions',
										value: 'Action',
										width: '110px',
									}
								] }
								data={ entries }
								tableChildren={ {
									body: {
										entry_id: ( { row } ) => {

											return (
												<div>
													Entry # { row.entry_id }
												</div>
											);
										},

										entry_data: ( { row } ) => {

											return (
												<div>
													{ row.value && Object.keys( row.value )[0] && row.value[ Object.keys( row.value )[0] ] && row.value[ Object.keys( row.value )[0] ].value && (
														<div>
															{ row.value[ Object.keys( row.value )[0] ].value }
														</div>
													) }
												</div>
											);
										},

										actions: ( { row } ) => {

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
										},
									}
								} }
								handleBulkAction={ ( ...v ) => { console.log( ...v )} }
								customFilters={ {
									components: [
										FormFilter,
									]
								} }
							/>
						</div>
					) }
				</div>
			) }
		</div>
	);
};

export default GutenaFormsEntries;
