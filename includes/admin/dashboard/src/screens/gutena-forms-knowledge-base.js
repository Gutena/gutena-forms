import { Button } from '@wordpress/components';
import { useEffect } from '@wordpress/element';
import { activateLeftMenu } from '../utils/functions';

const GutenaFormsKnowledgeBase = ( { setActiveMenu } ) => {

    useEffect( () => {
        setActiveMenu( '/knowledge-base' );
        activateLeftMenu( 0 );
    }, [] );

    return (
        <div id={ 'gfp-page-doc' }>
            <div className="gutena-forms-knowledge-base">
                <div className="gutena-docs">
                    <div className="gutena-details">
                        <h2 className="gutena-title">{ gutenaFormsAdmin.gutenaFormsDoc?.topics?.title }</h2>
                        <ol className="gutena-topics">
                            {
                                gutenaFormsAdmin.gutenaFormsDoc?.topics?.items.map( ( item, index ) => {
                                    return(
                                        <li className="gutena-topic" key={ index }>
                                            <a className="gutena-topic-link" href={ item?.link } target="_blank" >{ item?.heading }</a>
                                            <p className="gutena-topic-description">{ item?.description }</p>
                                        </li>
                                    );
                                } )
                            }
                        </ol>
                    </div>
                </div>
                <div className="gutena-support">
                    <div className="gutena-details">
                        <h2 className="gutena-title">{ gutenaFormsAdmin.gutenaFormsDoc?.support?.title }</h2>
                        <p className="gutena-description">{ gutenaFormsAdmin.gutenaFormsDoc?.support?.description }</p>
                        <div className='gf-btn-group'>
                            <Button
                                href={ gutenaFormsAdmin.gutenaFormsDoc?.support?.documentation_link }
                                target='_blank'
                                className='gutena-forms__primary-button'
                                style={ { marginRight: '20px' } }
                            >
                                { gutenaFormsAdmin.gutenaFormsDoc?.support?.documentation_text }
                            </Button>
                            <Button
                                href={ gutenaFormsAdmin.gutenaFormsDashboard?.support_link }
                                target='_blank'
                                className='secondary-button'
                            >
                                { gutenaFormsAdmin.gutenaFormsDoc?.support?.link_text }
                            </Button>
                        </div>
                    </div>
                </div>
                <div className="gutena-changelog">
                    <div className="gutena-details">
                        <h2 className="gutena-title">{ gutenaFormsAdmin.gutenaFormsDoc?.changelog?.title }</h2>
                        <p
                            className="gutena-description"
                            dangerouslySetInnerHTML={{ __html: gutenaFormsAdmin.gutenaFormsDoc?.changelog?.description }}
                        />
                    </div>
                </div>
            </div>
        </div>
    );
}

export default GutenaFormsKnowledgeBase;