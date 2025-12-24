import GutenaFormsAccordion from './GutenaFormsAccordion'
import Gear from '../icons/gear';
import Shield from '../icons/shield';
import { useState, useEffect } from '@wordpress/element';
import { gutenaFormsFetchSettingsMenu } from '../api';

const GutenaFormsLeftMenuNavigation = () => {

	const [ loading, setLoading ] = useState( true );
	const [ menus, setMenus ] = useState( false );

	useEffect( () => {
		setLoading( true );

		gutenaFormsFetchSettingsMenu()
			.then( menus => {
				setLoading( false );
				setMenus( menus );
			} )
	}, [] );

	return (
		<div className={ 'gutena-forms__left-menu-navigation' }>

			<div className={ 'gutena-forms__accordion-container' }>
				<div className={ 'gutena-forms__accordion-title' }>
					<div className={ 'gutena-forms__container' }>

						{ ! loading && menus && (
							<>
								{ menus.map( ( menu, key ) => {
									let IconComponent;
										switch ( menu.icon ) {
											case 'Shield':
												IconComponent = <Shield />;
												break;

											case 'Gear':
											default:
												IconComponent = <Gear />;
												break;
										}
									return (
										<div key={ key } style={{ marginBottom: '10px' }}>
											<GutenaFormsAccordion
												icon={ IconComponent }
												title={ menu.title }
												items={ menu.menus }
											/>
										</div>
									);
								} ) }
							</>
						) }

					</div>
				</div>
			</div>

		</div>
	);
}

export default GutenaFormsLeftMenuNavigation;
