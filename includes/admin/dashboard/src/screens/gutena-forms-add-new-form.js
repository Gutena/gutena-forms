import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import { useNavigate } from 'react-router';
import { activateLeftMenu } from '../utils/functions';
import FormCreationCard from '../components/add-new-form/form-creation-card';
import BlankFormIcon from '../icons/blank-form-icon';
import LayoutFormIcon from '../icons/layout-form-icon';
import AiSparkleIcon from '../icons/ai-sparkle-icon';

const GutenaFormsAddNewForm = () => {
	const navigate = useNavigate();

	useEffect( () => {
		activateLeftMenu( 3 );
	}, [] );

	const handleBlankForm = () => {
		window.location.href = 'post-new.php?post_type=gutena_forms';
	};

	const handleChooseLayout = () => {
		navigate( '/settings/add-new-form/layouts' );
	};

	const handleCreateWithAi = () => {
		navigate( '/settings/add-new-form/ai' );
	};

	return (
		<div className="gutena-forms__add-new-form-screen">
			<div className="gutena-forms__add-new-form-header">
				<h2>{ __( 'Create a Form', 'gutena-forms' ) }</h2>
				<p>{ __( 'Build, choose a layout or create forms with AI', 'gutena-forms' ) }</p>
			</div>

			<div className="gutena-forms__add-new-form-divider" />

			<div className="gutena-forms__creation-cards">
				<FormCreationCard
					icon={ <BlankFormIcon /> }
					title={ __( 'Blank Form', 'gutena-forms' ) }
					description={ __( 'Create one from scratch', 'gutena-forms' ) }
					onClick={ handleBlankForm }
				/>
				<FormCreationCard
					icon={ <LayoutFormIcon /> }
					title={ __( 'Choose a Layout', 'gutena-forms' ) }
					description={ __( 'Choose from the predefined layouts.', 'gutena-forms' ) }
					onClick={ handleChooseLayout }
				/>
				<FormCreationCard
					icon={ <AiSparkleIcon /> }
					title={ __( 'Create Using AI', 'gutena-forms' ) }
					description={ __( 'Create a form with AI.', 'gutena-forms' ) }
					onClick={ handleCreateWithAi }
				/>
			</div>
		</div>
	);
};

export default GutenaFormsAddNewForm;
