import { useCallback, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Link, useNavigate } from 'react-router';
import { ArrowLeft } from '../icons/arrow';
import { activateLeftMenu } from '../utils/functions';
import TemplateLibraryPanel from '../components/template-library/template-library-panel';
import {
	PREVIEW_RETURN_LIBRARY,
	TEMPLATE_LIBRARY_PATH,
} from '../utils/template-library-constants';

const GutenaFormsTemplateLibrary = ( {
	setActiveMenu,
	showProPopupHandler,
	previewReturnContext = PREVIEW_RETURN_LIBRARY,
} ) => {
	const navigate = useNavigate();

	const handleStartUseTemplate = useCallback( ( template ) => {
		navigate( `${ TEMPLATE_LIBRARY_PATH }/form-ready/${ template.id }` );
	}, [ navigate ] );

	useEffect( () => {
		setActiveMenu( '/templates' );
		activateLeftMenu( 2 );
	}, [ setActiveMenu ] );

	return (
		<div className="gutena-forms-template-library">
			<div className="gutena-forms-template-library__intro">
				<div className="gutena-forms-template-library__top">
					<div className="gutena-forms-template-library__heading-group">
						<Link
							to="/settings/forms"
							className="gutena-forms-template-library__back-link"
							onClick={ () => setActiveMenu( '/forms' ) }
						>
							<ArrowLeft color="#2C3338" />
							<span className="screen-reader-text">{ __( 'Back to forms', 'gutena-forms' ) }</span>
						</Link>
						<div>
							<p className="gutena-forms-template-library__brand">{ __( 'Gutena Forms', 'gutena-forms' ) }</p>
							<h1 className="gutena-forms-template-library__title">{ __( 'Template Library', 'gutena-forms' ) }</h1>
							<p className="gutena-forms-template-library__helper">
								{ __( 'To get started, choose from our collection of pre-built form templates.', 'gutena-forms' ) }
							</p>
						</div>
					</div>
				</div>

				<TemplateLibraryPanel
					showProPopupHandler={ showProPopupHandler }
					onStartUseTemplate={ handleStartUseTemplate }
					previewReturnContext={ previewReturnContext }
				/>
			</div>
		</div>
	);
};

export default GutenaFormsTemplateLibrary;
