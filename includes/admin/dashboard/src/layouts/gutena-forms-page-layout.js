import { useState, useEffect } from '@wordpress/element';
import { gutenaFormsFetchSettings } from '../api';
import { useParams } from 'react-router';
import { PageTemplates } from '../utils/templates'

const GutenaFormsPageLayout = () => {
	const { slug } = useParams();
	const [ template, setTemplate ] = useState( false );
	const [ loading, setLoading ] = useState( true );

	useEffect( () => {
		setLoading( true );
		gutenaFormsFetchSettings( slug )
			.then( data => {
				setLoading( false );
				setTemplate( data.fields[0].name )
			} )
	}, [ slug ] );

	const TemplateComponent = PageTemplates[ template ];
	return (
		<div>
			{ ! loading && (
				<>
					{ template && PageTemplates[ template ] && (
						<TemplateComponent />
					) }
				</>
			) }
		</div>
	);
}

export default GutenaFormsPageLayout;
