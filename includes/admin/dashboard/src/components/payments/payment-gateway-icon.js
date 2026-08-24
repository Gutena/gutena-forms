import StripeIcon from '../../icons/stripe';
import SquareIcon from '../../icons/square';

const PaymentGatewayIcon = ( { gateway = 'stripe', size = 18 } ) => {
	if ( 'square' === gateway ) {
		return <SquareIcon size={ size } />;
	}

	return <StripeIcon size={ size } />;
};

export default PaymentGatewayIcon;
