import GutenaFormsForms from '../templates/gutena-forms-forms';

const GutenaFromsPageTemplates = {
	'forms': GutenaFormsForms,

	'default': ( { slug } ) => {

		return (
			<>
				{ slug } Template not found.
			</>
		);
	}
};

export default GutenaFromsPageTemplates;
