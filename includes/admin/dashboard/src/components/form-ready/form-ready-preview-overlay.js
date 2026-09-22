import { __ } from '@wordpress/i18n';
import { useEffect, useRef } from '@wordpress/element';
import Close from '../../icons/close';
import { useDialogFocusTrap } from '../../hooks/use-dialog-focus-trap';
import TemplateFormPreview from '../template-library/template-form-preview';
import { PREVIEW_RETURN_FORM_READY } from '../../utils/template-library-constants';

const FormReadyPreviewOverlay = ( { isOpen, template, onClose } ) => {
	const dialogRef = useRef( null );

	useDialogFocusTrap( isOpen, dialogRef, onClose );

	useEffect( () => {
		if ( isOpen ) {
			document.body.style.overflow = 'hidden';
			return () => {
				document.body.style.overflow = '';
			};
		}
		return undefined;
	}, [ isOpen ] );

	if ( ! isOpen || ! template ) {
		return null;
	}

	const handleBackdropClick = ( event ) => {
		if ( event.target === event.currentTarget ) {
			onClose();
		}
	};

	return (
		<div
			className="gutena-forms-form-ready__preview-overlay"
			role="presentation"
			onClick={ handleBackdropClick }
		>
			<div
				ref={ dialogRef }
				className="gutena-forms-form-ready__preview-dialog"
				role="dialog"
				aria-modal="true"
				aria-label={ __( 'Form preview', 'gutena-forms' ) }
			>
				<button
					type="button"
					className="gutena-forms-form-ready__preview-close components-button"
					onClick={ onClose }
					aria-label={ __( 'Close preview', 'gutena-forms' ) }
				>
					<Close />
				</button>
				<TemplateFormPreview
					key={ template.id }
					template={ template }
					onBack={ onClose }
					returnContext={ PREVIEW_RETURN_FORM_READY }
				/>
			</div>
		</div>
	);
};

export default FormReadyPreviewOverlay;
