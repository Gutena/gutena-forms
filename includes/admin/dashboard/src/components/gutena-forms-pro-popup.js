import { Button } from '@wordpress/components';

const GutenaFormsProPopup = ( { isPopup = false } ) => {

	const featureList = [
		'',
		'',
		'',
		'',
		'',
		'',
	];

	return (
		<div className={ 'gutena-forms__pro-popup' }>
			<div className={ 'gutena-forms__popup-header' }>
				<img src={ `${gutenaFormsAdmin.pluginURL}assets/img/logo.svg` } alt={ 'gutena-forms logo' } />

				<h1>Upgrade to Gutena Pro</h1>
			</div>

			<div className={ 'gutena-forms__popup-actions' }>

				<div>

				</div>

			</div>
		</div>
	);
}

export default GutenaFormsProPopup;
