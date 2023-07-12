import { __ } from '@wordpress/i18n';
import { gfpIsEmpty } from '../../helper';

const DashboardMenu = () => {

    const pageType = gfpIsEmpty( gutenaFormsDashboard.pagetype ) ? '': gutenaFormsDashboard.pagetype;

    const pageSlugs = gutenaFormsDashboard.dashboard_menu.map( item => item.slug );
    const pageUrl = gutenaFormsDashboard.page_url;

    const isActiveMenu = ( slug = '' ) => ( pageType === slug || ( '' === slug && ! pageSlugs.includes( pageType ) ) ) ? 'active': '';
    
    return( 
        <ul>
            {
                gutenaFormsDashboard.dashboard_menu.map( ( item, index ) => (
                    <li 
                    className={ isActiveMenu( item.slug ) } 
                    key={ 'dashboard-mav-'+index } 
                    >
                        <a href={ pageUrl+item.slug }>
                            { item.title }
                        </a>
                    </li>
                ) )
            }
        </ul>
    );

}

export default DashboardMenu;