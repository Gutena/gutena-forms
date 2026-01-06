import GutenaFormsTable from './gutena-forms-table';
import { useState, useEffect } from '@wordpress/element';
import {Button, SelectControl} from '@wordpress/components';
import Calendar from "../icons/calendar";
import Search from "../icons/search";
import Next from "../icons/next";
import Previous from "../icons/previous";



const GutenaFormsDatatable = ( { headers, data } ) => {
	const [ numberOfRows, setNumberOfRows ] = useState( 10 );
	const [ currentPage, setCurrentPage ] = useState( 1 );
	const [ searchTerm, setSearchTerm ] = useState( '' );
	const totalPages = Math.ceil( data.length / numberOfRows );
	const [ tableData, setTableData ] = useState( [] );
	const [ offset, setOffset ] = useState( 0 );

	useEffect( () => {
		setCurrentPage( 1 );
		setOffset( 0 );
	}, [ numberOfRows ] );

	useEffect( () => {
		const filteredData = data.filter( ( a ) => {
			return Object.values( a ).some( ( value ) =>
				String( value ).toLowerCase().includes( searchTerm.toLowerCase() )
			);
		} );

		setTableData( filteredData.slice( offset, numberOfRows + offset ) );
	}, [ numberOfRows, searchTerm, offset ] );

	const handlePrevPage = () => {
		setCurrentPage( currentPage - 1 );
		setOffset( offset - numberOfRows );
	}

	const handleNextPage = () => {
		setCurrentPage( currentPage + 1 );
		setOffset( offset + numberOfRows );
	}


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

	return (
		<div className={ 'gutena-forms__data-table-container' }>

			<div className={ 'gutena-forms__datatable-header' }>
				<div></div>

				<div>
					<div className={ 'display-inline-block' }>
						<div className={ 'gutena-forms__search-box date-search' }>
							<span>
								<input
									placeholder={ 'mm/dd/yyyy - mm/dd/yyyy' }
									type={ 'text' }
									readOnly
								/>
							</span>
							<Calendar />
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
									<input type={ 'checkbox' } id={ 'checkbox_for_all' } />
								</label>
							);
						}
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
					}
				} }
			</GutenaFormsTable>
		</div>
	);
};

export default GutenaFormsDatatable;
