import { Button } from '@wordpress/components';

const GutenaFormsSubmitButton = ( { label, onClick, type, disabled } ) => {

	return (
		<div className={ `gutena-forms__submit-button ${ type }` }>
			<Button
				isPrimary
				onClick={ onClick }
				disabled={ disabled }
			>
				{ label }
			</Button>
		</div>
	);
};

export default GutenaFormsSubmitButton;
