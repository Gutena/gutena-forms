import GutenaFormsLeftMenuNavigation from '../components/gutena-forms-left-menu-navigation';
import GutenaFormsSettings from '../components/gutena-forms-settings';
import { useEffect, useState, useCallback } from '@wordpress/element';
import { useLocation } from 'react-router';
import { activateLeftMenu } from '../utils/functions';

const GutenaFormsSettingsLayout = ( { showProPopupHandler, setActiveMenu } ) => {
	const [ sidebarOpen, setSidebarOpen ] = useState( false );
	const location = useLocation();

	useEffect( () => {
		setActiveMenu( '/settings' );
		activateLeftMenu( 5 );
	}, [] );

	// Close sidebar on route change (menu link clicked)
	useEffect( () => {
		setSidebarOpen( false );
	}, [ location.pathname ] );

	const toggleSidebar = useCallback( () => {
		setSidebarOpen( prev => ! prev );
	}, [] );

	const closeSidebar = useCallback( () => {
		setSidebarOpen( false );
	}, [] );

	return (
		<div className={ 'gutena-froms__settings-layout' }>

			<button
				className={ `gutena-forms__settings-sidebar-toggle${ sidebarOpen ? ' is-open' : '' }` }
				onClick={ toggleSidebar }
				aria-label={ 'Toggle settings menu' }
			>
				<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M2 4.5H16M2 9H16M2 13.5H16" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
				</svg>
			</button>

			<div
				className={ `gutena-forms__settings-sidebar-overlay${ sidebarOpen ? ' is-open' : '' }` }
				onClick={ closeSidebar }
			/>

			<div className={ `gutena-forms__settings-sidebar-wrapper${ sidebarOpen ? ' is-open' : '' }` }>
				<div style={ { float: 'left', marginTop: '-1px' } }>
					<GutenaFormsLeftMenuNavigation />
				</div>
			</div>

			<div style={ { float: 'left' } } className={ 'gutena-forms__main-content-container' }>
				<div className={ 'gutena-forms__container' }>
					<GutenaFormsSettings showProPopupHandler={ showProPopupHandler } />
				</div>
			</div>
		</div>
	);
};

export default GutenaFormsSettingsLayout;
