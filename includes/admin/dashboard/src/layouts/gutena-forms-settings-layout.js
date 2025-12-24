import GutenaFormsLeftMenuNavigation from '../components/gutena-forms-left-menu-navigation';
import GutenaFormsSettingsMetaBox from '../components/gutena-forms-settings-meta-box';
import { useParams } from 'react-router';

const GutenaFormsSettingsLayout = () => {

	return (
		<div className={ 'gutena-froms__settings-layout' }>

			<div style={ { float: 'left' } }>
				<GutenaFormsLeftMenuNavigation />
			</div>
			<div style={ { float: 'left' } } className={ 'gutena-forms__main-content-container' }>


				<div className={ 'gutena-forms__container' }>
					<GutenaFormsSettingsMetaBox
						title={ 'General Settings' }
						description={ 'Enable Honeypot Security, which helps block automated bots without affecting real users.' }
					/>
				</div>

			</div>
		</div>
	);
};

export default GutenaFormsSettingsLayout;
