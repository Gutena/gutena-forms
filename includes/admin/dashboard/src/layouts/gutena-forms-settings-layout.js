import GutenaFormsLeftMenuNavigation from '../components/gutena-forms-left-menu-navigation';
import GutenaFormsSettingsMetaBox from '../components/gutena-forms-settings-meta-box';
import GutenaFormsSettings from '../components/gutena-forms-settings';

const GutenaFormsSettingsLayout = () => {

	return (
		<div className={ 'gutena-froms__settings-layout' }>

			<div style={ { float: 'left', marginTop: '-1px' } }>
				<GutenaFormsLeftMenuNavigation />
			</div>
			<div style={ { float: 'left' } } className={ 'gutena-forms__main-content-container' }>


				<div className={ 'gutena-forms__container' }>
					<GutenaFormsSettings />
				</div>

			</div>
		</div>
	);
};

export default GutenaFormsSettingsLayout;
