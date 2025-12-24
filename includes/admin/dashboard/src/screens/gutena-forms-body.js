import { Routes, Route } from 'react-router';
import GutenaFormsSettings from '../components/gutena-froms-settings'

const GutenaFormsBody = () => {

	return (
		<Routes>
			<Route
				path={ 'settings/:slug/' }
				element={ <GutenaFormsSettings /> }
			/>
		</Routes>
	);
}

export default GutenaFormsBody;
