const GutenaFormsNoticeField = ( { desc } ) => {
	if ( ! desc ) {
		return null;
	}

	return (
		<p
			className={ 'gutena-forms__notice-field' }
			dangerouslySetInnerHTML={ { __html: desc } }
		/>
	);
};

export default GutenaFormsNoticeField;
