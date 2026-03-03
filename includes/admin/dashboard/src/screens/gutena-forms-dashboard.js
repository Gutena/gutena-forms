import Introduction from '../components/introduction/Introduction';
import { useEffect } from '@wordpress/element';
import { activateLeftMenu } from '../utils/functions';

const GutenaFormsDashboard = ( { setActiveMenu } ) => {

    useEffect( () => {
        setActiveMenu( '/dashboard' );
        activateLeftMenu( 1 );
    }, [] )

    return (
        <div className={ 'gf-introduction-page' }>
            <Introduction />
        </div>
    );
};

export default GutenaFormsDashboard;