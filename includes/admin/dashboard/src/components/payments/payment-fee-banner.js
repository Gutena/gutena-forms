import { __ } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';

const PaymentFeeBanner = () => (
	<div className="gutena-forms__payment-fee-banner">
		<p>
			{ createInterpolateElement(
				__( '2.6% transaction and payment gateway fees apply. <link>Activate license</link> to reduce transaction fees.', 'gutena-forms' ),
				{
					link: (
						<a
							href="https://gutenaforms.com/pricing/?utm_source=plugin_dashboard&utm_medium=website&utm_campaign=free_plugin"
							target="_blank"
							rel="noopener noreferrer"
						/>
					),
				}
			) }
		</p>
	</div>
);

export default PaymentFeeBanner;
