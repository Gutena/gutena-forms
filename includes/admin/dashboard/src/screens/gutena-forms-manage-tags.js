import { useState, useEffect } from '@wordpress/element';
import { gutenaFormsFetchTags } from '../api';
import GutenaFormsSubmitButton from '../components/fields/gutena-forms-submit-button';
import { __, sprintf } from '@wordpress/i18n';
import Bin from '../icons/bin';
import Tag from '../icons/tag';
import { AddNew } from '../icons/plus';
import GutenaFormsListBox from '../components/gutena-forms-list-box';

const GutenaFormsManageTags = () => {

	const [ loading, setLoading ] = useState( true );
	const [ isGutenaPro, setIsGutenaPro ] = useState( false );
	const [ tags, setTags ] = useState( [] );

	useEffect( () => {
		setIsGutenaPro( true );
		gutenaFormsFetchTags()
			.then( ( tags ) => {
				// If the request is successful, the user has Gutena Pro.
				// @todo we need to improve this check based on actual response.
			} )
			.catch( error => {
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
													rightContent={ <Bin />}
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
