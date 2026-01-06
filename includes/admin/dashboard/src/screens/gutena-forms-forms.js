import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { Plus } from '../icons/plus';
import GutenaFormsTable from '../components/gutena-forms-table';
import GutenaFormsDataTable from '../components/gutena-forms-datatable'
import Edit from '../icons/edit';
import Copy from '../icons/copy';
import Eye from '../icons/eye';
import { Bin } from '../icons/bin';
import testdata from '../test-data/data.json';

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
							key: 'datetime',
							value: __( 'Date Created', 'gutena-forms' )
						},
						{
							key: 'actions',
							value: __( 'Action', 'gutena-forms' ),
							width: '100px',
						}
					] }
					data={ testdata.map( ( item, index ) => ( {
						...item,
						title: `${ item.title } #${ index + 1 }`,
					} ) ) }
				/>
			</div>
		</div>
	);
};

export default GutenaFormsForms;
