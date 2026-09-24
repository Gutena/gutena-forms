import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { Link } from 'react-router';
import { TEMPLATE_LIBRARY_PATH } from '../../utils/template-library-constants';

const FormReadyNotFound = ( {
	libraryPath = TEMPLATE_LIBRARY_PATH,
	backLabel = __( 'Back to Template Library', 'gutena-forms' ),
	onBack,
} ) => (
	<div className="gutena-forms-form-ready__not-found" role="alert">
		<h1>{ __( 'Template not found', 'gutena-forms' ) }</h1>
		<p>
			{ __( 'This template could not be loaded. It may have been removed or is no longer available.', 'gutena-forms' ) }
		</p>
		<Button
			as={ Link }
			to={ libraryPath }
			variant="primary"
			className="gutena-forms-form-ready__back-button"
			onClick={ onBack }
		>
			{ backLabel }
		</Button>
	</div>
);

export default FormReadyNotFound;
