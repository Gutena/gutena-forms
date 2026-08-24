import { Icon } from '@wordpress/components';
import stripeBlockIcon from './icon.png';

const StripeFieldIcon = () => (
	<Icon
		icon={ () => (
			<img
				src={ stripeBlockIcon }
				alt=""
				width={ 24 }
				height={ 24 }
				style={ { display: 'block', borderRadius: '50%' } }
			/>
		) }
	/>
);

export default StripeFieldIcon;
