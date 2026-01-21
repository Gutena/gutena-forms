import { useState, useEffect } from '@wordpress/element';
import { SelectControl, Button } from '@wordpress/components';
import GutenaFormsDatatable from '../components/gutena-forms-datatable';
import GutenaFormsSingleEntryPage from './gutena-forms-single-entry-page';
import { gutenaFormsFetchAllEntries } from '../api';
import { Link, useParams } from 'react-router';
import Eye from '../icons/eye';
import { Bin } from '../icons/bin';

const GutenaFormsEntries = () => {

	const { id, slug } = useParams();
	const [ entries, setEntries ] = useState( [] );
	const [ loading, setLoading ] = useState( true );

	useEffect( () => {
		setLoading( true );
		gutenaFormsFetchAllEntries()
			.then( entries => {
				setLoading( false );
				setEntries( entries );
			} )
	}, [] );

	const FormFilter = () => {

		const onChangeFormName = ( value ) => {
		}

		return (
			<div>
				<SelectControl
					options={ [
						{ label: 'All Forms', value: 'all' },
						{ label: 'Contact US', value: '1' },
						{ label: 'Planning', value: '2' },
						{ label: 'Feedback', value: '101' },
						{ label: 'Rotating', value: '102' },
						{ label: 'Feedback 2', value: '15' },
						{ label: 'Conversation', value: '16' },
						{ label: 'Conversation 2', value: '17' },
						{ label: 'Conversation 3', value: '106' },
						{ label: 'Contact US 2', value: '105' },
					] }
					onChange={ onChangeFormName }
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
