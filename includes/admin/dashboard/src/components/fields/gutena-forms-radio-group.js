import { useEffect, useState } from '@wordpress/element';

const GutenaFormsRadioGroup = ( {
	id,
	desc,
	label,
	value,
	options,
	onChange,
	disabled = false,
	variant = 'segmented',
} ) => {
	const [ selectedValue, setSelectedValue ] = useState( '' );

	useEffect( () => {
		setSelectedValue( value );
	}, [ value ] );

	const handleChange = ( newValue ) => {
		setSelectedValue( newValue );
		if ( onChange ) {
			onChange( newValue );
		}
	};

	if ( 'card' === variant && options ) {
		return (
			<div className="gutena-forms__radio-group-control is-card-variant">
				{ label && (
					<label htmlFor={ id } className="gutena-forms__field-label">
						{ label }
					</label>
				) }

				<div className="gutena-forms__option-cards">
					{ Object.keys( options ).map( ( optionKey ) => {
						const isSelected = selectedValue === optionKey;

						return (
							<button
								key={ optionKey }
								type="button"
								className={ `gutena-forms__option-cards__option${
									isSelected ? ' is-selected' : ''
								}` }
								onClick={ () => handleChange( optionKey ) }
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

				{ desc && (
					<p className="gutena-forms__field-description">{ desc }</p>
				) }
			</div>
		);
	}

	return (
		<div className={ 'gutena-forms__radio-group-control' }>
			{ label && (
				<label htmlFor={ id } className={ 'gutena-forms__field-label' }>
					{ label }
				</label>
			) }

			{ options && (
				<div className={ 'gutena-forms__radio-group-options' }>
					{ Object.keys( options ).map( ( optionKey, index ) => {
						return (
							<div
								key={ index }
								className={ 'gutena-forms__radio-option' }
							>
								<input
									className={ 'gutena-forms__radio-option-input' }
									type="radio"
									id={ optionKey }
									name={ id }
									value={ optionKey }
									checked={ selectedValue === optionKey }
									onChange={ ( e ) => handleChange( e.target.value ) }
									disabled={ disabled }
								/>
								<label
									className={ 'gutena-forms__radio-option-label' }
									htmlFor={ optionKey }
								>
									{ options[ optionKey ] }
								</label>
							</div>
						);
					} ) }
				</div>
			) }

			{ desc && (
				<p className={ 'gutena-forms__field-description' }>{ desc }</p>
			) }
		</div>
	);
};

export default GutenaFormsRadioGroup;