import { useState, useEffect } from '@wordpress/element';
import { gutenaFormsFetchSettings } from '../api';
import { useParams } from 'react-router';
import { PageTemplates } from '../utils/templates'

const GutenaFormsPageLayout = ( { showProPopupHandler, setActiveMenu } ) => {
	const { slug } = useParams();
	const [ template, setTemplate ] = useState( false );
	const [ loading, setLoading ] = useState( true );

	useEffect( () => {
		setActiveMenu( `/${ slug }` );
		setLoading( true );
		gutenaFormsFetchSettings( slug )
			.then( data => {
				setLoading( false );
				if ( data.fields && data.fields ) {
					if ( data.fields[0] && 'template' === data.fields[0].type ) {
						setTemplate( data.fields[0].name );
					} else {
						setTemplate( false );
					}
				} else {
					setTemplate( false );
				}
			} );
	}, [ slug ] );

	const TemplateComponent = PageTemplates[ template ];
	return (
		<div className={ 'gutena-forms__page-layout-wrapper' }>
			{ ! loading && (
				<>
					{ template && PageTemplates[ template ] && (
						<TemplateComponent
							showProPopupHandler={ showProPopupHandler }
							setActiveMenu={ setActiveMenu }
						/>
					) }
				</>
			) }
		</div>
	);
}

export default GutenaFormsPageLayout;
