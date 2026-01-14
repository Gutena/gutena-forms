import { useState, useEffect } from '@wordpress/element';
import {} from '@wordpress/i18n';
import { SelectControl } from '@wordpress/components';
import GutenaFormsDatatable from '../components/gutena-forms-datatable';
import { gutenaFormsFetchAllEntries } from '../api';
const GutenaFormsEntries = () => {

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
					tableChildren={ {} }
					handleBulkAction={ ( ...v ) => { console.log( ...v )} }
					customFilters={ {
						components: [
							FormFilter,
						]
					} }
				/>
			) }
		</div>
	);
};

export default GutenaFormsEntries;
