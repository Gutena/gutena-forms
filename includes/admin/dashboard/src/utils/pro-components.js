import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import { SelectControl } from '@wordpress/components';
import Notes from '../icons/notes';
import { ColouredLock } from '../icons/lock';
import { ucFirst } from './functions';

addFilter(
    'gutenaFormsFree.core.pro-components',
    'gutena-forms-free-dummy-components',
    ( components ) => {

        components['TagsComponent']   = ( { onClick } ) => {

            return (
                <div
                    className={ 'gutena-froms__entry-meta-box' }
                    onClick={ onClick }
                >
                    <h2 className={ 'heading' }>{ __( 'Tags', 'gutena-forms' ) }</h2>
                    <p className="desc">
                        Separate with commas or the Enter key.
                    </p>
                    <div
                        style={ { position: 'absolute', top: '10px', right: '10px' } }
                    >
                        <div
                            style={ {
                                width: '150px',
                            } }
                        >
                            <SelectControl
                                disabled={ true }
                                options={ [
                                    { label: 'Form', value: 'read' },
                                ] }
                                value={ 'read' }
                            />
                        </div>
                    </div>
                </div>
            );
        };
        components['StatusComponent'] = ( { onClick } ) 	=> {

            return (
                <div
                    className={ 'gutena-froms__entry-meta-box dummy-content' }
                    onClick={ onClick }
                >
                    <h2 className={ 'heading' }>{ __( 'Status', 'gutena-forms' ) }</h2>

                    <div
                        style={ { position: 'absolute', top: '10px', right: '10px' } }
                    >
                        <div
                            style={ {
                                width: '150px',
                            } }
                        >
                            <SelectControl
                                disabled={ true }
                                options={ [
                                    { label: 'Read', value: 'read' },
                                ] }
                                value={ 'read' }
                            />
                        </div>
                    </div>
                </div>
            );
        };
        components['NotesComponent']  = ( { onClick } ) 	=> {

            return (
                <div
                    className={ 'gutena-froms__entry-meta-box dummy-content' }
                    onClick={ onClick }
                >
                    <h2 className={ 'heading' }>{ __( 'Notes', 'gutena-forms' ) }</h2>

                    <div
                        className={ 'notes-button' }
                    >
                        <div>
                            Add Notes
                        </div>
                    </div>

                    <div
                        className={ 'notes-container' }
                    >
                        <div className={ 'notes-content' }>
                            <p>
								<span>
									<Notes />
								</span>
                                Add an internal note.
                            </p>
                        </div>
                    </div>
                </div>
            );
        };

        return components;
    },
    1
);

addFilter(
    'gutenaForms.entries.status',
    'gutena-forms-free-dummy-status',
    ( component, args, statuses, proPopup ) => {
        return (
            <div
                className={ 'gutena-forms__dummy-select' }
                onClick={ proPopup }
            >
                <div>{ ucFirst( args.row.status ) }</div>
                <div>
                    <ColouredLock />
                </div>
            </div>
        );
    },
    1
);

addFilter( 'gutenaForms.entries.components', 'gutena-forms-free-dummy-components', ( object ) => {

    if ( ! object.hasPro ) {
        object.components.push(
            () => (
                <div
                    className={ 'gutena-forms__dummy-select' }
                    onClick={ object.showProPopupHandler }
                >
                    <div>All Tags</div>
                    <div>
                        <ColouredLock />
                    </div>
                </div>
            )
        );

        object.components.push(
            () => (
                <div
                    className={ 'gutena-forms__dummy-select' }
                    onClick={ object.showProPopupHandler }
                >
                    <div>All Status</div>
                    <div>
                        <ColouredLock />
                    </div>
                </div>
            )
        );
    }

    return object;
}, 1 );