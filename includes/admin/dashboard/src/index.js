import './index.scss';
import domReady from '@wordpress/dom-ready';
import { createRoot, StrictMode } from '@wordpress/element';
import { HashRouter } from 'react-router';
import GutenaFormsToast from './components/gutena-froms-toast';
import GutenaFormsHeader from './components/gutena-forms-header';
import GutenaFormsBody from './screens/gutena-forms-body';
import GutenaFormsProPopup from './components/gutena-forms-pro-popup';

const GutenaFormsApp = () => {

	return (
		<div>
			<GutenaFormsToast />

			{
				! gutenaFormsAdmin.hasPro && (
					<GutenaFormsProPopup isPopup={ true } />
				)
			}

			<div className={ '' }>
				<GutenaFormsHeader />

				<div className={ 'gutena-froms__container' }>
					<GutenaFormsBody />
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
