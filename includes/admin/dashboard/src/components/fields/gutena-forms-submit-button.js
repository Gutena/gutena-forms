import { Button } from '@wordpress/components';

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

export default GutenaFormsSubmitButton;
