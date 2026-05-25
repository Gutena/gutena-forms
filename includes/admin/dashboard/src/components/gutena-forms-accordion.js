import Arrow from '../icons/arrow';
import { NavLink } from 'react-router';
import { useState } from '@wordpress/element';

const GutenaFormsAccordion = ( { icon, title, items, slug } ) => {

	const [ isOpen, setIsOpen ] = useState( true );

	const toggleAccordion = () => {
		setIsOpen( ! isOpen );
	};

	return (
		<div className={ 'gutena-forms__accordion' }>

			{ slug ? (
				<NavLink
					to={ `/settings/settings/${ slug }` }
					className={ 'gutena-forms__accordion-header' }
				>
					<div>
						<div className={ 'gutena-forms__accordion-icon' }>
							{ icon }
						</div>

						<div className={ 'gutena-forms__accordion-title' }>
							{ title }
						</div>
					</div>
					<div className={ `gutena-forms__accordion-arrow ${ ! isOpen ? 'closed' : '' }` }>
						{ items.length > 0 && <Arrow /> }
					</div>
				</NavLink>
			) : (
				<div
					className={ 'gutena-forms__accordion-header' }
					onClick={ toggleAccordion }
				>
					<div>
						<div className={ 'gutena-forms__accordion-icon' }>
							{ icon }
						</div>

						<div className={ 'gutena-forms__accordion-title' }>
							{ title }
						</div>
					</div>
					<div className={ `gutena-forms__accordion-arrow ${ ! isOpen ? 'closed' : '' }` }>
						{ items.length > 0 && <Arrow /> }
					</div>

				</div>
			) }

			<div className={ `gutena-forms__accordion-items ${ ! isOpen ? 'closed' : '' }` }>
				{ items.map( ( item, index ) => (
					<div
						key={ index }
						className={ 'gutena-forms__accordion-item' }
					>
						<NavLink
							to={ `/settings/settings/${ item.slug }` }
						>
							{ item.title }
						</NavLink>
					</div>
				) ) }
			</div>
		</div>
	);
};

export default GutenaFormsAccordion
