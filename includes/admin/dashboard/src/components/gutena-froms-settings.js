import { useParams } from 'react-router';
import Templates from '../utils/gutena-froms-page-templates';

const GutenaFormsSettings = () => {

	const { slug } = useParams();


	const TemplateComponent = Templates[ slug ] || Templates['default'];

	return (
		<>
			<TemplateComponent slug={ slug } />
		</>
	);
};

export default GutenaFormsSettings;
