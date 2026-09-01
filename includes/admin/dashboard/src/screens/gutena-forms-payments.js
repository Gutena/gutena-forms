import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { Button } from '@wordpress/components';
import { Link } from 'react-router';
import { toast } from 'react-toastify';
import GutenaFormsDatatable from '../components/gutena-forms-datatable';
import GutenaFormsPaymentsEmptyState from '../components/payments/gutena-forms-payments-empty-state';
import { fetchAllPaymentEntries } from '../api/payments';
import { gutenaFormsDeleteEntry } from '../api/entries';
import Eye from '../icons/eye';
import { Bin } from '../icons/bin';
import StripeIcon from '../icons/stripe';
import SquareIcon from '../icons/square';
import EntriesLoading from '../skeletons/entries-loading';
import { activateLeftMenu } from '../utils/functions';
import GutenaFormsProBadge from '../components/gutena-forms-pro-badge';

const GutenaFormsPayments = ( { setActiveMenu } ) => {
	const [ payments, setPayments ] = useState( [] );
	const [ loading, setLoading ] = useState( true );
	const hasPro = ! ! ( typeof gutenaFormsAdmin !== 'undefined' && gutenaFormsAdmin.hasPro );

	useEffect( () => {
		activateLeftMenu( '#/settings/payments' );
		setActiveMenu( '/payments' );
	}, [] );

	const refreshPayments = () => {
		setLoading( true );

		fetchAllPaymentEntries()
			.then( ( list ) => {
				setPayments( list );
				setLoading( false );
			} )
			.catch( () => {
				setPayments( [] );
				setLoading( false );
			} );
	};

	useEffect( () => {
		refreshPayments();
	}, [] );

	const handleDeletePaymentEntry = ( row ) => {
		gutenaFormsDeleteEntry( row.entry_id )
			.then( () => {
				toast.success( __( 'Payment entry deleted successfully', 'gutena-forms' ) );
				refreshPayments();
			} )
			.catch( () => {
				toast.error( __( 'Failed to delete payment entry.', 'gutena-forms' ) );
			} );
	};

	if ( loading ) {
		return <EntriesLoading />;
	}

	return (
		<div className="gutena-forms__payments-screen">
			<h2 className="gutena-forms__page-title">{ __( 'Payment', 'gutena-forms' ) }</h2>

			{ ! payments.length ? (
				<GutenaFormsPaymentsEmptyState />
			) : (
				<GutenaFormsDatatable
					name="payments"
					hideBulkActions
					headers={ [
						{
							key: 'entry_id',
							value: __( 'Entry ID', 'gutena-forms' ),
							width: '100px',
						},
						{
							key: 'payment_id',
							value: __( 'Payment ID', 'gutena-forms' ),
							width: '130px',
						},
						{
							key: 'form_name',
							value: __( 'Form Name', 'gutena-forms' ),
							width: '150px',
						},
						{
							key: 'customer_name',
							value: __( 'Customer', 'gutena-forms' ),
							width: '160px',
						},
						{
							key: 'gateway_label',
							value: __( 'Gateway', 'gutena-forms' ),
							width: '110px',
						},
						{
							key: 'payment_type_label',
							value: __( 'Payment Type', 'gutena-forms' ),
							width: '130px',
						},
						{
							key: 'amount_formatted',
							value: __( 'Amount', 'gutena-forms' ),
							width: '90px',
						},
						{
							key: 'status',
							value: __( 'Status', 'gutena-forms' ),
							width: '100px',
						},
						{
							key: 'added_time',
							value: __( 'Date & Time', 'gutena-forms' ),
							width: '160px',
						},
						{
							key: 'actions',
							value: __( 'Action', 'gutena-forms' ),
							width: '110px',
						},
					] }
					data={ payments }
					tableChildren={ {
						body: {
							entry_id: ( { row } ) => (
								<Link
									className="gutena-forms__payments-table__entry-link"
									to={ `/settings/entry/${ row.entry_id }?tab=payment` }
								>
									{ __( 'Entry', 'gutena-forms' ) } #{ row.entry_id }
								</Link>
							),
							payment_id: ( { row } ) => (
								<div className="gutena-forms__payments-table__payment-id">
									{ row.payment_id || `#${ row.entry_id }` }
								</div>
							),
							form_name: ( { row } ) => (
								<div className="gutena-forms__payments-table__form-name">{ row.form_name }</div>
							),
							customer_name: ( { row } ) => (
								<div className="gutena-forms__payments-table__customer">
									{ row.customer_name && (
										<span className="gutena-forms__payments-table__customer-name">
											{ row.customer_name }
										</span>
									) }
									{ row.customer_email && (
										<span className="gutena-forms__payments-table__customer-email">
											{ row.customer_email }
										</span>
									) }
									{ ! row.customer_name && ! row.customer_email && '—' }
								</div>
							),
							gateway_label: ( { row } ) => (
								<div className="gutena-forms__payments-table__gateway">
									{ 'stripe' === row.gateway && (
										<span className="gutena-forms__payments-table__gateway-icon">
											<StripeIcon />
										</span>
									) }
									{ 'square' === row.gateway && (
										<span className="gutena-forms__payments-table__gateway-icon">
											<SquareIcon size={ 16 } />
										</span>
									) }
									<span>{ row.gateway_label || __( 'Stripe', 'gutena-forms' ) }</span>
								</div>
							),
							payment_type_label: ( { row } ) => (
								<div className="gutena-forms__payments-table__type">
									<span className="gutena-forms__payments-table__type-pill">
										{ row.payment_type_label }
									</span>
									{ row.is_subscription && ! hasPro && <GutenaFormsProBadge /> }
								</div>
							),
							amount_formatted: ( { row } ) => (
								<div className="gutena-forms__payments-table__amount">{ row.amount_formatted }</div>
							),
							status: ( { row } ) => (
								<span className={ `gutena-forms__payment-status is-${ row.status }` }>
									{ row.status_label }
								</span>
							),
							added_time: ( { row } ) => (
								<div className="gutena-forms__payments-table__date">
									{ row.transaction_date || row.added_time }
								</div>
							),
							actions: ( { row } ) => (
								<div className="gutena-forms-datatable__action gutena-forms__payments-table__action">
									<Link
										to={ `/settings/entry/${ row.entry_id }?tab=payment` }
										title={ __( 'View payment', 'gutena-forms' ) }
									>
										<Eye />
									</Link>
									<Button
										title={ __( 'Delete payment entry', 'gutena-forms' ) }
										onClick={ () => handleDeletePaymentEntry( row ) }
									>
										<Bin />
									</Button>
								</div>
							),
						},
					} }
				/>
			) }
		</div>
	);
};

export default GutenaFormsPayments;
