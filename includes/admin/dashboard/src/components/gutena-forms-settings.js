import { useParams } from 'react-router';
import { useState, useEffect } from '@wordpress/element';
import { gutenaFormsFetchSettings } from '../api';
import GutenaFormsSettingsMetaBox from './gutena-forms-settings-meta-box';
const GutenaFormsSettings = () => {
	const { settings_id } = useParams();
	const [ settings, setSettings ] = useState( false );
	const [ loading, setLoading ] = useState( true );

	useEffect( () => {
		setLoading( true );
		gutenaFormsFetchSettings( settings_id )
			.then( response => {
				setLoading( false );
				setSettings( response );
			} );
	}, [ settings_id ] );

	return (
		<div>
			{ ! loading && settings && (
				<GutenaFormsSettingsMetaBox
					title={ settings.title }
					description={ settings.description }
					items={ settings.fields }
					isPro={ settings['is-pro'] }
				/>
			) }
		</div>
	);
};

export default GutenaFormsSettings;
