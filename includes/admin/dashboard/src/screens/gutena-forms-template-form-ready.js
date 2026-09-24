import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Link, useParams } from 'react-router';
import { ArrowLeft } from '../icons/arrow';
import { activateLeftMenu } from '../utils/functions';
import { useFormReady } from '../hooks/use-form-ready';
import TemplateLibraryBadges from '../components/template-library/template-library-badges';
import FormReadyNameField from '../components/form-ready/form-ready-name-field';
import FormReadyFieldPreview from '../components/form-ready/form-ready-field-preview';
import FormReadyActions from '../components/form-ready/form-ready-actions';
import FormReadyNotFound from '../components/form-ready/form-ready-not-found';
import FormReadyPreviewOverlay from '../components/form-ready/form-ready-preview-overlay';
import PageLoading from '../skeletons/page-loading';
import {
	NEW_FORM_PATH,
	TEMPLATE_LIBRARY_PATH,
} from '../utils/template-library-constants';

const GutenaFormsTemplateFormReady = ( {
	setActiveMenu,
	showProPopupHandler,
	libraryPath = TEMPLATE_LIBRARY_PATH,
} ) => {
	const { templateId } = useParams();
	const isNewFormFlow = libraryPath === NEW_FORM_PATH;
	const activeMenuPath = isNewFormFlow ? '/forms' : '/templates';
	const libraryBackLabel = isNewFormFlow
		? __( 'Back to New Form', 'gutena-forms' )
		: __( 'Back to Template Library', 'gutena-forms' );

	const {
		template,
		formName,
		setFormName,
		loading,
		notFound,
		proRequired,
		continueLoading,
		isPreviewOpen,
		openPreview,
		closePreview,
		handleContinue,
		handleUpgrade,
	} = useFormReady( templateId, { onUpgrade: showProPopupHandler } );

	useEffect( () => {
		setActiveMenu( activeMenuPath );
		activateLeftMenu( 2 );
	}, [ setActiveMenu, activeMenuPath ] );

	if ( loading ) {
		return (
			<div className="gutena-forms-form-ready">
				<PageLoading />
			</div>
		);
	}

	if ( notFound ) {
		return (
			<div className="gutena-forms-form-ready">
				<FormReadyNotFound
					libraryPath={ libraryPath }
					backLabel={ libraryBackLabel }
					onBack={ () => setActiveMenu( activeMenuPath ) }
				/>
			</div>
		);
	}

	return (
		<div className="gutena-forms-form-ready">
			<div className="gutena-forms-form-ready__header">
				<Link
					to={ libraryPath }
					className="gutena-forms-form-ready__back-link"
					onClick={ () => setActiveMenu( activeMenuPath ) }
				>
					<ArrowLeft color="#2C3338" />
					<span className="screen-reader-text">{ libraryBackLabel }</span>
				</Link>
				<div>
					<p className="gutena-forms-form-ready__brand">{ __( 'Gutena Forms', 'gutena-forms' ) }</p>
					<h1 className="gutena-forms-form-ready__title">{ __( 'Form Ready', 'gutena-forms' ) }</h1>
					<p className="gutena-forms-form-ready__helper">
						{ __( 'Review your template selection, name your form, and continue to the editor.', 'gutena-forms' ) }
					</p>
				</div>
			</div>

			<div className="gutena-forms-form-ready__layout">
				<div className="gutena-forms-form-ready__summary">
					<div className="gutena-forms-form-ready__template-card">
						<h2>{ template.title }</h2>
						<TemplateLibraryBadges
							categoryLabel={ template.category_label }
							isPro={ template.is_pro }
						/>
						<p className="gutena-forms-form-ready__template-description">{ template.description }</p>
						<p className="gutena-forms-form-ready__template-id">
							<span>{ __( 'Template ID', 'gutena-forms' ) }</span>
							<code>{ template.id }</code>
						</p>
					</div>

					<FormReadyNameField
						value={ formName }
						onChange={ setFormName }
						disabled={ ! template.can_use }
					/>

					{ proRequired && (
						<div className="gutena-forms-form-ready__pro-notice" role="status">
							{ __( 'This template requires Gutena Forms Pro before you can create a form from it.', 'gutena-forms' ) }
						</div>
					) }

					<FormReadyActions
						onPreview={ openPreview }
						onContinue={ handleContinue }
						onUpgrade={ handleUpgrade }
						continueLoading={ continueLoading }
						canUse={ template.can_use }
						canPreview={ template.can_preview }
						proRequired={ proRequired }
						libraryPath={ libraryPath }
					/>
				</div>

				<FormReadyFieldPreview
					fields={ template.fields }
					submitLabel={ template.submit_label }
					successMessage={ template.success_message }
				/>
			</div>

			<FormReadyPreviewOverlay
				isOpen={ isPreviewOpen }
				template={ template }
				onClose={ closePreview }
			/>
		</div>
	);
};

export default GutenaFormsTemplateFormReady;
