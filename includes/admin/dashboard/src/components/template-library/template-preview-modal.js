import { useRef, useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import Close from '../../icons/close';
import { useDialogFocusTrap } from '../../hooks/use-dialog-focus-trap';
import TemplateLibraryBadges from './template-library-badges';
import TemplateLibraryFieldList from './template-library-field-list';
import TemplateLibraryModalActions from './template-library-modal-actions';
import TemplateFormPreview from './template-form-preview';

const TemplatePreviewModal = ( {
	isOpen,
	template,
	loading,
	error,
	onClose,
	onUseTemplate,
	onUpgrade,
	returnContext,
} ) => {
	const dialogRef = useRef( null );
	const [ view, setView ] = useState( 'details' );

	useDialogFocusTrap( isOpen, dialogRef, onClose );

	useEffect( () => {
		if ( ! isOpen ) {
			setView( 'details' );
		}
	}, [ isOpen ] );

	useEffect( () => {
		if ( isOpen ) {
			document.body.style.overflow = 'hidden';
			return () => {
				document.body.style.overflow = '';
			};
		}
		return undefined;
	}, [ isOpen ] );

	if ( ! isOpen ) {
		return null;
	}

	const handleBackdropClick = ( event ) => {
		if ( event.target === event.currentTarget ) {
			onClose();
		}
	};

	const handleBackToDetails = () => {
		setView( 'details' );
	};

	const isDetailsView = ! loading && ! error && template && view === 'details';
	const dialogLabelProps = isDetailsView
		? {
			'aria-labelledby': 'gutena-forms-template-preview-title',
			'aria-describedby': 'gutena-forms-template-preview-description',
		}
		: {
			'aria-label': __( 'Template preview', 'gutena-forms' ),
		};

	return (
		<div
			className="gutena-forms-template-library__modal-wrapper"
			role="presentation"
			onClick={ handleBackdropClick }
		>
			<div
				ref={ dialogRef }
				className={ `gutena-forms-template-library__modal${ view === 'preview' ? ' is-preview-view' : '' }` }
				role="dialog"
				aria-modal="true"
				{ ...dialogLabelProps }
			>
				<button
					type="button"
					className="gutena-forms-template-library__modal-close components-button"
					onClick={ onClose }
					aria-label={ __( 'Close', 'gutena-forms' ) }
				>
					<Close />
				</button>

				{ loading && (
					<div className="gutena-forms-template-library__modal-loading" aria-busy="true">
						{ __( 'Loading template…', 'gutena-forms' ) }
					</div>
				) }

				{ ! loading && error && (
					<div className="gutena-forms-template-library__modal-error" role="alert">
						<h2>{ __( 'Template not found', 'gutena-forms' ) }</h2>
						<p>{ __( 'This template could not be loaded. It may have been removed or is unavailable.', 'gutena-forms' ) }</p>
						<button
							type="button"
							className="components-button is-secondary"
							onClick={ onClose }
						>
							{ __( 'Close', 'gutena-forms' ) }
						</button>
					</div>
				) }

				{ ! loading && ! error && template && view === 'details' && (
					<div className="gutena-forms-template-library__modal-layout">
						<div className="gutena-forms-template-library__modal-main">
							<div className="gutena-forms-template-library__modal-intro">
								<h2 id="gutena-forms-template-preview-title">{ template.title }</h2>
								<TemplateLibraryBadges
									categoryLabel={ template.category_label }
									isPro={ template.is_pro }
								/>
								<p id="gutena-forms-template-preview-description">{ template.description }</p>
							</div>

							<TemplateLibraryFieldList fields={ template.fields } />

							<TemplateLibraryModalActions
								template={ template }
								onPreview={ () => setView( 'preview' ) }
								onUseTemplate={ onUseTemplate }
								onUpgrade={ onUpgrade }
								showPreviewAction={ true }
							/>
						</div>

						{ template.preview_image && (
							<div className="gutena-forms-template-library__modal-aside" aria-hidden="true">
								<img src={ template.preview_image } alt="" />
							</div>
						) }
					</div>
				) }

				{ ! loading && ! error && template && view === 'preview' && (
					<TemplateFormPreview
						key={ template.id }
						template={ template }
						onBack={ handleBackToDetails }
						onCloseLibrary={ onClose }
						returnContext={ returnContext }
					/>
				) }
			</div>
		</div>
	);
};

export default TemplatePreviewModal;
