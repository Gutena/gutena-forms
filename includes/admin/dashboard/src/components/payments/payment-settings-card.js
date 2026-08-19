import { useState, useEffect } from '@wordpress/element';
import { NavLink } from 'react-router';
import GutenaFormsDescWrapper from '../gutena-forms-desc-wrapper';
import GutenaFormsToggleField from '../fields/gutena-forms-toggle-field';
import Settings from '../../icons/settings';

const PaymentSettingsCard = ( {
	title,
	desc,
	isEnabled,
	icon,
	name,
	handleSettingsEnable,
	settingsPath = `/settings/settings/payment/${ name }`,
} ) => {
	const [ enabled, setEnabled ] = useState( isEnabled );

	useEffect( () => {
		setEnabled( isEnabled );
	}, [ isEnabled ] );

	const handleToggleChange = ( value ) => {
		setEnabled( value );
		handleSettingsEnable( value, name );
	};

	return (
		<div className="gutena-forms__payment-methods__card">
			<div className="gutena-forms__payment-methods__card-header">
				{ icon }
				<h3>{ title }</h3>
			</div>
			<p>
				<GutenaFormsDescWrapper desc={ desc } />
			</p>
			<div className="gutena-forms__payment-methods__card-actions">
				<div>
					<GutenaFormsToggleField
						id={ name }
						checked={ enabled }
						onChange={ handleToggleChange }
					/>
				</div>
				<div>
					{ enabled ? (
						<NavLink to={ settingsPath }>
							<Settings />
						</NavLink>
					) : (
						<Settings disabled />
					) }
				</div>
			</div>
		</div>
	);
};

export default PaymentSettingsCard;
