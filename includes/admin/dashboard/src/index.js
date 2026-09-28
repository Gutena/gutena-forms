import './index.scss';
import domReady from '@wordpress/dom-ready';
import { createRoot, StrictMode, useState } from '@wordpress/element';
import { HashRouter, useNavigate } from 'react-router';
import GutenaFormsToast from './components/gutena-froms-toast';
import GutenaFormsHeader from './components/gutena-forms-header';
import GutenaFormsBody from './screens/gutena-forms-body';
import GutenaFormsProPopup from './components/gutena-forms-pro-popup';
import { TourProvider } from './tour';

import './utils/register-components';

const GutenaFormsApp = () => {

	const [ showProPopup, setShowProPopup ] = useState( false );
	const [ activeMenu, setActiveMenu ] = useState( '' );
	const navigate = useNavigate();

	return (
		<TourProvider
			runtime="dashboard"
			navigate={ navigate }
			setActiveMenu={ setActiveMenu }
			initialPreferences={ gutenaFormsAdmin?.tourPreferences }
			adminURL={ gutenaFormsAdmin?.adminURL || '' }
			dashboardURL={ `${ gutenaFormsAdmin?.adminURL || '' }admin.php?page=gutena-forms` }
		>
			<div>
				<GutenaFormsToast />

				{
					! gutenaFormsAdmin.hasPro && (
						<GutenaFormsProPopup
							isPopup={ true }
							show={ showProPopup }
							hideHandler={ e => setShowProPopup( false ) }
						/>
					)
				}

				<div className={ '' }>
					<GutenaFormsHeader
						activeMenu={ activeMenu }
						setActiveMenu={ setActiveMenu }
					/>

					<div className={ 'gutena-froms__container' }>
						<GutenaFormsBody
							showProPopupHandler={ () => setShowProPopup( true ) }
							setActiveMenu={ setActiveMenu }
						/>
					</div>
				</div>
			</div>
		</TourProvider>
	);
};
domReady( () => {

	const container = document.getElementById( 'gutena-forms__root' );
	if ( container ) {
		createRoot( container )
			.render(
				<HashRouter>
					<StrictMode>
						<GutenaFormsApp />
					</StrictMode>
				</HashRouter>
			);
	}
} );
