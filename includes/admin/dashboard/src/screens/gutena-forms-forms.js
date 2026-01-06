import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { Plus } from '../icons/plus';
import GutenaFormsTable from '../components/gutena-forms-table';
import GutenaFormsDataTable from '../components/gutena-forms-datatable'
import Edit from '../icons/edit';
import Copy from '../icons/copy';
import Eye from '../icons/eye';
import { Bin } from '../icons/bin';
import { useEffect, useState } from '@wordpress/element';
import {gutenaFormsFetchAllForms, gutenaFormsDeleteForm, deleteMultipleForms} from '../api';
import { toast } from 'react-toastify';

const GutenaFormsForms = () => {
	const [ forms, setForms ] = useState( false );
	const [ loading, setLoading ] = useState( true );

	useEffect( () => {
		setLoading( true );
		gutenaFormsFetchAllForms()
			.then( ( forms ) => {
				setLoading( false );
				setForms( forms );
			} )
	}, [] );

	const handleDeleteForm = ( formId ) => {
		setLoading( true );
		gutenaFormsDeleteForm( formId )
			.then( response => {
				toast.success( __( 'Form deleted successfully.', 'gutena-forms' ) );
				// Refresh forms list
				return gutenaFormsFetchAllForms()
					.then( ( response ) => {
						setLoading( false );
						setForms( response );
					} );
			} );
	}

	const handleBulkActions = ( action, selectedData ) => {
		setLoading( true );
		switch ( action ) {
			case 'delete':
				deleteMultipleForms( selectedData )
					.then( response => {
						toast.success( __( 'Selected forms deleted successfully.', 'gutena-forms' ) );
						// Refresh forms list
						return gutenaFormsFetchAllForms()
							.then( ( response ) => {
								setLoading( false );
								setForms( response );
							} );
					} );
				break;
		}
	}

	return (
		<div>
			<div className={ 'gutena-forms__mb-30' }>
				<h2 className={ 'gutena-forms__page-title' }>
					{ __( 'Gutena Forms', 'gutena-forms' ) }
					<Button
						href={ 'post-new.php?post_type=gutena_forms' }
						className="gutena-forms-add-new-form-button"
						variant="primary"
					>
						<Plus />
						{ __( 'Add New Form', 'gutena-forms' ) }
					</Button>
				</h2>
			</div>

			<div>
				{ ! loading && (
					<GutenaFormsDataTable
						headers={ [
							{
								key: 'checkbox',
								value: '',
								width: '25px',
							},
							{
								key: 'title',
								value: __( 'Form Title', 'gutena-forms' )
							},
							{
								key: 'author',
								value: __( 'Author', 'gutena-forms' ),
								width: '150px',
							},
							{
								key: 'datetime',
								value: __( 'Date Created', 'gutena-forms' ),
								width: '150px',
							},
							{
								key: 'status',
								value: __( 'Status', 'gutena-forms' ),
								width: '100px',
							},
							{
								key: 'actions',
								value: __( 'Action', 'gutena-forms' ),
								width: '110px',
							}
						] }
						data={ forms }

						datatableChildren={ {
							body: {
								actions: ( { row } ) => {
									return (
										<div className={ 'gutena-forms-datatable__action' }>
											<Button
												href={ `post.php?post=${ row.id }&action=edit`}
											>
												<Edit />
											</Button>
											<Button
												href={ row.permalink }
											>
												<Eye />
											</Button>
											<Button
												onClick={ () => handleDeleteForm( row.id ) }
											>
												<Bin />
											</Button>
										</div>
									);
								}
							}
						} }
						handleBulkAction={ handleBulkActions }
					>
					</GutenaFormsDataTable>
				) }
			</div>
		</div>
	);
};

export default GutenaFormsForms;
