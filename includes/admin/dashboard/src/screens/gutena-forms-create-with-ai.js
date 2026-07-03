import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import { Button } from '@wordpress/components';
import { useNavigate } from 'react-router';
import { toast } from 'react-toastify';
import { activateLeftMenu } from '../utils/functions';
import { gutenaFormsAiGenerateForm } from '../api';
import Previous from '../icons/previous';

const AI_PROMPT_MAX_LENGTH = 300;

const AI_PROMPT_SUGGESTIONS = [
	{
		id: 'newsletter',
		label: __( 'Newsletter Form', 'gutena-forms' ),
		prompt: __(
			'A newsletter signup form with first name, email address, and an optional checkbox to agree to receive marketing emails.',
			'gutena-forms'
		),
	},
	{
		id: 'consultation',
		label: __( 'Consultation Request Form', 'gutena-forms' ),
		prompt: __(
			'A consultation request form with name, email, phone number, preferred date, service type, and a message describing what they need help with.',
			'gutena-forms'
		),
	},
	{
		id: 'reservation',
		label: __( 'Reservation Form', 'gutena-forms' ),
		prompt: __(
			'A table reservation form with name, email, phone, date, time, number of guests, and special requests or dietary notes.',
			'gutena-forms'
		),
	},
	{
		id: 'appointment',
		label: __( 'Appointment Booking Form', 'gutena-forms' ),
		prompt: __(
			'An appointment booking form with full name, email, phone, preferred date, preferred time, service type, and additional notes.',
			'gutena-forms'
		),
	},
	{
		id: 'feedback',
		label: __( 'Customer Feedback Form', 'gutena-forms' ),
		prompt: __(
			'A customer feedback form with name, email, rating, satisfaction level, and a textarea for detailed feedback and improvement suggestions.',
			'gutena-forms'
		),
	},
	{
		id: 'donation',
		label: __( 'Donation Request Form', 'gutena-forms' ),
		prompt: __(
			'A donation form with donor name, email, phone, donation amount, payment preference, and an optional message or dedication.',
			'gutena-forms'
		),
	},
];

const GutenaFormsCreateWithAi = () => {
	const navigate = useNavigate();
	const [ prompt, setPrompt ] = useState( '' );
	const [ activeSuggestion, setActiveSuggestion ] = useState( '' );
	const [ isGenerating, setIsGenerating ] = useState( false );

	useEffect( () => {
		activateLeftMenu( 3 );
	}, [] );

	const handleBack = () => {
		navigate( '/settings/add-new-form' );
	};

	const handlePromptSuggestion = ( suggestion ) => {
		setPrompt( suggestion.prompt );
		setActiveSuggestion( suggestion.id );
	};

	const handleGenerate = async () => {
		if ( ! prompt.trim() ) {
			toast.error( __( 'Please describe the form you want to create.', 'gutena-forms' ) );
			return;
		}

		if ( isGenerating ) {
			return;
		}

		setIsGenerating( true );

		try {
			const response = await gutenaFormsAiGenerateForm( {
				prompt: prompt.trim(),
			} );

			window.location.href = response.edit_url;
		} catch ( error ) {
			const data = error?.data || {};
			const errorCode = data.code || error?.code || '';
			const errorMessage =
				data.message ||
				error?.message ||
				__( 'Could not generate the form. Please try again.', 'gutena-forms' );

			if ( 'no_middleware_credits' === errorCode ) {
				const upgradeUrl = data.upgrade_url || '';
				toast.error(
					upgradeUrl
						? `${ errorMessage } ${ __( 'Upgrade:', 'gutena-forms' ) } ${ upgradeUrl }`
						: errorMessage
				);
			} else if ( 'gutena_forms_ai_invalid_markup' === errorCode ) {
				toast.error( __( 'AI returned invalid form. Try again.', 'gutena-forms' ) );
			} else {
				toast.error( errorMessage );
			}
		} finally {
			setIsGenerating( false );
		}
	};

	return (
		<div className="gutena-forms__add-new-form-screen gutena-forms__create-with-ai-screen">
			<div className="gutena-forms__add-new-form-header">
				<h2>{ __( 'Create Form with AI', 'gutena-forms' ) }</h2>
				<p>{ __( 'Automatically generate smart, customizable forms using AI.', 'gutena-forms' ) }</p>
			</div>

			<div className="gutena-forms__ai-prompt-wrapper">
				<div className="gutena-forms__ai-prompt-box">
					<textarea
						className="gutena-forms__ai-prompt-input"
						value={ prompt }
						onChange={ ( event ) => {
							const value = event.target.value;
							if ( value.length <= AI_PROMPT_MAX_LENGTH ) {
								setPrompt( value );
								const matchedSuggestion = AI_PROMPT_SUGGESTIONS.find(
									( suggestion ) => suggestion.prompt === value
								);
								setActiveSuggestion( matchedSuggestion ? matchedSuggestion.id : '' );
							}
						} }
						placeholder={ __( 'Feedback form to ask customers: "How would you rate our product and what should we improve?"', 'gutena-forms' ) }
						rows={ 5 }
					/>
					<div className="gutena-forms__ai-prompt-footer">
						<div className="gutena-forms__ai-suggestion-tags">
							{ AI_PROMPT_SUGGESTIONS.map( ( suggestion ) => (
								<button
									key={ suggestion.id }
									type="button"
									className={ `gutena-forms__ai-suggestion-tag${ activeSuggestion === suggestion.id ? ' is-active' : '' }` }
									onClick={ () => handlePromptSuggestion( suggestion ) }
								>
									{ suggestion.label }
								</button>
							) ) }
						</div>
						<span className="gutena-forms__ai-char-count">
							{ prompt.length }/{ AI_PROMPT_MAX_LENGTH }
						</span>
					</div>
				</div>
			</div>

			<div className="gutena-forms__add-new-form-actions">
				<Button
					className="gutena-forms__back-button"
					variant="secondary"
					onClick={ handleBack }
					disabled={ isGenerating }
				>
					<Previous />
					{ __( 'Back', 'gutena-forms' ) }
				</Button>
				<Button
					className="gutena-forms__generate-ai-button"
					variant="primary"
					onClick={ handleGenerate }
					disabled={ isGenerating }
					isBusy={ isGenerating }
				>
					{ isGenerating
						? __( 'Generating…', 'gutena-forms' )
						: __( 'Generate Form', 'gutena-forms' ) }
				</Button>
			</div>
		</div>
	);
};

export default GutenaFormsCreateWithAi;
