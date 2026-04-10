import { TextControl, ToggleControl } from '@wordpress/components';
import Settings from '../icons/settings';
import Mailchimp from "../icons/mailchimp";
import Brevo from "../icons/brevo";
import ActiveCampaign from "../icons/activecampaign";

const GutenaFormsIntegrations = () => {

	const integrations = [
		{
			title: 'Mailchimp',
			desc: 'This module allows you to automatically add form submissions',
			Icon: Mailchimp,
		},
		{
			title: 'Brevo',
			desc: 'This module allows you to add form contacts to your Brevo li',
			Icon: Brevo,
		},
		{
			title: 'Active Campaign',
			desc: 'This module allows you to send form submissions to your Acti',
			Icon: ActiveCampaign,
		},
	];

	return (
		<div>
			<TextControl />

			<div className={ 'gutena-forms__integrations' }>

				{
					integrations.map( ( integration, index ) => {

						const Icon = integration.Icon;
						return (
							<div
								key={ index }
								className={ 'gutena-forms__integration' }
							>
								<h3>
									<Icon />
									{ integration.title }
								</h3>
								<p>{ integration.desc }...<span>Read More</span></p>

								<div className={ 'gutena-forms__integration-actions' }>
									<div><ToggleControl /></div>
									<div>
										<Settings disabled={ true } />
									</div>
								</div>
							</div>
						);
					} )
				}
			</div>
		</div>
	);
};

export default GutenaFormsIntegrations;
