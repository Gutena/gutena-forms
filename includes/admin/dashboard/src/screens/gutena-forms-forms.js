import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { Plus } from '../icons/plus';
import GutenaFormsDatatable from '../components/gutena-forms-datatable';
import Edit from '../icons/edit';
import Copy from '../icons/copy';
import Eye from '../icons/eye';
import { Bin } from '../icons/bin';

const GutenaFormsForms = () => {
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
				<GutenaFormsDatatable
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
							key: 'datetime',
							value: __( 'Date Created', 'gutena-forms' )
						},
						{
							key: 'actions',
							value: __( 'Action', 'gutena-forms' ),
							width: '100px',
						}
					] }
					data={ [
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
						{
							title: __( 'Contact Us', 'gutena-forms' ),
							datetime: '2024-01-15 10:30 AM',
						},
					] }
					children={ {
						'checkbox' : ( a,b ) => {
							return (
								<input type="checkbox" className={ 'select-form-checkbox' } />
							);
						},

						'actions': ( a,b ) => {
							return (
								<div className={ 'gutena-forms__action-links' }>
									<Button>
										<Edit />
									</Button>
									<Button>
										<Copy />
									</Button>
									<Button>
										<Eye />
									</Button>
									<Button>
										<Bin />
									</Button>
								</div>
							);
						}
					} }
				>
				</GutenaFormsDatatable>
			</div>
		</div>
	);
};

export default GutenaFormsForms;
