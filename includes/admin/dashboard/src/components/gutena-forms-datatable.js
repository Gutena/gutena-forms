import GutenaFormsTable from './gutena-forms-table';
import { useState, useEffect } from '@wordpress/element';
import { Button, SelectControl } from '@wordpress/components';
import Calendar from "../icons/calendar";
import Search from "../icons/search";
import Next from "../icons/next";
import Previous from "../icons/previous";
import GutenaFormsCalendar from './gutena-forms-calendar';
import { __ } from '@wordpress/i18n';
import { toast } from 'react-toastify';



const GutenaFormsDatatable = ( { headers, data, handleBulkAction, tableChildren, customFilters } ) => {
	const [ numberOfRows, setNumberOfRows ] = useState( 10 );
	const [ currentPage, setCurrentPage ] = useState( 1 );
	const [ searchTerm, setSearchTerm ] = useState( '' );
	const totalPages = Math.ceil( data.length / numberOfRows );
	const [ tableData, setTableData ] = useState( [] );
	const [ offset, setOffset ] = useState( 0 );
	const [ calendarActive, setCalendarActive ] = useState( false );
	const [ selectedDates, setSelectedDates ] = useState( '' );
	const [ bulkAction, setBulkAction ] = useState( 'bulk_actions' );
	const [ selectedData, setSelectedData ] = useState( [] );

	useEffect( () => {
		setCurrentPage( 1 );
		setOffset( 0 );
	}, [ numberOfRows ] );

	useEffect( () => {
		let filteredData = data;

		if ( 2 === selectedDates.length ) {
			filteredData = filteredData.filter( ( item ) => {
				let current = new Date( item.datetime );
				let startDate = new Date( selectedDates[0] );
				let endDate = new Date( selectedDates[1] );

				return current >= startDate && current <= endDate;
			} );
		}

		// Apply text search filter
		filteredData = filteredData.filter( ( a ) => {
			return Object.values( a ).some( ( value ) =>
				String( value ).toLowerCase().includes( searchTerm.toLowerCase() )
			);
		} );

		if ( -1 === numberOfRows ) {
			setTableData( filteredData );
			return;
		}
		setTableData( filteredData.slice( offset, numberOfRows + offset ) );
	}, [ numberOfRows, searchTerm, offset, selectedDates ] );

	const handlePrevPage = () => {
		setCurrentPage( currentPage - 1 );
		setOffset( offset - numberOfRows );
	}

	const handleNextPage = () => {
		setCurrentPage( currentPage + 1 );
		setOffset( offset + numberOfRows );
	}

	const handleDateSelect = ( { startDate, endDate } ) => {
		setSelectedDates( [ startDate, endDate ] );
	}

	const handleBulkActions = () => {
		if ( 'bulk_actions' === bulkAction ) {
			toast.error( 'Please select a bulk action to perform.' );
			return;
		}

		if ( ! selectedData.length ) {
			toast.error( 'Please select at least one item to perform bulk action.' );
			return;
		}

		handleBulkAction( bulkAction, selectedData );
	};


	const NumberOfPagesComponent = () => {

		return (
			<div className={ 'number-of-items-wrapper' }>
				<div className={ 'display-inline-block' }>
					Page { currentPage } out of { -1 < totalPages ? totalPages : 1 }
				</div>

				<div className={ 'display-inline-block gutena-forms__select-wrapper' }>
					<SelectControl
						onChange={ ( value ) => setNumberOfRows( parseInt( value ) ) }
						options={ [
							{ label: '10', value: 10 },
							{ label: '25', value: 25 },
							{ label: '50', value: 50 },
							{ label: '100', value: 100 },
							{ label: 'All', value: -1 },
						] }
						value={ numberOfRows }
					/>
				</div>
			</div>
		);
	};

	const PaginationComponent = () => {

		if ( -1 === numberOfRows ) {
			return (
				<div className={ 'gutena-forms__pagination' }>
					<Button
						className={ 'gutena-forms__pagination-button prev-button' }
						disabled={ true }
					>
						<Previous />
					</Button>
					<Button
						className={ 'gutena-forms__pagination-button current' }
						disabled={ true }
					>1</Button>
					<Button
						className={ 'gutena-forms__pagination-button next-button' }
						disabled={ true }
					>
						<Next />
					</Button>
				</div>
			);
		}

		return (
			<div className={ 'gutena-forms__pagination' }>
				<Button
					className={ 'gutena-forms__pagination-button prev-button' }
					onClick={ handlePrevPage }
					disabled={ currentPage <= 1 }
				>
					<Previous />
				</Button>

				<Button
					className={ 'gutena-forms__pagination-button current' }
					disabled={ true }
				>{ currentPage }</Button>
				<Button
					className={ 'gutena-forms__pagination-button next-button' }
					onClick={ handleNextPage }
					disabled={ currentPage >= totalPages || tableData.length < numberOfRows }
				>
					<Next />
				</Button>
			</div>
		);
	};

	const valueSetter = ( value, isSelected ) => {
		if ( isSelected ) {
			setSelectedData( ( prevSelected ) => ( [ ...prevSelected, value ] ) );
		} else {
			setSelectedData( ( prevSelected ) => prevSelected.filter( ( item ) => item !== value ) );
		}
	}

	const toggleAllCheckboxes = ( e ) => {
		const checkboxes = document.querySelectorAll( '.gutena_forms_form_select' );
		checkboxes.forEach( ( checkbox ) => {
			checkbox.checked = e.target.checked;

			const value = parseInt( checkbox.value );
			valueSetter( value, e.target.checked );
		} );
	}

	const uncheckGlobalCheckbox = ( e ) => {
		if ( ! e.target.checked ) {
			const globalCheckbox = document.querySelector( '.gutena_forms_form_select_all' );
			globalCheckbox.checked = false;
		}
	};

	return (
		<div className={ 'gutena-forms__data-table-container' }>

			<div className={ 'gutena-forms__datatable-header' }>

				<div className={ 'gutena-forms__bulk-action-container' }>

					<div className={ 'display-inline-block' }>
						<SelectControl
							options={ [
								{ label: 'Bulk Actions', value: 'bulk_actions' },
								{ label: 'Delete', value: 'delete' },
							] }
							value={ bulkAction }
							onChange={ ( value ) => setBulkAction( value ) }
						/>
					</div>

					<div className={ 'display-inline-block' }>
						<Button
							className={ 'secondary-button' }
							onClick={ handleBulkActions }
						>{ __( 'Apply', 'gutena-forms' ) }</Button>
					</div>

				</div>

				<div>

					{ customFilters && customFilters.components && customFilters.components.map( ( Component, key ) => {
						return (
							<div
								className={ 'display-inline-block' }
								key={ key }
								style={ { marginRight: '10px' } }
							>
								<Component />
							</div>
						);
					} ) }

					<div className={ 'display-inline-block' }>
						<div className={ 'gutena-forms__search-box date-search' }>
							<span>
								<input
									placeholder={ 'mm/dd/yyyy - mm/dd/yyyy' }
									type={ 'text' }
									readOnly
									value={ selectedDates && `${ selectedDates[0] } - ${ selectedDates[1] }` }
									onClick={ () => setCalendarActive( ! calendarActive ) }
								/>
							</span>
							<Calendar />

							{
								calendarActive && (
									<GutenaFormsCalendar
										onSelect={ handleDateSelect }
										onExit={ () => setCalendarActive( false ) }
									/>
								)
							}
						</div>
					</div>
					<div className={ 'display-inline-block' }>
						<div className={ 'gutena-forms__search-box text-search' }>
							<Search />
							<input
								placeholder={ 'Search' }
								type={ 'text' }
								value={ searchTerm }
								onChange={ ( e ) => setSearchTerm( e.target.value ) }
							/>
						</div>
					</div>
					<div className={ 'display-inline-block' }>
						<Button
							className={ 'gutena-forms__clear-filters-button secondary-button' }
							onClick={ () => {
								setSearchTerm( '' );
								setSelectedDates( '' );
							} }
						>{ __( 'Clear Filters', 'gutena-forms' ) }</Button>
					</div>

				</div>
			</div>

			<GutenaFormsTable
				headers={ headers }
				data={ tableData }
			>
				{ {
					header: {
						checkbox: () => {

							return (
								<label htmlFor={ 'checkbox_for_all' }>
									<input
										type={ 'checkbox' }
										id={ 'checkbox_for_all' }
										className={ 'gutena_forms_form_select_all' }
										onClick={ toggleAllCheckboxes }
									/>
								</label>
							);
						}
					},

					body: {
						checkbox: ( { row, index, header } ) => {
							return (
								<label htmlFor={ `select_for_${ row[ header.value ] }` }>
									<input
										type={ 'checkbox' }
										id={ `select_for_${ row[ header.value ] }` }
										className={ 'gutena_forms_form_select' }
										onClick={ uncheckGlobalCheckbox }
										value={ row[ header.value ] }
										onInput={ ( e ) => {
											const value = parseInt( e.target.value );
											valueSetter( value, e.target.checked );
										} }
									/>
								</label>
							);
						},
						...tableChildren.body,
					},

					footer: ( headers ) => {

						return (
							<tr>
								<th colSpan={ headers.length }>
									<div className={ 'gutena-forms__table-footer' }>

										<div>
											<NumberOfPagesComponent />
										</div>
										<div>
											<PaginationComponent />
										</div>

									</div>
								</th>
							</tr>
						);
					},
				} }
			</GutenaFormsTable>
		</div>
	);
};

export default GutenaFormsDatatable;
