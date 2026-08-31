import { __ } from '@wordpress/i18n';

const ProBadge = () => (
	<span className="gutena-forms__pro-badge" aria-label={ __( 'Pro feature', 'gutena-forms' ) }>
		{ __( 'Pro', 'gutena-forms' ) }
	</span>
);

export default ProBadge;
