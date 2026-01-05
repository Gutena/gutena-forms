import GutenaFormsTable from './gutena-forms-table';
import { useState, useEffect } from '@wordpress/element';
import {Button, SelectControl} from '@wordpress/components';
import Calendar from "../icons/calendar";
import Search from "../icons/search";
import Next from "../icons/next";
import Previous from "../icons/previous";



const GutenaFormsDatatable = ( { headers, data } ) => {
	const [ tableData, setTableData ] = useState( [] );
	const [ numberOfRowsPerPage, setNumberOfRowsPerPage ] = useState( 10 );
	const [ searchTerm, setSearchTerm ] = useState( '' );
	const [ currentPage, setCurrentPage ] = useState( 1 );
	const [ offSet, setOffset ] = useState( 0 );

	useEffect( () => {
		setTableData( data.splice( offSet, numberOfRowsPerPage ) );
	}, [] );

	const NumberOfPagesComponent = () => {

		return (
			<div className={ 'number-of-items-wrapper' }>
				<div className={ 'display-inline-block' }>
					Page 1 out of 1
				</div>

				<div className={ 'display-inline-block gutena-forms__select-wrapper' }>
					<SelectControl
						value={ numberOfRowsPerPage }
						options={ [
							{ label: '10', value: 10 },
							{ label: '25', value: 25 },
							{ label: '50', value: 50 },
							{ label: '100', value: 100 },
							{ label: 'All', value: -1 },
						] }
						onChange={ ( value ) => setNumberOfRowsPerPage( parseInt( value ) ) }
					/>
				</div>
			</div>
		);
	};

	const PaginationComponent = () => {

		if ( tableData.length <= 0 || -1 === numberOfRowsPerPage ) {
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
					onClick={ () => {
						const newOffset = offSet - numberOfRowsPerPage;
						setOffset( newOffset );
					} }
					disabled={ offSet <= 0 }
				>
					<Previous />
				</Button>

				<Button
					className={ 'gutena-forms__pagination-button current' }
					disabled={ true }
				>1</Button>
				<Button
					className={ 'gutena-forms__pagination-button next-button' }
					onClick={ () => {
						const newOffset = offSet + numberOfRowsPerPage;
						setOffset( newOffset );
					} }
					disabled={ ( offSet + numberOfRowsPerPage ) >= data.length }
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
