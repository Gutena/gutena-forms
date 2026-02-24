import {Routes, Route, useParams} from 'react-router';
import GutenaFormsSettingsLayout from '../layouts/gutena-forms-settings-layout';
import GutenaFormsPageLayout from '../layouts/gutena-forms-page-layout';
import GutenaFormsDashboard from './gutena-forms-dashboard';
import GuennaFormsKnowledgeBase from './gutena-forms-knowledge-base';
import { applyFilters } from '@wordpress/hooks';

const HelloWorld = () => {

	return (
		<>Hello World</>
	);
}
const GutenaFormsBody = ( { showProPopupHandler } ) => {

	return (
		<Routes>
			<Route
				path={ '/' }
				element={ <GutenaFormsDashboard showProPopupHandler={ showProPopupHandler } /> }
			/>
			<Route
				path={ '/settings/dashboard' }
				element={ <GutenaFormsDashboard showProPopupHandler={ showProPopupHandler } /> }
			/>
			<Route
				path={ '/settings/knowledge-base' }
				element={ <GuennaFormsKnowledgeBase showProPopupHandler={ showProPopupHandler } /> }
			/>
			<Route
				path={ 'settings/:slug/' }
				element={ <GutenaFormsPageLayout showProPopupHandler={ showProPopupHandler } /> }
			/>
			<Route
				path={ 'settings/:slug/:id' }
				element={ <GutenaFormsPageLayout showProPopupHandler={ showProPopupHandler } /> }
			/>
			<Route
				path={ 'settings/settings/:settings_id/' }
				element={ <GutenaFormsSettingsLayout showProPopupHandler={ showProPopupHandler } /> }
			/>
			<Route
				path={ 'settings/settings/integration/:settings_id' }
				element={ <GutenaFormsSettingsLayout showProPopupHandler={ showProPopupHandler } /> }
			/>
		</Routes>
	);
}

export default GutenaFormsBody;
