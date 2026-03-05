import { useState, useEffect } from '@wordpress/element';
import { SelectControl } from '@wordpress/components';
import Profile from '../icons/profile';
import { gutenaFormsFetchUsers } from '../api';
import GutenaFormsListBox from '../components/gutena-forms-list-box';
import GutenaFormsSubmitButton from '../components/fields/gutena-forms-submit-button';
const GutenaFormsUserAccess = () => {

	const [ users, setUsers ] = useState( [] );
	const [ loading, setLoading ] = useState( true );
	const [ isGutenaPro, setIsGutenaPro ] = useState( false );

	useEffect( () => {
		setLoading( true );

		gutenaFormsFetchUsers()
			.then( ( users ) => {
				// If the request is successful, the user has Gutena Pro.
				// @todo we need to improve this check based on actual response.
			} )
			.catch( error => {
				setUsers( [
					{
						name: 'John Doe',
						role: 'Administrator',
						selected: 'all',
					},
					{
						name: 'Jane Smith',
						role: 'Editor',
						selected: 'limited',
					},
					{
						name: 'Sam Wilson',
						role: 'Viewer',
						selected: 'read-only'
					},
				] );
				setIsGutenaPro( false );
				setLoading( false );
			} )

	}, [] );

	return (
		<div>
			{ ! loading && (
				<div>
					{ ! isGutenaPro && (
						<div className={ 'gutena-forms__pro-wrapper' }>

							<div>
								<div>
									{ users.map( ( user, index ) => {

										return (
											<div key={ index }>
												<GutenaFormsListBox
													leftContent={ <Profile /> }
													middleContent={
														<div className={ 'gutena-forms__user-profile' }>
															{ user.name }
															<span>( { user.role } )</span>
														</div>
													}
													rightContent={
														<SelectControl
															options={ [
																{ label: 'All Access', value: 'all' },
																{ label: 'Limited Access', value: 'limited' },
																{ label: 'Read Only', value: 'read-only' },
																] }
															value={ user.selected }
														/>
													}
												/>
											</div>
										);
									} ) }
								</div>

								<GutenaFormsSubmitButton
									label={ 'Save Changes' }
								/>
							</div>
						</div>
					) }

					{ isGutenaPro && (
						<div>
							User Management interface goes here.
						</div>
					) }
				</div>
			) }
		</div>
	);
};

export default GutenaFormsUserAccess;
