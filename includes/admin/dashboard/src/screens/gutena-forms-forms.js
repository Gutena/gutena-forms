import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { Plus } from '../icons/plus';
import NoFormBanner from '../icons/NoFormBanner';
import GutenaFormsYouTubeModal from '../components/gutena-forms-youtube-modal';
import GutenaFormsTable from '../components/gutena-forms-table';
import GutenaFormsDataTable from '../components/gutena-forms-datatable'
import Edit from '../icons/edit';
import Copy from '../icons/copy';
import Eye from '../icons/eye';
import { Bin } from '../icons/bin';
import { useEffect, useState } from '@wordpress/element';
import {gutenaFormsFetchAllForms, gutenaFormsDeleteForm, deleteMultipleForms} from '../api';
import { toast } from 'react-toastify';

const GutenaFormsForms = () => {
	const [ forms, setForms ] = useState( false );
	const [ loading, setLoading ] = useState( true );

	useEffect( () => {
		setLoading( true );
		gutenaFormsFetchAllForms()
			.then( ( forms ) => {
				setLoading( false );
				setForms( forms );
			} )
			.catch( ( error ) => {
				setLoading( false );
				toast.error( __( 'Failed to load forms.', 'gutena-forms' ) );
				console.error( error );
			} );
	}, [] );

	const handleDeleteForm = ( formId ) => {
		setLoading( true );
		gutenaFormsDeleteForm( formId )
			.then( response => {
				toast.success( __( 'Form deleted successfully.', 'gutena-forms' ) );
				// Refresh forms list
				return gutenaFormsFetchAllForms()
					.then( ( response ) => {
						setLoading( false );
						setForms( response );
					} )
					.catch( ( error ) => {
						setLoading( false );
						toast.error( __( 'Failed to refresh forms list.', 'gutena-forms' ) );
						console.error( error );
					} );
			} )
			.catch( ( error ) => {
				setLoading( false );
				toast.error( __( 'Failed to delete form.', 'gutena-forms' ) );
				console.error( error );
			} );
	}

	const handleBulkActions = ( action, selectedData ) => {
		setLoading( true );
		switch ( action ) {
			case 'delete':
				deleteMultipleForms( selectedData )
					.then( response => {
						toast.success( __( 'Selected forms deleted successfully.', 'gutena-forms' ) );
						// Refresh forms list
						return gutenaFormsFetchAllForms()
							.then( ( response ) => {
								setLoading( false );
								setForms( response );
							} )
							.catch( ( error ) => {
								setLoading( false );
								toast.error( __( 'Failed to refresh forms list.', 'gutena-forms' ) );
								console.error( error );
							} );
					} )
					.catch( ( error ) => {
						setLoading( false );
						toast.error( __( 'Failed to delete forms.', 'gutena-forms' ) );
						console.error( error );
					} );
				break;
		}
	}

	// Empty State Component
	const EmptyState = () => {
		const [ isVideoModalOpen, setIsVideoModalOpen ] = useState( false );

		const handleVideoClick = () => {
			setIsVideoModalOpen( true );
		};

		return (
			<div className={ 'gutena-forms__empty-state' }>
				<div className={ 'gutena-forms__empty-state-content' }>
					{/* Welcome Message */}
					<div className={ 'gutena-forms__empty-state-welcome' }>
						<h2>👋 { __( 'Hi there!', 'gutena-forms' ) }</h2>
						<p>{ __( 'It looks like you haven\'t created any forms yet.', 'gutena-forms' ) }</p>
						<p>{ __( 'You can use Gutena Forms to build contact forms, surveys, and more with just a few clicks.', 'gutena-forms' ) }</p>
					</div>

					{/* Video Tutorial Section */}
					<div className={ 'gutena-forms__empty-state-video-section' } onClick={ handleVideoClick }>
						<NoFormBanner />
					</div>

					{/* Create Button */}
					<div className={ 'gutena-forms__empty-state-actions' }>
						<Button
							href={ 'post-new.php?post_type=gutena_forms' }
							className="gutena-forms-create-first-form-button"
							variant="primary"
						>
							<Plus />
							{ __( 'Create Your First Form', 'gutena-forms' ) }
						</Button>
					</div>

					{/* Help Link */}
					<div className={ 'gutena-forms__empty-state-help' }>
						{ __( 'Need some help?', 'gutena-forms' ) }{ ' ' }
						<a
							href="https://gutenaforms.com/blog/?utm_source=plugin&utm_medium=empty_state&utm_campaign=help_articles"
							target="_blank"
							rel="noopener noreferrer"
						>
							{ __( 'Check out our comprehensive guide.', 'gutena-forms' ) }
						</a>
					</div>

					{/* YouTube Modal */}
					<GutenaFormsYouTubeModal
						isOpen={ isVideoModalOpen }
						onClose={ () => setIsVideoModalOpen( false ) }
						videoUrl="https://www.youtube.com/watch?v=oHNwAfpNOnQ"
					/>
				</div>
			</div>
		);
	};

	return (
		<div>
			<div className={ 'gutena-forms__mb-30' }>
				<h2 className={ 'gutena-forms__page-title' }>
					{ __( 'Gutena Forms', 'gutena-forms' ) }
					<Button
						href={ 'post-new.php?post_type=gutena_forms' }
						className="gutena-forms-add-new-form-button"
						variant="primary"
					>
						<Plus />
						{ __( 'Add New Form', 'gutena-forms' ) }
					</Button>
				</h2>
			</div>

			<div>
				{ ! loading && ( ! forms || forms.length === 0 ) ? (
					<EmptyState />
				) : (
					! loading && forms && forms.length > 0 && (
						<GutenaFormsDataTable
							headers={ [
								{
									key: 'checkbox',
									value: 'id',
									width: '25px',
								},
								{
									key: 'title',
									value: __( 'Form Title', 'gutena-forms' )
								},
								{
									key: 'author',
									value: __( 'Author', 'gutena-forms' ),
									width: '150px',
								},
								{
									key: 'datetime',
									value: __( 'Date & Time', 'gutena-forms' ),
									width: '150px',
								},
								{
									key: 'status',
									value: __( 'Status', 'gutena-forms' ),
									width: '100px',
								},
								{
									key: 'actions',
									value: __( 'Action', 'gutena-forms' ),
									width: '110px',
								}
							] }
							data={ forms }

							tableChildren={ {
								body: {
									actions: ( { row } ) => {
										return (
											<div className={ 'gutena-forms-datatable__action' }>
												<Button
													href={ `post.php?post=${ row.id }&action=edit`}
												>
													<Edit />
												</Button>
												<Button
													href={ row.permalink }
												>
													<Eye />
												</Button>
												<Button
													onClick={ () => handleDeleteForm( row.id ) }
												>
													<Bin />
												</Button>
											</div>
										);
									}
								}
							} }
							handleBulkAction={ handleBulkActions }
						>
						</GutenaFormsDataTable>
					)
				) }
			</div>
		</div>
	);
};

export default GutenaFormsForms;
