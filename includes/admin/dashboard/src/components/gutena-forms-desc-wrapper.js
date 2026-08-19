import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const GutenaFormsDescWrapper = ( { desc } ) => {
	const [ description, setDescription ] = useState( '' );
	const [ readMore, setReadMore ] = useState( false );

	useEffect( () => {
		setDescription( desc );
	}, [ desc ] );

	if ( String( description ).trim().length < 60 ) {
		return (
			<>{ description }</>
		);
	}

	if ( readMore ) {
		return (
			<>
				{ description }
				&nbsp;
				<span
					className="gutena-forms__desc-toggle"
					onClick={ () => setReadMore( false ) }
					role="button"
					tabIndex={ 0 }
					onKeyDown={ ( event ) => {
						if ( 'Enter' === event.key || ' ' === event.key ) {
							setReadMore( false );
						}
					} }
				>
					{ __( 'Read Less', 'gutena-forms' ) }
				</span>
			</>
		);
	}

	return (
		<>
			{ String( description ).substring( 0, 60 ) }...
			&nbsp;
			<span
				className="gutena-forms__desc-toggle"
				onClick={ () => setReadMore( true ) }
				role="button"
				tabIndex={ 0 }
				onKeyDown={ ( event ) => {
					if ( 'Enter' === event.key || ' ' === event.key ) {
						setReadMore( true );
					}
				} }
			>
				{ __( 'Read More', 'gutena-forms' ) }
			</span>
		</>
	);
};

export default GutenaFormsDescWrapper;
