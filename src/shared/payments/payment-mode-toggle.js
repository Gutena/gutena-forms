import { __ } from '@wordpress/i18n';

const PaymentModeToggle = ( { value = 'test', onChange, disabled = false } ) => {
	const modes = [
		{ id: 'test', label: __( 'Test Mode', 'gutena-forms' ) },
		{ id: 'live', label: __( 'Live Mode', 'gutena-forms' ) },
	];

	return (
		<div className="gutena-forms__payment-mode">
			<p className="gutena-forms__payment-mode__label">{ __( 'Payment Mode', 'gutena-forms' ) }</p>
			<div className="gutena-forms__payment-mode__controls">
				<div className="gutena-forms__payment-mode__options">
					{ modes.map( ( mode ) => (
						<button
							key={ mode.id }
							type="button"
							className={ `gutena-forms__payment-mode__option${ value === mode.id ? ' is-active' : '' }` }
							onClick={ () => ! disabled && onChange?.( mode.id ) }
							disabled={ disabled }
						>
							<span>{ mode.label }</span>
							<span className="gutena-forms__payment-mode__radio" aria-hidden="true" />
						</button>
					) ) }
				</div>
				<p className="gutena-forms__payment-mode__help">
					{ __( 'Test mode allows you to process payments without real charges. Switch to Live mode for actual transactions.', 'gutena-forms' ) }
				</p>
			</div>
		</div>
	);
};

export default PaymentModeToggle;
