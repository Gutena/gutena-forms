import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { useState } from '@wordpress/element';
import Close from '../../icons/close';
import Crown from '../../icons/crown';
import TemplateProBadge from './TemplateProBadge';
import TemplateFieldCheck from './TemplateFieldCheck';
import { gutenaFormsCreateFromTemplate } from '../../utils/form-templates-api';
import { toast } from 'react-toastify';

const normalizePreviewField = ( field ) => {
	if ( typeof field === 'string' ) {
		return { label: field, type: '' };
	}
	return {
		label: field?.label ?? '',
		type: field?.type ?? '',
	};
};

const TemplatePreviewModal = ( { template, isOpen, onClose, showProPopupHandler } ) => {
	const [ creating, setCreating ] = useState( false );

	if ( ! isOpen || ! template ) {
		return null;
	}

	const requiresPro = template.is_pro && ! gutenaFormsAdmin.hasPro;
	const previewFields = ( template.preview?.fields ?? [] ).map( normalizePreviewField );

	const handleUseTemplate = async () => {
		if ( requiresPro ) {
			showProPopupHandler();
			return;
		}

		setCreating( true );
		try {
			const data = await gutenaFormsCreateFromTemplate( template.id );
			toast.success( __( 'Form created successfully.', 'gutena-forms' ) );
			if ( data.edit_url ) {
				window.location.href = data.edit_url;
			}
		} catch ( error ) {
			setCreating( false );
			toast.error( error?.message || __( 'Failed to create form.', 'gutena-forms' ) );
		}
	};

	return (
		<div className="gutena-forms__template-modal-overlay" onClick={ onClose }>
			<div
				className="gutena-forms__template-modal"
				onClick={ ( e ) => e.stopPropagation() }
			>
				<button
					type="button"
					className="gutena-forms__template-modal-close"
					onClick={ onClose }
					aria-label={ __( 'Close', 'gutena-forms' ) }
				>
					<Close />
				</button>

				<div className="gutena-forms__template-modal-body">
					<div className="gutena-forms__template-modal-preview">
						<div className="gutena-forms__template-modal-preview-frame">
							<img src={ template.preview.image } alt={ template.title } />
						</div>
					</div>

					<div
						className={
							template.is_pro
								? 'gutena-forms__template-modal-details is-pro-template'
								: 'gutena-forms__template-modal-details'
						}
					>
						<div className="gutena-forms__template-modal-intro">
							<div className="gutena-forms__template-modal-meta">
								<span className="gutena-forms__template-modal-category">
									{ template.category_label }
								</span>
								{ template.is_pro && <TemplateProBadge /> }
							</div>

							<div className="gutena-forms__template-modal-heading">
								<h2>{ template.title }</h2>
								<p>{ template.description }</p>
							</div>
						</div>

						{ previewFields.length > 0 && (
							<div className="gutena-forms__template-modal-fields">
								<h4>{ __( 'Included fields', 'gutena-forms' ) }</h4>
								<ul>
									{ previewFields.map( ( field ) => (
										<li
											key={ field.label }
											className={
												field.type
													? 'gutena-forms__template-modal-field-row has-field-type'
													: 'gutena-forms__template-modal-field-row'
											}
										>
											{ field.type ? (
												<>
													<span className="gutena-forms__template-modal-field-main">
														<TemplateFieldCheck />
														<span className="gutena-forms__template-modal-field-label">
															{ field.label }
														</span>
													</span>
													<span className="gutena-forms__template-modal-field-type">
														{ field.type }
													</span>
												</>
											) : (
												<>
													<TemplateFieldCheck />
													<span className="gutena-forms__template-modal-field-label">
														{ field.label }
													</span>
												</>
											) }
										</li>
									) ) }
								</ul>
							</div>
						) }

						<div className="gutena-forms__template-modal-actions">
							{ requiresPro ? (
								<Button
									variant="primary"
									className="gutena-forms__template-modal-cta is-pro"
									onClick={ showProPopupHandler }
								>
									<Crown />
									{ __( 'Upgrade to Pro', 'gutena-forms' ) }
								</Button>
							) : (
								<Button
									variant="primary"
									className="gutena-forms__template-modal-cta"
									onClick={ handleUseTemplate }
									disabled={ creating }
								>
									{ creating
										? __( 'Creating...', 'gutena-forms' )
										: __( 'Use Template', 'gutena-forms' ) }
								</Button>
							) }
						</div>
					</div>
				</div>
			</div>
		</div>
	);
};

export default TemplatePreviewModal;
