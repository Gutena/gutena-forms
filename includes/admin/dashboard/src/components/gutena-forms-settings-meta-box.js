const GutenaFormsSettingsMetaBox = ( { title, description, items } ) => {

	return (
		<div>
			<h2>{ title }</h2>
			<p>{ description }</p>

			<div className={ 'gutena-forms__settings-meta-box' }></div>
		</div>
	);
};

export default GutenaFormsSettingsMetaBox;
