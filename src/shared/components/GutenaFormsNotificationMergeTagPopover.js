import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, Popover } from '@wordpress/components';

const MergeTagDotsIcon = () => (
	<svg
		width="3"
		height="14"
		viewBox="0 0 3 14"
		fill="none"
		xmlns="http://www.w3.org/2000/svg"
		aria-hidden="true"
	>
		<circle cx="1.5" cy="1.5" r="1.5" fill="currentColor" />
		<circle cx="1.5" cy="7" r="1.5" fill="currentColor" />
		<circle cx="1.5" cy="12.5" r="1.5" fill="currentColor" />
	</svg>
);

const GutenaFormsNotificationMergeTagPopover = ( {
	tags = [],
	tagItems = [],
	onInsert,
	variant = 'dots',
	buttonLabel = __( 'Form tags', 'gutena-forms' ),
	popoverTitle = __( 'Merge Tags', 'gutena-forms' ),
	disabled = false,
} ) => {
	const [ isOpen, setIsOpen ] = useState( false );
	const hasTags = tags.length > 0 || tagItems.length > 0;

	if ( ! hasTags || ! onInsert || disabled ) {
		return null;
	}

	const handleInsert = ( tag ) => {
		onInsert( tag );
		setIsOpen( false );
	};

	const buttonClass =
		'form-tags' === variant
			? 'gutena-forms-notification-merge-tag-popover__form-tags-button'
			: 'gutena-forms-notification-merge-tag-popover__dots-button';

	return (
		<div className="gutena-forms-notification-merge-tag-popover">
			<Button
				type="button"
				className={ buttonClass }
				onClick={ () => setIsOpen( ! isOpen ) }
				aria-expanded={ isOpen }
			>
				{ 'form-tags' === variant ? buttonLabel : <MergeTagDotsIcon /> }
			</Button>

			{ isOpen && (
				<Popover
					className="gutena-forms-notification-merge-tag-popover__panel"
					onClose={ () => setIsOpen( false ) }
					placement="bottom-end"
					offset={ 8 }
				>
					<div className="gutena-forms-notification-merge-tag-popover__content">
						<p className="gutena-forms-notification-merge-tag-popover__title">
							{ popoverTitle }
						</p>

						{ tagItems.length > 0 ? (
							<ul className="gutena-forms-notification-merge-tag-popover__items">
								{ tagItems.map( ( item ) => (
									<li key={ item.tag }>
										<button
											type="button"
											className="gutena-forms-notification-merge-tag-popover__item"
											onClick={ () => handleInsert( item.tag ) }
										>
											<span className="gutena-forms-notification-merge-tag-popover__item-label">
												{ item.label }
											</span>
											<span className="gutena-forms-notification-merge-tag-popover__item-tag">
												{ item.tag }
											</span>
										</button>
									</li>
								) ) }
							</ul>
						) : (
							<ul className="gutena-forms-notification-merge-tag-popover__tags">
								{ tags.map( ( tag ) => (
									<li key={ tag }>
										<button
											type="button"
											className="gutena-forms-notification-merge-tag-popover__tag"
											onClick={ () => handleInsert( tag ) }
										>
											{ tag }
										</button>
									</li>
								) ) }
							</ul>
						) }
					</div>
				</Popover>
			) }
		</div>
	);
};

export default GutenaFormsNotificationMergeTagPopover;
