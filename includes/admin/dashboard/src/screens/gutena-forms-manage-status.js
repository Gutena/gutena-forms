import { useState, useEffect } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { FadedBin } from '../icons/bin';
import Ellipse from '../icons/ellipse';
import { gutenaFormsFetchStatus } from '../api';
import {AddNew} from '../icons/plus';
import GutenaFormsSubmitButton from '../components/fields/gutena-forms-submit-button';
import GutenaFormsListBox from '../components/gutena-forms-list-box';

const GutenaFormsManageStatus = () => {
	const [ status, setStatus ] = useState([] );
	const [ loading, setLoading ] = useState( true );
	const [ isGutenaPro, setIsGutenaPro ] = useState( false );

	useEffect( () => {
		setLoading( true );

		gutenaFormsFetchStatus()
			.then( ( statuses ) => {
				// If the request is successful, the user has Gutena Pro.
				// @todo we need to improve this check based on actual response.
			} )
			.catch( error => {
				setStatus( [
					{
						title: __( 'Unread', 'gutena-forms' ),
						color: '#ABB0BA',
					},
					{
						title: __( 'Read', 'gutena-forms' ),
						color: '#A685E0',
					},
					{
						title: __( 'Closed', 'gutena-forms' ),
						color: '#47D17A',
					},
					{
						title: __( 'Hot', 'gutena-forms' ),
						color: '#E64D4D',
					},
					{
						title: __( 'Warm', 'gutena-forms' ),
						color: '#F6A823',
					},
					{
						title: __( 'Cold', 'gutena-forms' ),
						color: '#308CE8',
					},
				] );

				setIsGutenaPro( false );
				setLoading( false );
			})
	}, [] );

	return (
		<div>
			{ ! loading && (
				<div>
					{ ! isGutenaPro && (
						<div className={ 'gutena-forms__pro-wrapper' }>
							<p className={ 'gutena-forms__pro-description' }>
								{ sprintf( __( '%1$s statuses configured', 'gutena-forms' ), status.length ) }
							</p>

							<div>
								<div>
									{ status.map( ( stat, index ) => {
										return (
											<div key={ index }>
												<GutenaFormsListBox
													leftContent={ <Ellipse fill={ stat.color } /> }
													middleContent={ stat.title }
													rightContent={ <FadedBin /> }
													style={ { borderLeftColor: stat.color } }
												/>
											</div>
										);
									} ) }
								</div>

								<div className={ 'gutena-forms__pro-single-list-wrapper gutena-forms__add-new-tag-btn' }>
									<AddNew />
									Add Status
								</div>

								<GutenaFormsSubmitButton label={ __( 'Save Changes', 'gutena-forms' ) } />

							</div>
						</div>
					) }

					{ isGutenaPro && (
						<div>
							Status Management interface goes here.
						</div>
					) }
				</div>
			) }
		</div>
	);
};

export default GutenaFormsManageStatus;
