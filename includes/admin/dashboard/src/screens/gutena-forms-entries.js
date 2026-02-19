import { useState, useEffect } from '@wordpress/element';
import { SelectControl, Button } from '@wordpress/components';
import GutenaFormsDatatable from '../components/gutena-forms-datatable';
import GutenaFormsSingleEntryPage from './gutena-forms-single-entry-page';
import GutenaFormsFormEntries from './gutena-forms-form-entries';
import { gutenaFormsFetchAllEntries, gutenaFromsIdTitle, gutenaFormsFetchEntriesByFormId, gutenaFormsDeleteEntry, deleteMultipleEntries } from '../api';
import { Link, useParams } from 'react-router';
import { toast } from 'react-toastify';
import Eye from '../icons/eye';
import { Bin } from '../icons/bin';
import { __ } from '@wordpress/i18n';

const GutenaFormsEntries = ( { showProPopupHandler } ) => {

	const { id, slug } = useParams();
	const [ entries, setEntries ] = useState( [] );
	const [ loading, setLoading ] = useState( true );
	const [ formsFilter, setFormsFilter ] = useState( [] );
	const [ selectedFormFilter, setSelectedFormFilter ] = useState( 'all' );
	const [ capabilities, setCapabilities ] = useState( {} );


	useEffect( () => {
		setLoading( true );

		gutenaFormsFetchAllEntries()
			.then( ({ entries, capabilities } ) => {
				setLoading( false );
				setEntries( entries );
				setCapabilities( capabilities );
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
						setEntries( entries.entries );
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

	const refreshEntries = () => {
		setLoading( true );
		if ( 'all' === selectedFormFilter ) {
			gutenaFormsFetchAllEntries()
				.then( entries => {
					setLoading( false );
					setEntries( entries.entries );
				} )
				.catch( () => setLoading( false ) );
		} else {
			gutenaFormsFetchEntriesByFormId( selectedFormFilter )
				.then( entries => {
					setLoading( false );
					setEntries( entries );
				} )
				.catch( () => setLoading( false ) );
		}
	};

	const handleDeleteEntry = ( row ) => {
		if ( ! window.confirm( __( 'Move this entry to trash?', 'gutena-forms' ) ) ) {
			return;
		}
		gutenaFormsDeleteEntry( row.entry_id )
			.then( () => {
				toast.success( __( 'Entry moved to trash successfully.', 'gutena-forms' ) );
				refreshEntries();
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
					refreshEntries();
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
			{ ! loading && (
				<div>
					{ id && 'entry' === slug && (
						<div>
							<GutenaFormsSingleEntryPage
								showProPopupHandler={ showProPopupHandler }
								entryId={ id }
							/>
						</div>
					) }

					{ id && 'entries' === slug && (
						<GutenaFormsFormEntries showProPopupHandler={ showProPopupHandler } />
					) }

					{ ! id && (
						<div>
							<GutenaFormsDatatable
								bulkActionOptions={ bulkActionOptions }
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
															<span style={ { color: '#414A51', fontWeight: '700' } }>{ row.value[ Object.keys( row.value )[0] ].label }:</span> { row.value[ Object.keys( row.value )[0] ].value }
														</div>
													) }
												</div>
											);
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
										},
									}
								} }
								handleBulkAction={ handleBulkAction }
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
