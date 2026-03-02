import Introduction from '../components/introduction/Introduction';
import { useEffect } from '@wordpress/element';

const GutenaFormsDashboard = ( { setActiveMenu } ) => {

    useEffect( () => {
        setActiveMenu( '/dashboard' );
    }, [] )

    return (
        <div className={ 'gf-introduction-page' }>
            <Introduction />
        </div>
    );
};

export default GutenaFormsDashboard;