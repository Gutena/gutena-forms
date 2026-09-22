import { Routes, Route } from 'react-router';
import GutenaFormsSettingsLayout from '../layouts/gutena-forms-settings-layout';
import GutenaFormsPageLayout from '../layouts/gutena-forms-page-layout';
import GutenaFormsDashboard from './gutena-forms-dashboard';
import GuennaFormsKnowledgeBase from './gutena-forms-knowledge-base';
import GutenaFormsTemplateLibrary from './gutena-forms-template-library';
import GutenaFormsTemplateFormReady from './gutena-forms-template-form-ready';
import { PREVIEW_RETURN_LIBRARY } from '../utils/template-library-constants';

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
				path={ '/settings/templates' }
				element={ <GutenaFormsTemplateLibrary
					showProPopupHandler={ showProPopupHandler }
					setActiveMenu={ setActiveMenu }
					previewReturnContext={ PREVIEW_RETURN_LIBRARY }
				/> }
			/>
			<Route
				path={ '/settings/templates/form-ready/:templateId' }
				element={ <GutenaFormsTemplateFormReady
					showProPopupHandler={ showProPopupHandler }
					setActiveMenu={ setActiveMenu }
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
