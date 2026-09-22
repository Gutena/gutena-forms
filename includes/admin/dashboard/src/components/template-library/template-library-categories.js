import { __, sprintf } from '@wordpress/i18n';
import Gear from '../../icons/gear';
import {
	ALL_TEMPLATES_CATEGORY,
	ALL_TEMPLATES_LABEL,
	COMING_SOON_LABEL,
	REQUEST_TEMPLATE_LABEL,
	REQUEST_TEMPLATE_URL,
} from '../../utils/template-library-constants';
import { getTotalTemplateCount } from '../../utils/template-library-utils';

const CategoryCount = ( { count } ) => (
	<span className="gutena-forms-template-library__category-count" aria-hidden="true">
		{ count }
	</span>
);

const SidebarCategoryButton = ( {
	id,
	label,
	count,
	isActive,
	disabled,
	comingSoon,
	onSelect,
} ) => (
	<li>
		<button
			type="button"
			className={ `gutena-forms-template-library__category-item${ isActive ? ' is-active' : '' }${ disabled ? ' is-disabled' : '' }` }
			onClick={ () => onSelect( id ) }
			disabled={ disabled }
			aria-current={ isActive ? 'true' : undefined }
			aria-disabled={ disabled ? 'true' : undefined }
		>
			<span className="gutena-forms-template-library__category-icon" aria-hidden="true">
				<Gear />
			</span>
			<span className="gutena-forms-template-library__category-label">{ label }</span>
			{ ! disabled && <CategoryCount count={ count } /> }
			{ comingSoon && (
				<span className="gutena-forms-template-library__coming-soon">{ COMING_SOON_LABEL }</span>
			) }
		</button>
	</li>
);

const MobileCategoryChip = ( {
	id,
	label,
	isActive,
	disabled,
	comingSoon,
	onSelect,
} ) => (
	<button
		type="button"
		className={ `gutena-forms-template-library__category-chip${ isActive ? ' is-active' : '' }${ disabled ? ' is-disabled' : '' }` }
		onClick={ () => onSelect( id ) }
		disabled={ disabled }
		aria-current={ isActive ? 'true' : undefined }
		aria-disabled={ disabled ? 'true' : undefined }
	>
		<span>{ label }</span>
		{ comingSoon && (
			<span className="gutena-forms-template-library__coming-soon">{ COMING_SOON_LABEL }</span>
		) }
	</button>
);

const TemplateLibraryCategories = ( {
	activeCategory,
	categoryItems,
	categoryCounts,
	onCategoryChange,
} ) => {
	const allCount = getTotalTemplateCount( categoryCounts );

	const handleSelect = ( categoryId ) => {
		onCategoryChange( categoryId );
	};

	return (
		<>
			<nav
				className="gutena-forms-template-library__sidebar"
				aria-label={ __( 'Template categories', 'gutena-forms' ) }
			>
				<ul className="gutena-forms-template-library__category-list">
					<SidebarCategoryButton
						id={ ALL_TEMPLATES_CATEGORY }
						label={ ALL_TEMPLATES_LABEL }
						count={ allCount }
						isActive={ ALL_TEMPLATES_CATEGORY === activeCategory }
						disabled={ false }
						comingSoon={ false }
						onSelect={ handleSelect }
					/>
					{ categoryItems.map( ( item ) => (
						<SidebarCategoryButton
							key={ item.id }
							id={ item.id }
							label={ item.label }
							count={ item.count }
							isActive={ item.id === activeCategory }
							disabled={ item.comingSoon }
							comingSoon={ item.comingSoon }
							onSelect={ handleSelect }
						/>
					) ) }
					<li>
						<a
							className="gutena-forms-template-library__request-template"
							href={ REQUEST_TEMPLATE_URL }
							target="_blank"
							rel="noopener noreferrer"
							aria-label={ sprintf(
								/* translators: %s: link label */
								__( '%s (opens in a new tab)', 'gutena-forms' ),
								REQUEST_TEMPLATE_LABEL
							) }
						>
							<span className="gutena-forms-template-library__category-icon" aria-hidden="true">
								<Gear />
							</span>
							<span className="gutena-forms-template-library__category-label">{ REQUEST_TEMPLATE_LABEL }</span>
						</a>
					</li>
				</ul>
			</nav>

			<div
				className="gutena-forms-template-library__category-chips"
				role="navigation"
				aria-label={ __( 'Template categories', 'gutena-forms' ) }
			>
				<MobileCategoryChip
					id={ ALL_TEMPLATES_CATEGORY }
					label={ ALL_TEMPLATES_LABEL }
					isActive={ ALL_TEMPLATES_CATEGORY === activeCategory }
					disabled={ false }
					comingSoon={ false }
					onSelect={ handleSelect }
				/>
				{ categoryItems.map( ( item ) => (
					<MobileCategoryChip
						key={ item.id }
						id={ item.id }
						label={ item.label }
						isActive={ item.id === activeCategory }
						disabled={ item.comingSoon }
						comingSoon={ item.comingSoon }
						onSelect={ handleSelect }
					/>
				) ) }
				<a
					className="gutena-forms-template-library__category-chip gutena-forms-template-library__category-chip--link"
					href={ REQUEST_TEMPLATE_URL }
					target="_blank"
					rel="noopener noreferrer"
					aria-label={ sprintf(
						/* translators: %s: link label */
						__( '%s (opens in a new tab)', 'gutena-forms' ),
						REQUEST_TEMPLATE_LABEL
					) }
				>
					{ REQUEST_TEMPLATE_LABEL }
				</a>
			</div>
		</>
	);
};

export default TemplateLibraryCategories;
