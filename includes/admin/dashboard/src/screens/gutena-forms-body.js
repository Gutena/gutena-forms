import {Routes, Route, useParams} from 'react-router';
import GutenaFormsSettingsLayout from '../layouts/gutena-forms-settings-layout';

const GutenaFormsBody = ( { showProPopupHandler } ) => {

	return (
		<Routes>
			<Route
				path={ 'settings/:slug/' }
			/>
			<Route
				path={ 'settings/settings/:settings_id/' }
				element={ <GutenaFormsSettingsLayout showProPopupHandler={ showProPopupHandler } /> }
			/>
		</Routes>
	);
}

export default GutenaFormsBody;
