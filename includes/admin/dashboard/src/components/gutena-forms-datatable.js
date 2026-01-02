import { SelectControl, Button, TextControl } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import Previous from '../icons/previous';
import Next from '../icons/next';
import { gutenaFormsUcFirst } from '../utils/functions';
import Calendar from '../icons/calendar';
import Search from '../icons/search';

const GutenaFormsDatatable = ( { headers = [], data = [], children } ) => {
	const [ numberOfData, setNumberOfData ] = useState( 10 );
	const [ currentPage, setCurrentPage ] = useState( 1 );
	const [ offset, setOffset ] = useState( 0 );
	const totalPages = Math.ceil( data.length / numberOfData );

	// Ensure offset is never negative and slice end doesn't exceed data length
	const safeOffset = Math.max( 0, offset );
	const sliceEnd = numberOfData === -1
		? data.length
		: Math.min( safeOffset + numberOfData, data.length );
	const newData = data.slice( safeOffset, sliceEnd );

	// Recalculate pagination when items per page changes, maintaining relative position
	useEffect( () => {
		if ( numberOfData > 0 && data.length > 0 ) {
			// Calculate which page the current offset corresponds to with new items per page
			const newPage = Math.floor( offset / numberOfData ) + 1;
			const maxPage = Math.ceil( data.length / numberOfData );
			// Ensure the calculated page doesn't exceed maximum pages
			const validPage = Math.min( newPage, maxPage > 0 ? maxPage : 1 );
			setCurrentPage( validPage );
			setOffset( ( validPage - 1 ) * numberOfData );
		} else if ( numberOfData === -1 ) {
			// When showing all items, reset to page 1
			setCurrentPage( 1 );
			setOffset( 0 );
		}
		// Note: offset is intentionally not in dependencies to avoid infinite loops
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ numberOfData, data.length ] );

	// Validate and correct pagination state when data changes
	useEffect( () => {
		if ( numberOfData > 0 && data.length > 0 ) {
			const maxPage = Math.ceil( data.length / numberOfData );
			let needsUpdate = false;
			let newPage = currentPage;
			let newOffset = offset;

			// Check if current page exceeds total pages
			if ( currentPage > maxPage ) {
				newPage = maxPage > 0 ? maxPage : 1;
				newOffset = ( newPage - 1 ) * numberOfData;
				needsUpdate = true;
			}

			// Ensure offset is non-negative
			if ( offset < 0 ) {
				newPage = 1;
				newOffset = 0;
				needsUpdate = true;
			}

			// Ensure offset doesn't exceed data length
			if ( offset >= data.length ) {
				newPage = maxPage > 0 ? maxPage : 1;
				newOffset = Math.max( 0, ( newPage - 1 ) * numberOfData );
				needsUpdate = true;
			}

			if ( needsUpdate ) {
				setCurrentPage( newPage );
				setOffset( newOffset );
			}
		} else if ( data.length === 0 ) {
			// Handle empty data
			setCurrentPage( 1 );
			setOffset( 0 );
		}
	}, [ data.length, numberOfData ] );

	const PaginationComponent = () => {

		if ( numberOfData < 0 ) {
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
					disabled={ currentPage === 1 }
					onClick={ () => {
						if ( currentPage > 1 ) {
							setCurrentPage( currentPage - 1 );
							setOffset( offset - numberOfData );
						}
					} }
				>
					<Previous />
				</Button>
				<Button
					className={ 'gutena-forms__pagination-button current' }
					disabled={ true }
				>{ currentPage }</Button>
				<Button
					className={ 'gutena-forms__pagination-button next-button' }
					disabled={ currentPage === totalPages || totalPages === 0 }
					onClick={ () => {
						if ( currentPage < totalPages ) {
							setCurrentPage( currentPage + 1 );
							setOffset( offset + numberOfData );
						}
					} }
				>
					<Next />
				</Button>
			</div>
		);
	};

	const NumberOfPagesComponent = () => {

		return (
			<div className={ 'number-of-items-wrapper' }>
				<div className={ 'display-inline-block' }>
					Page { currentPage } out of { totalPages > 0 ? totalPages : 1 }
				</div>
				<div className={ 'display-inline-block gutena-forms__select-wrapper' }>
					<SelectControl
						value={ numberOfData }
						options={ [
							{ label: '10', value: 10 },
							{ label: '25', value: 25 },
							{ label: '50', value: 50 },
							{ label: '100', value: 100 },
							{ label: 'All', value: -1 },
						] }
						onChange={ ( value ) => setNumberOfData( parseInt( value ) ) }
					/>
				</div>
			</div>
		);
	}

	return (
		<div className={ 'gutena-forms__data-table-container' }>
			<div className={ 'gutena-forms__data-table-header' }>
				<div>A</div>
				<div>
					<div className={ 'display-inline-block' }>
						<div className={ 'gutena-forms__search-box' }>
							<span>
								mm/dd/yyyy - mm/dd/yyyy
							</span>
							<Calendar />
						</div>
					</div>
					<div className={ 'display-inline-block' }>
						<div className={ 'gutena-forms__search-box' }>
							<Search />
							Search
						</div>
					</div>

				</div>
			</div>

			<table className={ 'gutena-forms__datatable-wrapper' } cellPadding={ 0 } cellSpacing={ 0 }>
				<thead>
				<tr>
					{ headers.map( ( header, index ) => (
						<th
							key={ index }
							style={ { width: header.width ? header.width : 'auto' } }
						>
							{ 'checkbox' === header.key ? (
								<label
									key={ index }
									htmlFor={ `checkbox_for_all` }
								>
									<input
										type="checkbox"
										id={ `checkbox_for_all` }
										className={ 'select-all-checkbox' }
									/>{ ' ' }
									{ header.value }
								</label>
							) : (
								header.value
							) }
						</th>
					) ) }
				</tr>
				</thead>

				<tbody>
				{
					newData.map( ( row, index ) => (
						<tr key={ index }>
							{ headers.map( ( header, index ) => {
								if ( 'checkbox' === header.key ) {
									return (
										<td key={ index }>
											<label
												htmlFor={ `checkbox_${ row.id }` }
											>
												<input
													type="checkbox"
													id={ `checkbox_${ row.id }` }
													className={ 'row-select-checkbox' }
												/>
											</label>
										</td>
									);
								}

								const callableChildren = children[ gutenaFormsUcFirst( header.key ) ];
								if ( callableChildren ) {
									return (
										<td key={ index }>
											{ callableChildren( {
												row: row,
												header: header,
											} ) }
										</td>
									);
								}

								return (
									<td key={ index }>{ row[ header.key ] }</td>
								);
							} ) }
						</tr>
					) )
				}

				<tr>
					<td colSpan={ headers.length }>
						<div className={ 'gutena-forms__table-footer' }>
							<div>
								<NumberOfPagesComponent />
							</div>
							<div>
								<PaginationComponent />
							</div>
						</div>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
	);
};

export default GutenaFormsDatatable;
