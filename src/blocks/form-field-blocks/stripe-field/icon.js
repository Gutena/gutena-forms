import { Icon } from '@wordpress/components';
import { CardIcon } from '../../../shared/payments/card-icon';

const stripeFieldBlockIcon = () => (
	<Icon icon={ () => <CardIcon size={ 32 } color="#0DA88C" /> } />
);

export default stripeFieldBlockIcon;
