import { __ } from '@wordpress/i18n';

const FormCreationCard = ( { icon, title, description, onClick } ) => {
	return (
		<button
			type="button"
			className="gutena-forms__creation-card"
			onClick={ onClick }
		>
			<div className="gutena-forms__creation-card-visual">
				{ icon }
			</div>
			<div className="gutena-forms__creation-card-content">
				<h3>{ title }</h3>
				<p>{ description }</p>
			</div>
		</button>
	);
};

export default FormCreationCard;
