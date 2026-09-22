import { useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { ArrowLeft } from '../../icons/arrow';
import { useTemplateFormPreview } from '../../hooks/use-template-form-preview';
import { PREVIEW_RETURN_FORM_READY } from '../../utils/template-library-constants';
import TemplateFormPreviewField from './template-form-preview-field';
import TemplateFormPreviewSuccess from './template-form-preview-success';

const TemplateFormPreview = ( {
	template,
	onBack,
	onCloseLibrary,
	returnContext,
} ) => {
	const formRef = useRef( null );
	const {
		values,
		errors,
		showSuccess,
		setFieldValue,
		resetPreviewForm,
		handleSubmit,
	} = useTemplateFormPreview( template?.fields || [] );

	const backLabel = returnContext === PREVIEW_RETURN_FORM_READY
		? __( 'Back to Form Ready', 'gutena-forms' )
		: __( 'Back to template details', 'gutena-forms' );

	const handleFormSubmit = ( event ) => {
		const isValid = handleSubmit( event );

		if ( ! isValid ) {
			const firstErrorField = formRef.current?.querySelector( '.gutena-forms-field-error-msg.has-error' );

			if ( firstErrorField ) {
				const fieldGroup = firstErrorField.closest( '.wp-block-gutena-field-group' );
				const focusable = fieldGroup?.querySelector( 'input, textarea, select' );

				if ( focusable ) {
					focusable.focus();
				}
			}
		}
	};

	return (
		<div className="gutena-forms-template-library__form-preview">
			<div className="gutena-forms-template-library__form-preview-header">
				<div className="gutena-forms-template-library__form-preview-nav">
					<Button
						variant="link"
						className="gutena-forms-template-library__form-preview-back"
						onClick={ onBack }
					>
						<ArrowLeft color="#2C3338" />
						{ backLabel }
					</Button>

					{ onCloseLibrary && (
						<Button
							variant="link"
							className="gutena-forms-template-library__form-preview-library-link"
							onClick={ onCloseLibrary }
						>
							{ __( 'Back to Template Library', 'gutena-forms' ) }
						</Button>
					) }
				</div>

				<p className="gutena-forms-template-library__form-preview-notice" role="status">
					{ __( 'This is a sample preview. Submissions are not saved or sent.', 'gutena-forms' ) }
				</p>
			</div>

			<div className="gutena-forms-template-library__form-preview-canvas">
				<form
					ref={ formRef }
					className={ `wp-block-gutena-forms gutena-forms-preview-form${ showSuccess ? ' display-success-message' : '' }` }
					onSubmit={ handleFormSubmit }
					noValidate
					data-gutena-template-preview="true"
				>
					<h2 className="gutena-forms-template-library__form-preview-title">{ template.title }</h2>

					{ template.fields?.map( ( field, index ) => (
						<TemplateFormPreviewField
							key={ field.attributes?.nameAttr || index }
							field={ field }
							index={ index }
							value={ values[ field.attributes?.nameAttr || `field-${ index }` ] }
							onChange={ setFieldValue }
							error={ errors[ field.attributes?.nameAttr || `field-${ index }` ] }
						/>
					) ) }

					<div className="wp-block-buttons gutena-forms-submit-buttons gutena-forms-template-library__form-preview-submit">
						<div className="wp-block-button gutena-forms-submit-button">
							<button type="submit" className="wp-block-button__link wp-element-button">
								{ template.submit_label || __( 'Submit', 'gutena-forms' ) }
							</button>
						</div>
					</div>

					<TemplateFormPreviewSuccess message={ template.success_message } />

					{ showSuccess && (
						<div className="gutena-forms-template-library__form-preview-reset">
							<Button variant="secondary" onClick={ resetPreviewForm }>
								{ __( 'Try again', 'gutena-forms' ) }
							</Button>
						</div>
					) }
				</form>
			</div>
		</div>
	);
};

export default TemplateFormPreview;
