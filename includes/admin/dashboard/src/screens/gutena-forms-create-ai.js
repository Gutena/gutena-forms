/**
 * AI Form Creation screen — lets the user describe a form in plain text
 * and generates a Gutena Forms draft via the middle server.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

import { __ } from '@wordpress/i18n';
import { useState, useRef } from '@wordpress/element';
import { useNavigate } from 'react-router';
import { toast } from 'react-toastify';
import PromptChip from '../components/ai/PromptChip';
import { gutenaFormsCreateFromAI } from '../utils/form-templates-api';

const MAX_PROMPT_LENGTH = 350;

// Small inline icons (kept inline to avoid external asset dependencies).
const ArrowLeftIcon = () => (
	<svg width="13" height="10" viewBox="0 0 13 10" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path d="M5 1L1 5L5 9M1 5H12" stroke="#757575" strokeWidth="1.2" strokeLinecap="round" strokeLinejoin="round" />
	</svg>
);

const SparkleIcon = () => (
	<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path d="M7 1L8.4 5.2L12.6 6.6L8.4 8L7 12.2L5.6 8L1.4 6.6L5.6 5.2L7 1Z" fill="#fff" />
		<path d="M11 1L11.5 2.5L13 3L11.5 3.5L11 5L10.5 3.5L9 3L10.5 2.5L11 1Z" fill="#fff" opacity="0.8" />
	</svg>
);

const CategoryIcon = () => (
	<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
		<rect x="1" y="1" width="4" height="4" rx="1" stroke="#757575" strokeWidth="0.8" fill="none" />
		<rect x="7" y="1" width="4" height="4" rx="1" stroke="#757575" strokeWidth="0.8" fill="none" />
		<rect x="1" y="7" width="4" height="4" rx="1" stroke="#757575" strokeWidth="0.8" fill="none" />
		<rect x="7" y="7" width="4" height="4" rx="1" stroke="#757575" strokeWidth="0.8" fill="none" />
	</svg>
);

const GutenaFormsCreateAi = () => {
	const navigate = useNavigate();
	const [ prompt, setPrompt ] = useState( '' );
	const [ generating, setGenerating ] = useState( false );
	const [ error, setError ] = useState( '' );
	const [ isInvalid, setIsInvalid ] = useState( false );
	const inFlight = useRef( false );

	// Localized prompt chips: { label: promptString, ... }
	const promptChips = ( typeof gutenaFormsAdmin !== 'undefined' && gutenaFormsAdmin.aiPromptChips ) ? gutenaFormsAdmin.aiPromptChips : {};
	const categoryChips = ( typeof gutenaFormsAdmin !== 'undefined' && gutenaFormsAdmin.aiCategoryChips ) ? gutenaFormsAdmin.aiCategoryChips : [];

	const handleChange = ( value ) => {
		setPrompt( value );
		// Clear any error styling as soon as the user edits the prompt.
		if ( error || isInvalid ) {
			setError( '' );
			setIsInvalid( false );
		}
	};

	const handleChipClick = ( chipPrompt ) => {
		setPrompt( chipPrompt );
		if ( error || isInvalid ) {
			setError( '' );
			setIsInvalid( false );
		}
	};

	const handleGenerate = async () => {
		// Block empty prompts and duplicate requests.
		if ( ! prompt.trim() || inFlight.current ) {
			return;
		}

		inFlight.current = true;
		setGenerating( true );
		setError( '' );
		setIsInvalid( false );

		try {
			const data = await gutenaFormsCreateFromAI( prompt.trim() );
			toast.success( __( 'Form generated successfully.', 'gutena-forms' ) );
			if ( data.edit_url ) {
				window.location.href = data.edit_url;
			}
		} catch ( err ) {
			setGenerating( false );
			inFlight.current = false;

			const code = err?.code || '';
			const message = err?.message || __( 'Failed to generate form.', 'gutena-forms' );

			// Backend emits namespaced codes (gutena_forms_ai_*); match the suffix.
			if ( 'limit_reached' === code || 'gutena_forms_ai_limit_reached' === code ) {
				setError( message );
			} else if ( 'invalid_prompt' === code || 'gutena_forms_ai_invalid_prompt' === code ) {
				setError( message );
				setIsInvalid( true );
			} else {
				setError( message );
			}
		}
	};

	const handleBack = () => {
		if ( inFlight.current ) {
			return;
		}
		navigate( '/create' );
	};

	const charCount = prompt.length;
	const canGenerate = prompt.trim().length > 0 && ! generating;

	return (
		<div className="gutena-forms__create-ai">
			<div className="gutena-forms__create-ai-header">
				<h1>{ __( 'Create Form with AI', 'gutena-forms' ) }</h1>
				<p>{ __( 'Automatically generate smart, customizable forms using AI.', 'gutena-forms' ) }</p>
			</div>

			<div className={ `gutena-forms__create-ai-box${ isInvalid ? ' is-invalid' : '' }` }>
				<textarea
					className="gutena-forms__create-ai-textarea"
					placeholder={ __( 'Feedback form to ask customers: “How would you rate our product and what should we improve?”', 'gutena-forms' ) }
					maxLength={ MAX_PROMPT_LENGTH }
					value={ prompt }
					onChange={ ( e ) => handleChange( e.target.value ) }
					disabled={ generating }
					rows={ 5 }
				/>

				<div className="gutena-forms__create-ai-box-footer">
					<div className="gutena-forms__create-ai-categories">
						{ categoryChips.map( ( label ) => (
							<span key={ label } className="gutena-forms__create-ai-category-chip">
								<CategoryIcon />
								{ label }
							</span>
						) ) }
					</div>
					<div className="gutena-forms__create-ai-counter">
						{ charCount }/{ MAX_PROMPT_LENGTH }
					</div>
				</div>
			</div>

			{ error && (
				<div className="gutena-forms__create-ai-error">
					{ error }
				</div>
			) }

			<div className="gutena-forms__create-ai-prompts">
				<div className="gutena-forms__create-ai-prompts-label">
					{ __( 'Or create using our Prompts:', 'gutena-forms' ) }
				</div>

				<div className="gutena-forms__create-ai-chips">
					{ Object.keys( promptChips ).map( ( label ) => (
						<PromptChip
							key={ label }
							label={ label }
							onClick={ () => handleChipClick( promptChips[ label ] ) }
						/>
					) ) }
				</div>
			</div>

			<div className="gutena-forms__create-ai-actions">
				<button
					type="button"
					className="gutena-forms__create-ai-back"
					onClick={ handleBack }
					disabled={ generating }
				>
					<ArrowLeftIcon />
					{ __( 'Back', 'gutena-forms' ) }
				</button>

				<button
					type="button"
					className="gutena-forms__create-ai-generate"
					onClick={ handleGenerate }
					disabled={ ! canGenerate }
				>
					{ generating ? __( 'Generating…', 'gutena-forms' ) : __( 'Generate Form', 'gutena-forms' ) }
					{ ! generating && <SparkleIcon /> }
				</button>
			</div>
		</div>
	);
};

export default GutenaFormsCreateAi;
