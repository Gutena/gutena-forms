import GutenaFormsProBadge from '../gutena-forms-pro-badge';
import TemplateLibraryFreeBadge from './template-library-free-badge';

const TemplateLibraryBadges = ( { categoryLabel, isPro } ) => (
	<div className="gutena-forms-template-library__badges">
		{ categoryLabel && (
			<span className="gutena-forms-template-library__category-badge">{ categoryLabel }</span>
		) }
		{ isPro ? <GutenaFormsProBadge /> : <TemplateLibraryFreeBadge /> }
	</div>
);

export default TemplateLibraryBadges;
