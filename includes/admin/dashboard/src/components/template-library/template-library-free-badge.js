import { __ } from '@wordpress/i18n';
import { FREE_LABEL } from '../../utils/template-library-constants';

const TemplateLibraryFreeBadge = () => (
	<span className="gutena-forms-template-library__badge gutena-forms-template-library__badge--free">
		{ FREE_LABEL }
	</span>
);

export default TemplateLibraryFreeBadge;
