import { __ } from '@wordpress/i18n';
import Crown from '../../icons/crown';

/**
 * PRO pill shown on template library cards (matches Figma PRO Tag).
 */
const TemplateProBadge = () => (
	<span className="gutena-forms__template-pro-badge">
		<Crown />
		{ __( 'PRO', 'gutena-forms' ) }
	</span>
);

export default TemplateProBadge;
