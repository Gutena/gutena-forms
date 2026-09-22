import { __ } from '@wordpress/i18n';
import { normalizeTemplateFields } from '../../utils/template-field-utils';

const FormReadyFieldPreview = ( { fields = [], submitLabel, successMessage } ) => {
	const normalizedFields = normalizeTemplateFields( fields );

	if ( ! normalizedFields.length ) {
		return (
			<div className="gutena-forms-form-ready__field-preview">
				<h2>{ __( 'Generated field preview', 'gutena-forms' ) }</h2>
				<p className="gutena-forms-form-ready__field-preview-empty">
					{ __( 'No fields are available to preview for this template.', 'gutena-forms' ) }
				</p>
			</div>
		);
	}

	return (
		<div className="gutena-forms-form-ready__field-preview">
			<h2>{ __( 'Generated field preview', 'gutena-forms' ) }</h2>
			<ul className="gutena-forms-form-ready__field-list">
				{ normalizedFields.map( ( field ) => (
					<li key={ field.id } className="gutena-forms-form-ready__field-item">
						<div className="gutena-forms-form-ready__field-heading">
							<span className="gutena-forms-form-ready__field-label">
								{ field.label }
								{ field.required && (
									<span
										className="gutena-forms-form-ready__field-required"
										aria-label={ __( 'Required', 'gutena-forms' ) }
									>
										*
									</span>
								) }
							</span>
							<span className="gutena-forms-form-ready__field-type">{ field.typeLabel }</span>
						</div>
						{ field.placeholder && (
							<p className="gutena-forms-form-ready__field-meta">
								{ __( 'Placeholder:', 'gutena-forms' ) } { field.placeholder }
							</p>
						) }
						{ field.options.length > 0 && (
							<p className="gutena-forms-form-ready__field-meta">
								{ __( 'Options:', 'gutena-forms' ) } { field.options.join( ', ' ) }
							</p>
						) }
					</li>
				) ) }
			</ul>

			<div className="gutena-forms-form-ready__form-meta">
				<div className="gutena-forms-form-ready__form-meta-item">
					<span className="gutena-forms-form-ready__form-meta-label">
						{ __( 'Submit button label', 'gutena-forms' ) }
					</span>
					<span className="gutena-forms-form-ready__form-meta-value">
						{ submitLabel || __( 'Submit', 'gutena-forms' ) }
					</span>
				</div>
				{ successMessage && (
					<div className="gutena-forms-form-ready__form-meta-item">
						<span className="gutena-forms-form-ready__form-meta-label">
							{ __( 'Success message', 'gutena-forms' ) }
						</span>
						<span className="gutena-forms-form-ready__form-meta-value">{ successMessage }</span>
					</div>
				) }
			</div>
		</div>
	);
};

export default FormReadyFieldPreview;
