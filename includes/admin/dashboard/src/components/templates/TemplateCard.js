import TemplateProBadge from './TemplateProBadge';

const TemplateCard = ( { template, onClick } ) => {
	return (
		<button
			type="button"
			className="gutena-forms__template-card"
			onClick={ () => onClick( template ) }
		>
			<div className="gutena-forms__template-card-preview">
				<div className="gutena-forms__template-card-preview-frame">
					<img src={ template.preview.image } alt={ template.title } />
				</div>
				{ template.is_pro && <TemplateProBadge /> }
			</div>
			<div className="gutena-forms__template-card-content">
				<h3>{ template.title }</h3>
				<p>{ template.description }</p>
			</div>
		</button>
	);
};

export default TemplateCard;
