import { SelectControl, Button } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import Previous from '../icons/previous';
import Next from '../icons/next';

const GutenaFormsDatatable = ( { headers = [], data = [], children } ) => {
	const [ numberOfData, setNumberOfData ] = useState( 10 );

	data = data.slice( 0, numberOfData === -1 ? data.length : numberOfData );

	useEffect( () => {

	}, [] );

	const PaginationComponent = ( { data } ) => {

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

	};

	const NumberOfPagesComponent = ( { data } ) => {

		return (
			<div className={ 'number-of-items-wrapper' }>
				<div className={ 'display-inline-block' }>
					Page 1 out of 1
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
		<div>
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
					data.map( ( row, index ) => (
						<tr key={ index }>
							{ headers.map( ( header, index ) => (
								<td key={ index }>
									{ children[ header.key ] ? children[ header.key ](
										{
											row: row,
											header: header,
										}
									) : row[ header.key ] }
								</td>
							) ) }
						</tr>
					) )
				}

				<tr>
					<td colSpan={ headers.length }>
						<div className={ 'gutena-forms__table-footer' }>
							<div>
								<NumberOfPagesComponent data={ data } />
							</div>
							<div>
								<PaginationComponent data={ data } />
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
