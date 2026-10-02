import { __ } from '@wordpress/i18n';

const FormNoticePreview = ( {
	blockProps,
	variant,
	previewHtml,
	onActivate,
} ) => {
	const handleKeyDown = ( event ) => {
		if ( 'Enter' === event.key || ' ' === event.key ) {
			event.preventDefault();
			onActivate();
		}
	};

	return (
		<div
			{ ...blockProps }
			className={ `${ blockProps.className || '' } gutena-forms-notice-preview is-${ variant } is-readonly`.trim() }
			onClick={ onActivate }
			onKeyDown={ handleKeyDown }
			role="button"
			tabIndex={ 0 }
			aria-label={ __(
				'Edit message in Form Confirmation settings',
				'gutena-forms'
			) }
		>
			<span className="screen-reader-text">
				{ __(
					'Click to edit in Form Confirmation settings',
					'gutena-forms'
				) }
			</span>
			<div
				className="gutena-forms-notice-preview__content"
				dangerouslySetInnerHTML={ { __html: previewHtml } }
			/>
		</div>
	);
};

export default FormNoticePreview;
