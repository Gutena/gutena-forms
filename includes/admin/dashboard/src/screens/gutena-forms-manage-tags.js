import { useState, useEffect } from '@wordpress/element';
import { gutenaFormsFetchTags } from '../api';
import GutenaFormsSubmitButton from '../components/fields/gutena-forms-submit-button';
import { __, sprintf } from '@wordpress/i18n';
import { FadedBin } from '../icons/bin';
import Tag from '../icons/tag';
import { AddNew } from '../icons/plus';
import GutenaFormsListBox from '../components/gutena-forms-list-box';
import GutenaFormsSettingsTemplateSkeleton from '../skeletons/gutena-forms-settings-template-skeleton';

const GutenaFormsManageTags = () => {

	const [ loading, setLoading ] = useState( true );
	const [ isGutenaPro, setIsGutenaPro ] = useState( false );
	const [ tags, setTags ] = useState( [] );

	useEffect( () => {
		setIsGutenaPro( true );
		gutenaFormsFetchTags()
			.then( ( tags ) => {
				if ( tags && typeof tags === 'object' ) {
					const normalizedTags = Object.keys( tags ).map( ( key ) => tags[ key ] );
					setTags( normalizedTags );
				}
				// If the request is successful, the user has Gutena Pro.
				setIsGutenaPro( true );
				setLoading( false );
			} )
			.catch( () => {
				setTags( [
					{ title: __( 'WordPress', 'gutena-forms' ) },
					{ title: __( 'Contact Form', 'gutena-forms' ) },
					{ title: __( 'Newsletter', 'gutena-forms' ) },
				] );
				setLoading( false );
				setIsGutenaPro( false );
			} );
	}, [] );

	return (
		<div>
			{ loading && (
				<GutenaFormsSettingsTemplateSkeleton />
			) }

			{ ! loading && (
				<div>
					{ ! isGutenaPro && (
						<div className={ 'gutena-forms__pro-wrapper' }>
							<p className={ 'gutena-forms__pro-description' }>
								{ sprintf( __( '%1$s tags total', 'gutena-forms' ), tags.length ) }
							</p>

							<div>
								<div>
									{ tags.map( ( tag, index ) => {
										return (
											<div key={ index }>
												<GutenaFormsListBox
													leftContent={ <Tag /> }
													middleContent={ tag.title }
													rightContent={ <FadedBin />}
												/>
											</div>
										);
									} ) }
								</div>

								<div className={ 'gutena-forms__pro-single-list-wrapper gutena-forms__add-new-tag-btn' }>
									<AddNew />
									Add Tags
								</div>

								<GutenaFormsSubmitButton label={ __( 'Save Changes', 'gutena-forms' ) } />
							</div>
						</div>
					) }

					{ isGutenaPro && (
						<div>
							Tags Management interface goes here.
						</div>
					) }
				</div>
			) }
		</div>
	);
};

export default GutenaFormsManageTags;
