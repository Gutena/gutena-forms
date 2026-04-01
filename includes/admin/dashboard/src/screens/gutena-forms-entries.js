import { useState, useEffect } from '@wordpress/element';
import { SelectControl, Button } from '@wordpress/components';
import GutenaFormsDatatable from '../components/gutena-forms-datatable';
import GutenaFormsSingleEntryPage from './gutena-forms-single-entry-page';
import GutenaFormsFormEntries from './gutena-forms-form-entries';
import { gutenaFormsFetchAllEntries, gutenaFromsIdTitle, gutenaFormsFetchEntriesByFormId, gutenaFormsFetchEntriesFiltered, gutenaFormsFetchTags, gutenaFormsFetchStatus, gutenaFormsDeleteEntry, deleteMultipleEntries } from '../api';
import { Link, useParams } from 'react-router';
import { toast } from 'react-toastify';
import Eye from '../icons/eye';
import { Bin } from '../icons/bin';
import { __ } from '@wordpress/i18n';
import {applyFilters, doAction} from "@wordpress/hooks";
import { activateLeftMenu } from '../utils/functions';

const GutenaFormsEntries = ( { showProPopupHandler, setActiveMenu } ) => {

	const { id, slug } = useParams();
	const hasPro = ! ! ( typeof gutenaFormsAdmin !== 'undefined' && gutenaFormsAdmin.hasPro );

	const [ entries, setEntries ] = useState( [] );
	const [ loading, setLoading ] = useState( true );
	const [ formsFilter, setFormsFilter ] = useState( [] );
	const [ selectedFormFilter, setSelectedFormFilter ] = useState( 'all' );
	const [ capabilities, setCapabilities ] = useState( {} );

	const [ tagsOptions, setTagsOptions ] = useState( [ { label: __( 'All Tags', 'gutena-forms' ), value: 'all' } ] );
	const [ statusesOptions, setStatusesOptions ] = useState( [ { label: __( 'All Status', 'gutena-forms' ), value: 'all' } ] );
	const [ selectedTag, setSelectedTag ] = useState( 'all' );
	const [ selectedStatus, setSelectedStatus ] = useState( 'all' );

	const [ statuses, setStatuses ] = useState( [] );

	useEffect( () => {
		activateLeftMenu( 4 );
		setActiveMenu( '/entries' );
	}, [] )

	useEffect( () => {
		gutenaFromsIdTitle()
			.then( formFilterData => {
				const formFilterOptions = [ { label: __( 'All Forms', 'gutena-forms' ), value: 'all' } ];
				Object.keys( formFilterData ).forEach( ( formFilterKey ) => {
					formFilterOptions.push( { label: formFilterData[ formFilterKey ].title, value: formFilterData[ formFilterKey ].id } );
				} );
				setFormsFilter( formFilterOptions );
			} );

		doAction( 'gutenaForms.core.functions.fetchAllStatuses', setStatuses );

		if ( hasPro ) {
			gutenaFormsFetchTags()
				.then( tags => {
					const opts = [ { label: __( 'All Tags', 'gutena-forms' ), value: 'all' } ];
					if ( tags && typeof tags === 'object' ) {
						Object.keys( tags ).forEach( slug => {
							const t = tags[ slug ];
							if ( t && t.title ) {
								opts.push( { label: t.title, value: slug } );
							}
						} );
					}
					setTagsOptions( opts );
				} )
				.catch( () => {} );

			gutenaFormsFetchStatus()
				.then( statuses => {
					const opts = [ { label: __( 'All Status', 'gutena-forms' ), value: 'all' } ];
					if ( statuses && Array.isArray( statuses ) ) {
						statuses.forEach( s => {
							if ( s && s.slug && s.title ) {
								opts.push( { label: s.title, value: s.slug } );
							}
						} );
					} else if ( statuses && typeof statuses === 'object' ) {
						Object.keys( statuses ).forEach( slug => {
							const s = statuses[ slug ];
							if ( s && s.title ) {
								opts.push( { label: s.title, value: slug } );
							}
						} );
					}
					setStatusesOptions( opts );
				} )
				.catch( () => {} );
		}
	}, [ hasPro ] );

	const fetchEntriesForCurrentFilters = () => {
		setLoading( true );

		const useProFilter = hasPro && ( selectedTag !== 'all' || selectedStatus !== 'all' );

		if ( useProFilter ) {
			gutenaFormsFetchEntriesFiltered( {
				formId: selectedFormFilter === 'all' ? undefined : selectedFormFilter,
				tag: selectedTag === 'all' ? undefined : selectedTag,
				status: selectedStatus === 'all' ? undefined : selectedStatus,
			} )
				.then( ( { entries: list, capabilities: caps } ) => {
					setLoading( false );
					setEntries( list );
					setCapabilities( caps );
				} )
				.catch( () => setLoading( false ) );
			return;
		}

		if ( selectedFormFilter === 'all' ) {
			gutenaFormsFetchAllEntries()
				.then( ( { entries: list, capabilities: caps } ) => {
					setLoading( false );
					setEntries( list );
					setCapabilities( caps );
				} )
				.catch( () => setLoading( false ) );
		} else {
			gutenaFormsFetchEntriesByFormId( selectedFormFilter )
				.then( list => {
					setLoading( false );
					setEntries( list );
				} )
				.catch( () => setLoading( false ) );
		}
	};

	useEffect( () => {
		fetchEntriesForCurrentFilters();
	}, [ selectedFormFilter, selectedTag, selectedStatus ] );

	const FormFilter = () => (
		<div>
			<SelectControl
				style={ { width: '100px' } }
				options={ formsFilter }
				onChange={ value => setSelectedFormFilter( value ) }
				value={ selectedFormFilter }
			/>
		</div>
	);

	const refreshEntries = () => {
		fetchEntriesForCurrentFilters();
	};

	const handleDeleteEntry = ( row ) => {

		gutenaFormsDeleteEntry( row.entry_id )
			.then( () => {
				toast.success( __( 'Entry deleted successfully', 'gutena-forms' ) );
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
					toast.success( __( 'Selected entries deleted successfully.', 'gutena-forms' ) );
					refreshEntries();
				} )
				.catch( () => {
					toast.error( __( 'Failed to delete entries.', 'gutena-forms' ) );
				} );
		} else {
			doAction( 'gutenaForms.entries.handle.bulk_actions', action, selectedData, { refresh: refreshEntries, toast: toast } );
		}

	};

	const bulkActionOptions = [
		{ label: __( 'Bulk Actions', 'gutena-forms' ), value: 'bulk_actions' },
		...( Array.isArray( capabilities ) && capabilities.includes( 'delete' ) ? [ { label: __( 'Delete', 'gutena-forms' ), value: 'delete' } ] : [] ),
		...applyFilters( 'gutenaForms.entries.bulk_actions.star', [] ),
		...applyFilters( 'gutenaForms.entries.bulk_actions.status', [] ),
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
						<div>
							<h2 className={ 'gutena-forms__page-title' }>Form Entries</h2>

							<GutenaFormsFormEntries showProPopupHandler={ showProPopupHandler } />
						</div>
					) }

					{ ! id && (
						<div>
							<h2 className={ 'gutena-forms__page-title' }>All Forms Entries</h2>

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
										'key': 'status',
										'value': 'Status',
										'width': '100px',
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

										status: ( { row, header, index } ) => {
											return applyFilters( 'gutenaForms.entries.status', null, { row, header, index }, statuses, showProPopupHandler );
										},

										actions: ( { row, header, index } ) => {

											return (
												<div className={ 'gutena-forms-datatable__action' }>
													<>
														{ applyFilters( 'gutenaForms.entries.actions', null, { row, header, index, form_id: row.form_id } ) }
													</>

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
								customFilters={ applyFilters( 'gutenaForms.entries.components', {
									showProPopupHandler: showProPopupHandler,
									statusesOptions: statusesOptions,
									setSelectedStatus: setSelectedStatus,
									selectedStatus: selectedStatus,
									tagsOptions: tagsOptions,
									selectedTag: selectedTag,
									setSelectedTag: setSelectedTag,
									hasPro: hasPro,
									components: [
										FormFilter,
									]
								} ) }
							/>
						</div>
					) }
				</div>
			) }
		</div>
	);
};

export default GutenaFormsEntries;
