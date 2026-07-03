import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import { Button } from '@wordpress/components';
import { useNavigate } from 'react-router';
import { activateLeftMenu } from '../utils/functions';
import Previous from '../icons/previous';

const GutenaFormsChooseLayout = () => {
	const navigate = useNavigate();

	useEffect( () => {
		activateLeftMenu( 3 );
	}, [] );

	const handleBack = () => {
		navigate( '/settings/add-new-form' );
	};

	return (
		<div className="gutena-forms__add-new-form-screen gutena-forms__choose-layout-screen">
			<div className="gutena-forms__add-new-form-header">
				<h2>{ __( 'Choose a Layout', 'gutena-forms' ) }</h2>
				<p>{ __( 'Choose from the predefined layouts.', 'gutena-forms' ) }</p>
			</div>

			<div className="gutena-forms__layout-placeholder">
				<p>{ __( 'Form layouts will be available here soon.', 'gutena-forms' ) }</p>
			</div>

			<div className="gutena-forms__add-new-form-actions">
				<Button
					className="gutena-forms__back-button"
					variant="secondary"
					onClick={ handleBack }
				>
					<Previous />
					{ __( 'Back', 'gutena-forms' ) }
				</Button>
			</div>
		</div>
	);
};

export default GutenaFormsChooseLayout;
