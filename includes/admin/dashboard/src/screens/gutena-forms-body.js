import { Routes, Route } from 'react-router';
import GutenaFormsSettingsLayout from '../layouts/gutena-forms-settings-layout';
import GutenaFormsPageLayout from '../layouts/gutena-forms-page-layout';
import GutenaFormsDashboard from './gutena-forms-dashboard';
import GuennaFormsKnowledgeBase from './gutena-forms-knowledge-base';
import GutenaFormsTemplateLibrary from './gutena-forms-template-library';
import GutenaFormsTemplateFormReady from './gutena-forms-template-form-ready';
import GutenaFormsNewForm from './gutena-forms-new-form';
import {
	NEW_FORM_PATH,
	PREVIEW_RETURN_LIBRARY,
	TEMPLATE_LIBRARY_PATH,
} from '../utils/template-library-constants';

const GutenaFormsBody = ( { showProPopupHandler, setActiveMenu } ) => {

	return (
		<Routes>
			<Route
				path={ '/' }
				element={ <GutenaFormsDashboard
					showProPopupHandler={ showProPopupHandler }
					setActiveMenu={ setActiveMenu }
				/> }
			/>
			<Route
				path={ '/settings/dashboard' }
				element={ <GutenaFormsDashboard
					showProPopupHandler={ showProPopupHandler }
					setActiveMenu={ setActiveMenu }
				/> }
			/>
			<Route
				path={ '/settings/knowledge-base' }
				element={ <GuennaFormsKnowledgeBase
					showProPopupHandler={ showProPopupHandler }
					setActiveMenu={ setActiveMenu }
				/> }
			/>
			<Route
				path={ NEW_FORM_PATH }
				element={ <GutenaFormsNewForm
					showProPopupHandler={ showProPopupHandler }
					setActiveMenu={ setActiveMenu }
				/> }
			/>
			<Route
				path={ `${ NEW_FORM_PATH }/form-ready/:templateId` }
				element={ <GutenaFormsTemplateFormReady
					showProPopupHandler={ showProPopupHandler }
					setActiveMenu={ setActiveMenu }
					libraryPath={ NEW_FORM_PATH }
				/> }
			/>
			<Route
				path={ TEMPLATE_LIBRARY_PATH }
				element={ <GutenaFormsTemplateLibrary
					showProPopupHandler={ showProPopupHandler }
					setActiveMenu={ setActiveMenu }
					previewReturnContext={ PREVIEW_RETURN_LIBRARY }
				/> }
			/>
			<Route
				path={ `${ TEMPLATE_LIBRARY_PATH }/form-ready/:templateId` }
				element={ <GutenaFormsTemplateFormReady
					showProPopupHandler={ showProPopupHandler }
					setActiveMenu={ setActiveMenu }
					libraryPath={ TEMPLATE_LIBRARY_PATH }
				/> }
			/>
			<Route
				path={ 'settings/:slug/' }
				element={ <GutenaFormsPageLayout
					showProPopupHandler={ showProPopupHandler }
					setActiveMenu={ setActiveMenu }
				/> }
			/>
			<Route
				path={ 'settings/:slug/:id' }
				element={ <GutenaFormsPageLayout
					showProPopupHandler={ showProPopupHandler }
					setActiveMenu={ setActiveMenu }
				/> }
			/>
			<Route
				path={ 'settings/settings/:settings_id/' }
				element={ <GutenaFormsSettingsLayout
					showProPopupHandler={ showProPopupHandler }
					setActiveMenu={ setActiveMenu }
				/> }
			/>
			<Route
				path={ 'settings/settings/integration/:settings_id' }
				element={ <GutenaFormsSettingsLayout
					showProPopupHandler={ showProPopupHandler }
					setActiveMenu={ setActiveMenu }
				/> }
			/>
		</Routes>
	);
}

export default GutenaFormsBody;
