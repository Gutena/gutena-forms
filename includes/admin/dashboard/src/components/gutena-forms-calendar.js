import { useState, useEffect } from '@wordpress/element';
import { DateRangePicker } from 'react-date-range';
import 'react-date-range/dist/styles.css';
import 'react-date-range/dist/theme/default.css';

const GutenaFormsCalendar = ( { onSelect } ) => {

	const [ selection, setSelection ] = useState( {
		startDate: new Date(),
		endDate: new Date(),
		key: 'selection',
	} );

	const [ active, setActive ] = useState( true );

	const handleSelection = ( selection ) => {
		let { startDate, endDate } = selection.selection;

		setSelection( selection.selection );

		startDate = new Date(startDate);
		endDate = new Date(endDate);

		const pad = ( n ) => ( String( n ).padStart( 2, '0' ) );
		const formatDate = ( date ) => `${ pad( date.getMonth() + 1 ) }/${ pad( date.getDate() ) }/${ date.getFullYear() }`;

		startDate = formatDate( startDate );
		endDate = formatDate( endDate );

		onSelect( { startDate, endDate } );

	};

	document.addEventListener( 'click', ( e ) => {
		if ( active && ! e.target.closest( '.date-search' ) ) {
			setActive( false );
		}
	} );

	return (
		<div className={ 'gutena-forms__calendar-wrapper' }>
			{
				active && (
						<DateRangePicker
							ranges={ [ selection ] }

							onChange={ handleSelection }
						/>
				)
			}
		</div>
	);
};

export default GutenaFormsCalendar;
