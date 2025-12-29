import './index.scss';
import domReady from '@wordpress/dom-ready';
import { createRoot, StrictMode, useState } from '@wordpress/element';
import { HashRouter } from 'react-router';
import GutenaFormsToast from './components/gutena-froms-toast';
import GutenaFormsHeader from './components/gutena-forms-header';
import GutenaFormsBody from './screens/gutena-forms-body';
import GutenaFormsProPopup from './components/gutena-forms-pro-popup';

const GutenaFormsApp = () => {

	const [ showProPopup, setShowProPopup ] = useState( false );

	return (
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
				<GutenaFormsHeader />

				<div className={ 'gutena-froms__container' }>
					<GutenaFormsBody
						showProPopupHandler={ () => setShowProPopup( true ) }
					/>
				</div>
			</div>
		</div>
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
