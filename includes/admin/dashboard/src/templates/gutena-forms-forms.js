import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import Plus from '../icons/plus';
import { useState, useEffect } from '@wordpress/element';
import { gutenaFormsFetchForms } from '../api'

const GutenaFormsForms = () => {

	const [ formList, setFormList ] = useState( false );
	const [ loading, setLoading ] = useState( true );

	useEffect( () => {
		setLoading( true );
		gutenaFormsFetchForms()
			.then( forms => {
				setLoading( false );
				setFormList( forms );
			} );
	}, [] );

	return (
		<div>
			<div className={ 'gutena-forms__forms-header' }>

				<h1>{ __( 'Gutena Forms', 'gutena-forms' ) }</h1>
				<Button
					variant="primary"
					className={ 'gutena-froms__primary-button' }
					href={ `${ gutenaFormsAdmin.adminURL }post-new.php?post_type=gutena_forms` }
				>
					<Plus />
					{ __( 'Add New Form', 'gutena-forms' ) }
				</Button>
			</div>

			<div>
				{ ! loading && console.log( formList ) }
			</div>
		</div>
	);
};

export default GutenaFormsForms;
