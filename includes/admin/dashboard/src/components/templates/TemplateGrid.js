import { __ } from '@wordpress/i18n';
import TemplateCard from './TemplateCard';

const TemplateGrid = ( { templates, onSelect } ) => {
	if ( ! templates || templates.length === 0 ) {
		return (
			<div className="gutena-forms__template-empty">
				<p>{ __( 'No templates found.', 'gutena-forms' ) }</p>
			</div>
		);
	}

	return (
		<div className="gutena-forms__template-grid">
			{ templates.map( ( template ) => (
				<TemplateCard
					key={ template.id }
					template={ template }
					onClick={ onSelect }
				/>
			) ) }
		</div>
	);
};

export default TemplateGrid;
