import { Button } from '@wordpress/components';
import Checklist from '../icons/checklist';
import { __ } from '@wordpress/i18n';
import Crown from '../icons/crown';
import Close from '../icons/close';
import { useState, useEffect } from '@wordpress/element';

const GutenaFormsProPopup = ( { isPopup = false, show = false, hideHandler } ) => {

	const [ isVisible, setIsVisible ] = useState( false );

	useEffect( () => {
		setIsVisible( show );
	}, [ show ] );

	const handleClosePopupButton = ( e ) => {
		e.preventDefault();

		hideHandler();
		setIsVisible( false );
	}

	const featureList = gutenaFormsAdmin.featureList;

	return (
		<div className={ `gutena-forms__pro-popup-wrapper ${ isVisible ? 'visible' : 'invisible' }` }>
			<div className={ `gutena-forms__pro-popup` }>
				<div className={ 'gutena-forms__popup-header' }>
					<img src={ `${gutenaFormsAdmin.pluginURL}assets/img/logo.svg` } alt={ 'gutena-forms logo' } />

					<h1>Upgrade to Gutena Pro</h1>

					{ isPopup && (
						<div className={ 'gutena-froms__popup-close-button-container' }>
							<Button
								onClick={ handleClosePopupButton }
							>
								<Close />
							</Button>
						</div>
					) }
				</div>

				<div className={ 'gutena-forms__popup-actions' }>

					<div className={ 'gutena-forms__popup-content' }>
						{
							featureList.map( ( feature, index ) => {
								return (
									<div className={ 'gutena-forms__feature' } key={ index }>
										<Checklist />
										{ feature }
									</div>
								);
							} )
						}
					</div>

					<Button className={ 'gutena-forms__pro-upgrade-button' } variant="primary" href="https://gutena.com/forms/pricing/" target="_blank" rel="noopener noreferrer">
						<Crown />
						{ __( 'Get Pro Now - $49/Year', 'gutena-forms' ) }
					</Button>

				</div>
			</div>
		</div>
	);
}

export default GutenaFormsProPopup;
