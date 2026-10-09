import { Routes, Route } from 'react-router';
import GutenaFormsSettingsLayout from '../layouts/gutena-forms-settings-layout';
import GutenaFormsPageLayout from '../layouts/gutena-forms-page-layout';
import GutenaFormsDashboard from './gutena-forms-dashboard';
import GuennaFormsKnowledgeBase from './gutena-forms-knowledge-base';
import GutenaFormsCreateEntry from './gutena-forms-create-entry';
import GutenaFormsTemplateLibrary from './gutena-forms-template-library';

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
				path={ '/create' }
				element={ <GutenaFormsCreateEntry /> }
			/>
			<Route
				path={ '/templates' }
				element={ <GutenaFormsTemplateLibrary
					showProPopupHandler={ showProPopupHandler }
				/> }
			/>
			<Route
				path={ '/templates/:category' }
				element={ <GutenaFormsTemplateLibrary
					showProPopupHandler={ showProPopupHandler }
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
