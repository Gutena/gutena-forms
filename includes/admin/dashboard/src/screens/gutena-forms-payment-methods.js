import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import { TextControl } from '@wordpress/components';
import { toast } from 'react-toastify';
import { fetchAllPaymentGateways, togglePaymentGateway } from '../api/payments';
import PaymentSettingsCard from '../components/payments/payment-settings-card';
import StripeIcon from '../icons/stripe';

const StripePaymentCardIcon = () => <StripeIcon size={ 28 } />;

const IconMap = {
	stripe: StripePaymentCardIcon,
};

const GutenaFormsPaymentMethods = () => {
	const [ gateways, setGateways ] = useState( [] );
	const [ tempGateways, setTempGateways ] = useState( [] );

	useEffect( () => {
		fetchAllPaymentGateways()
			.then( ( items ) => {
				setGateways( items );
				setTempGateways( items );
			} );
	}, [] );

	const handleSearch = ( value ) => {
		if ( String( value ).trim().length ) {
			const filtered = gateways.filter( ( gateway ) => {
				return gateway.title.toLowerCase().includes( value.toLowerCase() );
			} );
			setTempGateways( filtered );
		} else {
			setTempGateways( gateways );
		}
	};

	const handleEnableGateway = ( value, name ) => {
		togglePaymentGateway( value, name )
			.then( ( response ) => {
				toast.success( response.message );
			} );
	};

	return (
		<div className="gutena-forms__payment-methods">
			<div className="gutena-forms__payment-methods__heading">
				<h1>{ __( 'Payments', 'gutena-forms' ) }</h1>
				<p>{ __( 'Connect and manage your payment gateways to securely accept transactions through your forms.', 'gutena-forms' ) }</p>
			</div>

			<TextControl
				className="gutena-forms__payment-methods__search"
				placeholder={ __( 'Search Available Integrations...', 'gutena-forms' ) }
				onChange={ handleSearch }
				__nextHasNoMarginBottom
			/>

			<div className="gutena-forms__payment-methods__grid">
				{ tempGateways.length ? (
					tempGateways.map( ( gateway ) => {
						const IconComponent = IconMap[ gateway.icon ];
						return (
							<PaymentSettingsCard
								key={ gateway.name || gateway.title }
								title={ gateway.title }
								desc={ gateway.desc }
								isEnabled={ gateway.enabled }
								name={ gateway.name }
								icon={ IconComponent ? <IconComponent /> : null }
								handleSettingsEnable={ handleEnableGateway }
								settingsPath={ `/settings/settings/payment/${ gateway.name }` }
							/>
						);
					} )
				) : (
					<p>{ __( 'No payment gateways found.', 'gutena-forms' ) }</p>
				) }
			</div>
		</div>
	);
};

export default GutenaFormsPaymentMethods;
