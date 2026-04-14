import {Routes, Route, useParams} from 'react-router';
import GutenaFormsSettingsLayout from '../layouts/gutena-forms-settings-layout';
import GutenaFormsPageLayout from '../layouts/gutena-forms-page-layout';
import GutenaFormsDashboard from './gutena-forms-dashboard';
import GuennaFormsKnowledgeBase from './gutena-forms-knowledge-base';

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
		</Routes>
	);
}

export default GutenaFormsBody;
