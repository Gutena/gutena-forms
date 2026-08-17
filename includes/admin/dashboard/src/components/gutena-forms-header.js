import { useState, useEffect, useRef } from '@wordpress/element';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { NavLink } from 'react-router';
import Crown from '../icons/crown';
import Burger from '../icons/burger';
import { gutenaFormsFetchMenus } from '../api';
import { gutenaFormsStrContains } from '../utils/functions';

// Dark-stroke X icon for the header's "menu open" state.
// The shared Close icon uses stroke="white" which is invisible on the
// white header background, so we use a dedicated dark variant here.
const HeaderClose = () => (
	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
		<path d="M6 6L18 18M18 6L6 18" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
	</svg>
);

const GutenaFormsHeader = ( { activeMenu, setActiveMenu } ) => {

	const [ menus, setMenus ] = useState( false );
	const [ loading, setLoading ] = useState( true );
	const [ menuOpen, setMenuOpen ] = useState( false );
	const headerRef = useRef( null );

	useEffect( () => {
		setLoading( true );
		gutenaFormsFetchMenus()
			.then( ( response ) => {
				setMenus( response );
				setLoading( false );
			} );
	}, [] );

	// Close the mobile menu whenever the active route changes.
	useEffect( () => {
		setMenuOpen( false );
	}, [ activeMenu ] );

	// Close on Escape and on click outside the header.
	useEffect( () => {
		if ( ! menuOpen ) {
			return;
		}

		const handleKeyDown = ( e ) => {
			if ( 'Escape' === e.key ) {
				setMenuOpen( false );
			}
		};

		const handleClickOutside = ( e ) => {
			if ( headerRef.current && ! headerRef.current.contains( e.target ) ) {
				setMenuOpen( false );
			}
		};

		document.addEventListener( 'keydown', handleKeyDown );
		document.addEventListener( 'mousedown', handleClickOutside );

		return () => {
			document.removeEventListener( 'keydown', handleKeyDown );
			document.removeEventListener( 'mousedown', handleClickOutside );
		};
	}, [ menuOpen ] );

	const handleNavLinkClick = ( slug ) => {
		setActiveMenu( slug );
		setMenuOpen( false );
	};

	return (
		<div className={ 'gutena-forms__header-container' } ref={ headerRef }>
			<div className={ 'gutena-forms_icon-menu-container' }>
				<div className={ 'gutena-forms_icon-menu' }>
					<NavLink
						to={ '/settings/dashboard' }
					>
						<img
							src={ `${ gutenaFormsAdmin.pluginURL }assets/img/logo.png` }
							alt={ 'Gutena Forms Logo' }
						/>
					</NavLink>
				</div>

				{ ! loading && menus && (
					<nav className={ `gutena-forms__header-menu${ menuOpen ? ' is-open' : '' }` }>
						<ul>
							{ menus.map( ( menu, index ) => {
								if ( menu.external ) {
									return (
										<li key={ index }>
											<a
												href={ menu.slug }
												target="_blank"
												rel="noopener noreferrer"
												onClick={ () => setMenuOpen( false ) }
											>{ menu.title }</a>
										</li>
									);
								} else {
									return (
										<li key={ index }>
											<NavLink
												to={ `settings${ menu.slug }` }
												className={ gutenaFormsStrContains( menu.slug, activeMenu ) ? 'active' : '' }
												onClick={ () => handleNavLinkClick( menu.slug ) }
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
			<div className={ 'gutena-forms__header-right' }>
				{
					! gutenaFormsAdmin.hasPro && (
						<Button
							className={ 'gutena-forms__upgrade-button' }
							variant="primary"
							href="https://gutenaforms.com/pricing/?utm_source=plugin_dashboard&utm_medium=website&utm_campaign=free_plugin"
							target="_blank"
							rel="noopener noreferrer"
						>
							<Crown />
							{ __( 'Upgrade Now', 'gutena-forms' ) }
						</Button>
					)
				}
				<button
					type="button"
					className="gutena-forms__burger-toggle"
					aria-label={ menuOpen ? __( 'Close menu', 'gutena-forms' ) : __( 'Open menu', 'gutena-forms' ) }
					aria-expanded={ menuOpen }
					onClick={ () => setMenuOpen( ( v ) => ! v ) }
				>
					{ menuOpen ? <HeaderClose /> : <Burger /> }
				</button>
			</div>
		</div>
	);
};

export default GutenaFormsHeader;
