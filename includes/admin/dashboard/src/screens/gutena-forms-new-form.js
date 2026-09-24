import { useCallback, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { TabPanel } from '@wordpress/components';
import { Link, useNavigate } from 'react-router';
import { ArrowLeft } from '../icons/arrow';
import { activateLeftMenu } from '../utils/functions';
import TemplateLibraryPanel from '../components/template-library/template-library-panel';
import FormLayoutsPicker from '../components/new-form/form-layouts-picker';
import {
	NEW_FORM_PATH,
	PREVIEW_RETURN_NEW_FORM,
} from '../utils/template-library-constants';

const GutenaFormsNewForm = ( { setActiveMenu, showProPopupHandler } ) => {
	const navigate = useNavigate();

	const handleStartUseTemplate = useCallback( ( template ) => {
		navigate( `${ NEW_FORM_PATH }/form-ready/${ template.id }` );
	}, [ navigate ] );

	useEffect( () => {
		setActiveMenu( '/forms' );
		activateLeftMenu( 2 );
	}, [ setActiveMenu ] );

	const tabs = [
		{
			name: 'templates',
			title: __( 'Templates', 'gutena-forms' ),
			className: 'gutena-forms-new-form__tab-templates',
		},
		{
			name: 'layouts',
			title: __( 'Layouts', 'gutena-forms' ),
			className: 'gutena-forms-new-form__tab-layouts',
		},
	];

	return (
		<div className="gutena-forms-new-form">
			<div className="gutena-forms-new-form__top">
				<div className="gutena-forms-new-form__heading-group">
					<Link
						to="/settings/forms"
						className="gutena-forms-new-form__back-link"
						onClick={ () => setActiveMenu( '/forms' ) }
					>
						<ArrowLeft color="#2C3338" />
						<span className="screen-reader-text">{ __( 'Back to forms', 'gutena-forms' ) }</span>
					</Link>
					<div>
						<p className="gutena-forms-new-form__brand">{ __( 'Gutena Forms', 'gutena-forms' ) }</p>
						<h1 className="gutena-forms-new-form__title">{ __( 'New Form', 'gutena-forms' ) }</h1>
						<p className="gutena-forms-new-form__helper">
							{ __( 'Choose a template to get started quickly, or start from a blank layout.', 'gutena-forms' ) }
						</p>
					</div>
				</div>
			</div>

			<TabPanel
				className="gutena-forms-new-form__tabs"
				activeClass="is-active"
				initialTabName="templates"
				tabs={ tabs }
			>
				{ ( tab ) => {
					if ( tab.name === 'layouts' ) {
						return <FormLayoutsPicker />;
					}

					return (
						<TemplateLibraryPanel
							showProPopupHandler={ showProPopupHandler }
							onStartUseTemplate={ handleStartUseTemplate }
							previewReturnContext={ PREVIEW_RETURN_NEW_FORM }
						/>
					);
				} }
			</TabPanel>
		</div>
	);
};

export default GutenaFormsNewForm;
