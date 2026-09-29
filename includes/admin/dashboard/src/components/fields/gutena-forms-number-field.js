import { useEffect, useState } from '@wordpress/element';

const GutenaFormsNumberField = ( {
	id,
	onChange,
	value,
	desc,
	label,
	min,
	max,
	step,
	disabled = false,
} ) => {
	const [ numberValue, setNumberValue ] = useState( value || 0 );

	useEffect( () => {
		setNumberValue( value || 0 );
	}, [ value ] );

	const handleChange = ( event ) => {
		const newValue = event.target.value;
		setNumberValue( newValue );
		if ( onChange ) {
			onChange( newValue );
		}
	};

	return (
		<div className={ 'gutena-forms__number-control gutena-forms-notification-field' }>
			{ label && (
				<label
					className={ 'gutena-forms-notification-field__label' }
					htmlFor={ id }
				>
					{ label }
				</label>
			) }

			<div className={ 'gutena-forms-notification-field__row' }>
				<input
					id={ id }
					className={ 'gutena-forms-notification-field__input' }
					type="number"
					value={ numberValue }
					onChange={ handleChange }
					min={ min }
					max={ max }
					step={ step }
					disabled={ disabled }
				/>
			</div>

			{ desc && (
				<p className={ 'gutena-forms-notification-field__help' }>{ desc }</p>
			) }
		</div>
	);
};

export default GutenaFormsNumberField;
