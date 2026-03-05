import { __ } from '@wordpress/i18n';
import Lock from '../icons/lock';

const GutenaFormsProBadge = () => {

	return (
		<div className={ 'gutena-forms__pro-badge' }>
			<Lock />
			{ __( 'Pro', 'gutena-forms' ) }
		</div>
	);
};

export default GutenaFormsProBadge;
