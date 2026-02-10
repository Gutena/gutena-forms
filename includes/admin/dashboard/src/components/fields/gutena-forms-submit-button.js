import { Button } from '@wordpress/components';
import { addFilter } from '@wordpress/hooks';

const GutenaFormsSubmitButton = ( { label, onClick } ) => {

	return (
		<div className={ 'gutena-forms__submit-button' }>
			<Button
				isPrimary
				onClick={ onClick }
			>
				{ label }
			</Button>
		</div>
	);
};

addFilter(
	'gutenaFormsPro.core.components',
	'gutena-forms-free',
	( components ) => {

		components['GutenaFormsSubmitButton'] = GutenaFormsSubmitButton;

		return components;
	}
);

export default GutenaFormsSubmitButton;
