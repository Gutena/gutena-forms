import { __ } from '@wordpress/i18n';
import Checklist from '../../icons/checklist';
import { normalizeTemplateFields } from '../../utils/template-field-utils';

const TemplateLibraryFieldList = ( { fields = [] } ) => {
	const normalizedFields = normalizeTemplateFields( fields );

	if ( ! normalizedFields.length ) {
		return null;
	}

	return (
		<div className="gutena-forms-template-library__field-list">
			<h3 className="gutena-forms-template-library__field-list-title">
				{ __( 'Fields included', 'gutena-forms' ) }
			</h3>
			<ul>
				{ normalizedFields.map( ( field ) => (
					<li key={ field.id } className="gutena-forms-template-library__field-item">
						<span className="gutena-forms-template-library__field-check" aria-hidden="true">
							<Checklist />
						</span>
						<div className="gutena-forms-template-library__field-content">
							<div className="gutena-forms-template-library__field-heading">
								<span className="gutena-forms-template-library__field-label">
									{ field.label }
									{ field.required && (
										<span className="gutena-forms-template-library__field-required" aria-label={ __( 'Required', 'gutena-forms' ) }>
											*
										</span>
									) }
								</span>
								<span className="gutena-forms-template-library__field-type">{ field.typeLabel }</span>
							</div>
							{ field.options.length > 0 && (
								<p className="gutena-forms-template-library__field-options">
									{ __( 'Options:', 'gutena-forms' ) } { field.options.join( ', ' ) }
								</p>
							) }
							{ field.placeholder && (
								<p className="gutena-forms-template-library__field-meta">
									{ __( 'Placeholder:', 'gutena-forms' ) } { field.placeholder }
								</p>
							) }
						</div>
					</li>
				) ) }
			</ul>
		</div>
	);
};

export default TemplateLibraryFieldList;
