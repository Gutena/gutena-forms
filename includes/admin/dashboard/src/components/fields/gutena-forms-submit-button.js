import { Button } from '@wordpress/components';

const GutenaFormsSubmitButton = ( { label, onClick, type } ) => {

	return (
		<div className={ `gutena-forms__submit-button ${ type }` }>
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
