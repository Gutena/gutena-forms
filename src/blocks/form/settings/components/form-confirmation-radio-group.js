const FormConfirmationRadioGroup = ( {
	id,
	label,
	value,
	options = {},
	onChange,
	disabled = false,
	variant = 'inline',
} ) => {
	if ( 'card' === variant ) {
		return (
			<div className="gutena-forms-form-confirmation-radio-group is-card">
				{ label && (
					<p className="gutena-forms-form-confirmation-radio-group__label">
						{ label }
					</p>
				) }
				<div className="gutena-forms__option-cards">
					{ Object.keys( options ).map( ( optionKey ) => {
						const isSelected = value === optionKey;

						return (
							<button
								key={ optionKey }
								type="button"
								className={ `gutena-forms__option-cards__option${
									isSelected ? ' is-selected' : ''
								}` }
								onClick={ () => onChange( optionKey ) }
								disabled={ disabled }
								aria-pressed={ isSelected }
							>
								<span>{ options[ optionKey ] }</span>
								<span
									className={ `gutena-forms__option-cards__indicator${
										isSelected ? ' is-selected' : ''
									}` }
									aria-hidden="true"
								/>
							</button>
						);
					} ) }
				</div>
			</div>
		);
	}

	return (
		<div className="gutena-forms-form-confirmation-radio-group is-inline">
			{ label && (
				<p className="gutena-forms-form-confirmation-radio-group__label">
					{ label }
				</p>
			) }
			<div className="gutena-forms-form-confirmation-radio-group__options">
				{ Object.keys( options ).map( ( optionKey ) => (
					<label
						key={ optionKey }
						className="gutena-forms-form-confirmation-radio-group__option"
					>
						<input
							type="radio"
							name={ id }
							value={ optionKey }
							checked={ value === optionKey }
							onChange={ () => onChange( optionKey ) }
							disabled={ disabled }
						/>
						<span>{ options[ optionKey ] }</span>
					</label>
				) ) }
			</div>
		</div>
	);
};

export default FormConfirmationRadioGroup;
