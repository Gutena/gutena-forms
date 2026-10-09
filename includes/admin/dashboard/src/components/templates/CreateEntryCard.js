const CreateEntryCard = ( { icon, title, description, onClick, disabled = false, className = '' } ) => {
	return (
		<button
			type="button"
			className={ `gutena-forms__create-entry-card ${ className } ${ disabled ? 'is-disabled' : '' }` }
			onClick={ disabled ? undefined : onClick }
			disabled={ disabled }
		>
			<div className="gutena-forms__create-entry-card-icon">{ icon }</div>
			<h3>{ title }</h3>
			<p>{ description }</p>
		</button>
	);
};

export default CreateEntryCard;
