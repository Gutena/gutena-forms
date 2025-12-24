import { useState, useEffect } from '@wordpress/element';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { NavLink } from 'react-router';
import Crown from '../icons/crown';
import { gutenaFormsFetchMenus } from '../api';

const GutenaFormsHeader = () => {

	const [ menus, setMenus ] = useState( false );
	const [ loading, setLoading ] = useState( true );

	useEffect( () => {
		setLoading( true );
		gutenaFormsFetchMenus()
			.then( ( response ) => {
				setMenus( response );
				setLoading( false );
			} );
	}, [] );

	return (
		<div className={ 'gutena-forms__header-container' }>
			<div className={ 'gutena-forms_icon-menu-container' }>
				<div className={ 'gutena-forms_icon-menu' }>
					<img src={ `${ gutenaFormsAdmin.pluginURL }assets/img/logo.png` } alt={ 'Gutena Forms Logo' } />
				</div>

				{ ! loading && menus && (
					<nav className={ 'gutena-forms__header-menu' }>
						<ul>
							{ menus.map( ( menu, index ) => {
								if ( menu.external ) {
									return (
										<li key={ index }>
											<a
												href={ menu.slug }
												target="_blank"
												rel="noopener noreferrer"
											>{ menu.title }</a>
										</li>
									);
								} else {
									return (
										<li key={ index }>
											<NavLink
												to={ `settings${ menu.slug }` }
											>
												{ menu.title }
											</NavLink>
										</li>
									);
								}
							} ) }
						</ul>
					</nav>
				) }
			</div>
			<div>
				<Button
					className={ 'gutena-forms__upgrade-button' }
					variant="primary"
					href="https://gutenaforms.com/pricing/?utm_source=plugin_dashboard&utm_medium=website&utm_campaign=free_plugin"
					target="_blank"
					rel="noopener noreferrer"
				>
					<Crown />
					{ __( 'Upgrade Now', 'gutena-froms' ) }
				</Button>
			</div>
		</div>
	);
};

export default GutenaFormsHeader;
