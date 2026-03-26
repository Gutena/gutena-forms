import { useState, useEffect } from '@wordpress/element';
const GutenaFormsDescWrapper = ( { desc } ) => {
    const [ description, setDescription ] = useState( '' );
    const [ readMore, setReadMore ] = useState( false );

    useEffect( () => {
        setDescription( desc );
    }, [] );

    if ( String( description ).trim().length < 60 ) {
        return (
            <>{ description }</>
        );
    } else {
        if ( readMore ) {
            return (
                <>
                    { description }
                    &nbsp;
                    <span
                        onClick={ () => setReadMore( false ) }
                    >
                        Read Less
                    </span>
                </>
            );
        } else {
            return (
                <>
                    { String( description ).substring( 0, 60 ) }...
                    &nbsp;
                    <span
                        onClick={ () => setReadMore( true ) }
                    >
                        Read More
                    </span>
                </>
            );
        }
    }
};

export default GutenaFormsDescWrapper;