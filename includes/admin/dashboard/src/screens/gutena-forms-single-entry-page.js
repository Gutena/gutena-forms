import { __ } from '@wordpress/i18n';

import { useState, useEffect } from '@wordpress/element';

import { ArrowLeft } from '../icons/arrow';

import { Link, NavLink, useSearchParams } from 'react-router';

import { gutenaFormsFetchPrevNextEntry } from '../api/entries';

import { fetchEntryPayment } from '../api/payments';



import GutenaFormsEntryData from '../components/entries/gutena-forms-entry-data';

import GutenaFormsEntryDetails from '../components/entries/gutena-forms-entry-details';

import GutenaFormsEntryPaymentDetails from '../components/entries/gutena-forms-entry-payment-details';

import GutenaFormsEntryPaymentEmpty from '../components/entries/gutena-forms-entry-payment-empty';

import GutenaFormsEntryPaymentLog from '../components/entries/gutena-forms-entry-payment-log';

import GutenaFormsNotes from '../components/entries/gutena-forms-notes';

import GutenaFormsTags from '../components/entries/gutena-forms-tags';

import GutenaFormsRelatedEntries from '../components/entries/gutena-forms-related-entries';

import GutenaFormsStatus from '../components/entries/gutena-forms-status';



const GutenaFormsSingleEntryPage = ( { entryId, showProPopupHandler } ) => {

	const [ searchParams ] = useSearchParams();

	const [ prevEntryId, setPrevEntryId ] = useState( null );

	const [ nextEntryId, setNextEntryId ] = useState( null );

	const [ totalEntries, setTotalEntries ] = useState( 0 );

	const [ loading, setLoading ] = useState( true );

	const [ currentEntry, setCurrentEntry ] = useState( 0 );

	const [ activeTab, setActiveTab ] = useState( 'entry' );

	const [ payment, setPayment ] = useState( null );

	const [ paymentLoading, setPaymentLoading ] = useState( true );



	useEffect( () => {

		setLoading( true );



		gutenaFormsFetchPrevNextEntry( entryId )

			.then( ( response ) => {

				setPrevEntryId( response.prevEntryId );

				setNextEntryId( response.nextEntryId );

				setTotalEntries( response.totalEntries );

				setCurrentEntry( response.serialNo );



				setLoading( false );

			} );

	}, [ entryId ] );



	useEffect( () => {

		setPaymentLoading( true );



		fetchEntryPayment( entryId )

			.then( ( paymentData ) => {

				setPayment( paymentData );

				setPaymentLoading( false );

			} )

			.catch( () => {

				setPayment( { has_payment: false } );

				setPaymentLoading( false );

			} );

	}, [ entryId ] );



	useEffect( () => {

		if ( 'payment' === searchParams.get( 'tab' ) ) {

			setActiveTab( 'payment' );

		} else {

			setActiveTab( 'entry' );

		}

	}, [ entryId, searchParams ] );



	const hasPayment = ! paymentLoading && payment?.has_payment;



	return (

		<div className={ 'gutena-forms__entry-screen' }>

			{ ! loading && (

				<>

					<div style={ { display: 'flex', justifyContent: 'space-between' } }>

						<div>

							<h2 className={ 'heading' } style={ { marginBottom: '30px' } }>

								{ prevEntryId && (

									<Link

										className={ 'gutena-forms__entry-nav-button' }

										to={ `/settings/entry/${ prevEntryId }` }

									>

										<ArrowLeft />

									</Link>

								) }

								&nbsp;

								Entry { currentEntry } / { totalEntries }

								&nbsp;

								{ nextEntryId && (

									<Link

										className={ 'gutena-forms__entry-nav-button' }

										to={ `/settings/entry/${ nextEntryId }` }

									>

									<span style={ { display: 'inline-block', transform: 'scaleX( -1 )' } }>

										<ArrowLeft />

									</span>

									</Link>

								) }

							</h2>

						</div>



						<div className={ 'gutena-forms__submit-button secondary' }>

							<NavLink

								to={ '/settings/entries' }

							>

								<ArrowLeft color={ '#0DA88C' } /> Go Back

							</NavLink>

						</div>

					</div>



					<div className={ 'gutena-forms__entry-screen-container' }>

						<div className={ 'gutena-forms__col-70' }>

							<div className="gutena-forms__entry-tabs">

								<button

									type="button"

									className={ `gutena-forms__entry-tab${ activeTab === 'entry' ? ' is-active' : '' }` }

									onClick={ () => setActiveTab( 'entry' ) }

								>

									{ __( 'Entry', 'gutena-forms' ) }

								</button>

								<button

									type="button"

									className={ `gutena-forms__entry-tab${ activeTab === 'payment' ? ' is-active' : '' }` }

									onClick={ () => setActiveTab( 'payment' ) }

								>

									{ __( 'Payment', 'gutena-forms' ) }

								</button>

							</div>



							{ activeTab === 'entry' && (

								<>

									<GutenaFormsEntryData entryId={ entryId } />

									<GutenaFormsEntryDetails

										entryId={ entryId }

										payment={ payment }

										onViewPayment={ () => setActiveTab( 'payment' ) }

									/>

								</>

							) }



							{ activeTab === 'payment' && (
								paymentLoading ? null : (
									hasPayment ? (
										<GutenaFormsEntryPaymentDetails
											entryId={ entryId }
											payment={ payment }
											onPaymentUpdated={ setPayment }
											showProPopupHandler={ showProPopupHandler }
										/>
									) : (
										<div className="gutena-froms__entry-meta-box">
											<GutenaFormsEntryPaymentEmpty />
										</div>
									)
								)
							) }

						</div>

						<div className={ 'gutena-forms__col-30' }>

							<GutenaFormsStatus

								entryId={ entryId }

								showProPopupHandler={ showProPopupHandler }

							/>

							<GutenaFormsTags

								entryId={ entryId }

								showProPopupHandler={ showProPopupHandler }

							/>



							<GutenaFormsNotes

								entryId={ entryId }

								showProPopupHandler={ showProPopupHandler }

							/>

							{ activeTab === 'payment' && hasPayment && (
								<GutenaFormsEntryPaymentLog payment={ payment } />
							) }

							<GutenaFormsRelatedEntries

								entryId={ entryId }

							/>

						</div>

					</div>

				</>

			) }

		</div>

	);

};



export default GutenaFormsSingleEntryPage;


