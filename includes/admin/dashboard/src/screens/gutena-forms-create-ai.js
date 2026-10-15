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
import { Button } from '@wordpress/components';
import { toast } from 'react-toastify';
import PromptChip from '../components/ai/PromptChip';
import { gutenaFormsCreateFromAI } from '../utils/form-templates-api';

const MAX_PROMPT_LENGTH = 350;

const GutenaFormsCreateAi = () => {
	const navigate = useNavigate();
	const [ prompt, setPrompt ] = useState( '' );
	const [ generating, setGenerating ] = useState( false );
	const [ error, setError ] = useState( '' );
	const [ isInvalid, setIsInvalid ] = useState( false );
	const inFlight = useRef( false );

	// Localized prompt chips: { label: promptString, ... }
	const promptChips = ( typeof gutenaFormsAdmin !== 'undefined' && gutenaFormsAdmin.aiPromptChips ) ? gutenaFormsAdmin.aiPromptChips : {};

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

			if ( 'limit_reached' === code ) {
				setError( message );
			} else if ( 'invalid_prompt' === code ) {
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

			<div className="gutena-forms__create-ai-body">
				<textarea
					className={ `gutena-forms__create-ai-textarea${ isInvalid ? ' is-invalid' : '' }` }
					placeholder={ __( 'Describe your form.', 'gutena-forms' ) }
					maxLength={ MAX_PROMPT_LENGTH }
					value={ prompt }
					onChange={ ( e ) => handleChange( e.target.value ) }
					disabled={ generating }
					rows={ 6 }
				/>

				<div className="gutena-forms__create-ai-counter">
					{ charCount } / { MAX_PROMPT_LENGTH }
				</div>

				{ error && (
					<div className="gutena-forms__create-ai-error">
						{ error }
					</div>
				) }

				<div className="gutena-forms__create-ai-prompts-label">
					{ __( 'or create using our Prompts:', 'gutena-forms' ) }
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

				<div className="gutena-forms__create-ai-actions">
					<Button
						variant="primary"
						onClick={ handleGenerate }
						disabled={ ! canGenerate }
						isBusy={ generating }
						showTooltip={ false }
					>
						{ generating ? __( 'Generating…', 'gutena-forms' ) : __( 'Generate Form', 'gutena-forms' ) }
					</Button>

					<Button
						variant="secondary"
						onClick={ handleBack }
						disabled={ generating }
					>
						{ __( 'Back', 'gutena-forms' ) }
					</Button>
				</div>
			</div>
		</div>
	);
};

export default GutenaFormsCreateAi;
