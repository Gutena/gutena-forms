import { getTemplateCategoryIcon } from './template-category-icons';

const TemplateCategorySidebar = ( { categories, activeCategory, onSelect } ) => {
	return (
		<aside className="gutena-forms__template-sidebar">
			<ul>
				{ categories.map( ( category ) => {
					const IconComponent = getTemplateCategoryIcon( category.slug );
					const isLink = category.type === 'link';
					const isActive = ! isLink && activeCategory === category.slug;
					const rowClass = [
						'gutena-forms__template-sidebar-item',
						isActive ? 'is-active' : '',
						isLink ? 'is-link' : '',
					]
						.filter( Boolean )
						.join( ' ' );

					const content = (
						<>
							<span className="gutena-forms__template-sidebar-icon" aria-hidden="true">
								{ IconComponent ? <IconComponent /> : null }
							</span>
							<span className="gutena-forms__template-sidebar-label">
								{ category.title }
							</span>
							{ ! isLink && typeof category.count === 'number' && (
								<span className="gutena-forms__template-sidebar-count">
									{ category.count }
								</span>
							) }
						</>
					);

					if ( isLink ) {
						return (
							<li key={ category.slug }>
								<a
									className={ rowClass }
									href={ category.url }
									target={ category.open_in_new_tab ? '_blank' : undefined }
									rel={ category.open_in_new_tab ? 'noopener noreferrer' : undefined }
								>
									{ content }
								</a>
							</li>
						);
					}

					return (
						<li key={ category.slug }>
							<button
								type="button"
								className={ rowClass }
								onClick={ () => onSelect( category.slug ) }
							>
								{ content }
							</button>
						</li>
					);
				} ) }
			</ul>
		</aside>
	);
};

export default TemplateCategorySidebar;
