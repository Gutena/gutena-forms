/**
 * PromptChip — a small clickable chip that fills the AI prompt textarea
 * with a pre-written prompt string.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

const PromptChip = ( { label, onClick } ) => {
	return (
		<button
			type="button"
			className="gutena-forms__ai-prompt-chip"
			onClick={ onClick }
		>
			{ label }
		</button>
	);
};

export default PromptChip;
