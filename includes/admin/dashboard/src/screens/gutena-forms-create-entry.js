import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { useNavigate } from 'react-router';
import { toast } from 'react-toastify';
import CreateEntryCard from '../components/templates/CreateEntryCard';
import { gutenaFormsCreateBlankForm } from '../utils/form-templates-api';

const BlankFormIcon = () => (
	<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
		<rect width="48" height="48" rx="12" fill="#EEF8F6" />
		<rect x="14" y="14" width="20" height="4" rx="2" fill="#0DA88C" />
		<rect x="14" y="22" width="20" height="4" rx="2" fill="#B8E8DF" />
		<rect x="14" y="30" width="14" height="4" rx="2" fill="#B8E8DF" />
	</svg>
);

const TemplateIcon = () => (
	<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
		<rect width="48" height="48" rx="12" fill="#EEF8F6" />
		<rect x="12" y="12" width="10" height="12" rx="2" fill="#0DA88C" />
		<rect x="26" y="12" width="10" height="12" rx="2" fill="#B8E8DF" />
		<rect x="12" y="28" width="24" height="8" rx="2" fill="#B8E8DF" />
	</svg>
);

const AiIcon = () => (
	<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
		<rect width="48" height="48" rx="12" fill="#F3F4F6" />
		<path d="M24 14L27 21H34L28.5 25.5L30.5 33L24 28.5L17.5 33L19.5 25.5L14 21H21L24 14Z" fill="#9CA3AF" />
	</svg>
);

const GutenaFormsCreateEntry = () => {
	const navigate = useNavigate();
	const [ creating, setCreating ] = useState( false );

	const handleBlankForm = async () => {
		if ( creating ) {
			return;
		}

		setCreating( true );
		try {
			const data = await gutenaFormsCreateBlankForm();
			toast.success( __( 'Form created successfully.', 'gutena-forms' ) );
			if ( data.edit_url ) {
				window.location.href = data.edit_url;
			}
		} catch ( error ) {
			setCreating( false );
			toast.error( error?.message || __( 'Failed to create form.', 'gutena-forms' ) );
		}
	};

	const handleAiStub = () => {
		toast.info( __( 'Coming soon', 'gutena-forms' ) );
	};

	return (
		<div className="gutena-forms__create-entry">
			<div className="gutena-forms__create-entry-header">
				<h1>{ __( 'Beautiful forms, without starting from a blank page', 'gutena-forms' ) }</h1>
				<p>{ __( 'Choose how you want to create your next form.', 'gutena-forms' ) }</p>
			</div>

			<div className="gutena-forms__create-entry-cards">
				<CreateEntryCard
					icon={ <BlankFormIcon /> }
					title={ __( 'Blank Form', 'gutena-forms' ) }
					description={ __( 'Start with a minimal form and add your own fields.', 'gutena-forms' ) }
					onClick={ handleBlankForm }
					disabled={ creating }
				/>
				<CreateEntryCard
					icon={ <TemplateIcon /> }
					title={ __( 'Choose a Template', 'gutena-forms' ) }
					description={ __( 'Browse pre-built templates for common use cases.', 'gutena-forms' ) }
					onClick={ () => navigate( '/templates' ) }
				/>
				<CreateEntryCard
					icon={ <AiIcon /> }
					title={ __( 'Create Using AI', 'gutena-forms' ) }
					description={ __( 'Generate a form with AI assistance.', 'gutena-forms' ) }
					onClick={ handleAiStub }
					disabled={ true }
					className="is-coming-soon"
				/>
			</div>
		</div>
	);
};

export default GutenaFormsCreateEntry;
