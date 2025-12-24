import {Routes, Route, useParams} from 'react-router';
import GutenaFormsSettingsLayout from '../layouts/gutena-forms-settings-layout';

const GutenaFormsBody = () => {

	return (
		<Routes>
			<Route
				path={ 'settings/:slug/' }
			/>
			<Route
				path={ 'settings/settings/:settings_id/' }
				element={ <GutenaFormsSettingsLayout /> }
			/>
		</Routes>
	);
}

export default GutenaFormsBody;
