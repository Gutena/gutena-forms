import {Routes, Route, useParams} from 'react-router';
import GutenaFormsSettingsLayout from '../layouts/gutena-forms-settings-layout';
import GutenaFormsPageLayout from '../layouts/gutena-forms-page-layout';

const GutenaFormsBody = ( { showProPopupHandler } ) => {

	return (
		<Routes>
			<Route
				path={ 'settings/:slug/' }
				element={ <GutenaFormsPageLayout /> }
			/>
			<Route
				path={ 'settings/settings/:settings_id/' }
				element={ <GutenaFormsSettingsLayout showProPopupHandler={ showProPopupHandler } /> }
			/>
		</Routes>
	);
}

export default GutenaFormsBody;
