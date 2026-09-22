import { __, sprintf } from '@wordpress/i18n';
import GutenaFormsProBadge from '../gutena-forms-pro-badge';
import TemplateLibraryFreeBadge from './template-library-free-badge';

const TemplateLibraryCard = ( { template, onPreview } ) => {
	const handleKeyDown = ( event ) => {
		if ( 'Enter' === event.key || ' ' === event.key ) {
			event.preventDefault();
			onPreview( template );
		}
	};

	return (
		<button
			type="button"
			className="gutena-forms-template-library__card"
			onClick={ () => onPreview( template ) }
			onKeyDown={ handleKeyDown }
			aria-label={ sprintf(
				/* translators: %s: template title */
				__( 'Preview %s template', 'gutena-forms' ),
				template.title
			) }
		>
			<div className="gutena-forms-template-library__card-preview">
				{ template.preview_image ? (
					<img
						src={ template.preview_image }
						alt=""
						loading="lazy"
					/>
				) : (
					<div className="gutena-forms-template-library__card-preview-placeholder" aria-hidden="true" />
				) }
				<div className="gutena-forms-template-library__card-badge">
					{ template.is_pro ? <GutenaFormsProBadge /> : <TemplateLibraryFreeBadge /> }
				</div>
			</div>
			<div className="gutena-forms-template-library__card-body">
				<h3 className="gutena-forms-template-library__card-title">{ template.title }</h3>
				<p className="gutena-forms-template-library__card-description">{ template.description }</p>
				{ template.category_label && (
					<span className="gutena-forms-template-library__card-category">{ template.category_label }</span>
				) }
			</div>
		</button>
	);
};

export default TemplateLibraryCard;
