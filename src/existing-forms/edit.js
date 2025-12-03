import { useEffect, useState, useCallback, useMemo } from '@wordpress/element';
import { useBlockProps, InspectorControls, InnerBlocks } from '@wordpress/block-editor';
import { PanelBody, SelectControl, Placeholder, Spinner, Button, Notice } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';

// Cache for forms data to avoid repeated API calls
const formsCache = new Map();
const formContentCache = new Map();

const Edit = ( { attributes, setAttributes, clientId } ) => {
	const { formId } = attributes;
	const [ state, setState ] = useState( {
		forms: [],
		formContent: null,
		loading: true,
		isEditing: false,
		error: null,
		saving: false
	} );

	const { replaceInnerBlocks } = useDispatch( 'core/block-editor' );
	const { getBlocks } = useSelect( ( select ) => select( 'core/block-editor' ) );

	// Memoized form options to prevent unnecessary re-renders
	const formOptions = useMemo( () => [
		{ label: __( 'Select a form', 'gutena-forms' ), value: '' },
		...state.forms.map( form => ( {
			label: form.title,
			value: form.id
		} ) )
	], [ state.forms ] );

	// Optimized form fetching with caching
	const fetchForms = useCallback( async () => {
		if ( formsCache.has( 'forms' ) ) {
			setState( prev => ( { ...prev, forms: formsCache.get( 'forms' ), loading: false } ) );
			return;
		}

		try {
			const response = await fetch( '/wp-json/gutena-forms/v1/forms', {
				headers: { 'X-WP-Nonce': wpApiSettings.nonce }
			} );

			if ( response.ok ) {
				const formsData = await response.json();
				formsCache.set( 'forms', formsData );
				setState( prev => ( { ...prev, forms: formsData, loading: false } ) );
			} else {
				throw new Error( 'Failed to fetch forms' );
			}
		} catch ( error ) {
			console.error( 'Error fetching forms:', error );
			setState( prev => ( {
				...prev,
				loading: false,
				error: __( 'Failed to load forms. Please try again.', 'gutena-forms' )
			} ) );
		}
	}, [] );

	// Optimized form content fetching with caching
	const fetchFormContent = useCallback( async ( id ) => {
		if ( formContentCache.has( id ) ) {
			setState( prev => ( { ...prev, formContent: formContentCache.get( id ) } ) );
			return;
		}

		try {
			const response = await fetch( `/wp-json/gutena-forms/v1/forms/${id}`, {
				headers: { 'X-WP-Nonce': wpApiSettings.nonce }
			} );

			if ( response.ok ) {
				const formData = await response.json();
				formContentCache.set( id, formData );
				setState( prev => ( { ...prev, formContent: formData } ) );
			} else {
				throw new Error( 'Failed to fetch form content' );
			}
		} catch ( error ) {
			console.error( 'Error fetching form content:', error );
			setState( prev => ( {
				...prev,
				error: __( 'Failed to load form content. Please try again.', 'gutena-forms' )
			} ) );
		}
	}, [] );

	// Load forms on mount
	useEffect( () => {
		fetchForms();
	}, [ fetchForms ] );

	// Load form content when formId changes
	useEffect( () => {
		if ( formId ) {
			fetchFormContent( formId );
		} else {
			setState( prev => ( { ...prev, formContent: null } ) );
		}
	}, [ formId, fetchFormContent ] );

	// Load blocks only when entering edit mode
	const handleEditToggle = useCallback( () => {
		const newEditingState = ! state.isEditing;

		if ( newEditingState && state.formContent?.content ) {
			try {
				const blocks = wp.blocks.parse( state.formContent.content );

				// Find the main gutena/forms block (not gutena/existing-forms)
				let formBlock = blocks.find( block => block.name === 'gutena/forms' );

				// If we found a gutena/forms block, use its inner blocks
				if ( formBlock?.innerBlocks && formBlock.innerBlocks.length > 0 ) {
					// Filter out any existing-forms blocks from inner blocks
					const cleanInnerBlocks = formBlock.innerBlocks.filter( block => block.name !== 'gutena/existing-forms' );
					replaceInnerBlocks( clientId, cleanInnerBlocks );
				} else {
					// Check if there's a gutena/existing-forms block and extract its content
					const existingFormsBlock = blocks.find( block => block.name === 'gutena/existing-forms' );
					if ( existingFormsBlock ) {
						// This shouldn't happen in a properly saved form, so start with empty state
						replaceInnerBlocks( clientId, [] );
					} else {
						// If no gutena/forms block found, try to use the blocks directly
						// This handles cases where the content might be just the inner blocks
						const validBlocks = blocks.filter( block =>
							block.name &&
							block.name !== 'gutena/existing-forms' &&
							block.name !== 'core/freeform' &&
							( block.name !== 'core/paragraph' ||
								( block.name === 'core/paragraph' && block.attributes?.content ) )
						);

						if ( validBlocks.length > 0 ) {
							replaceInnerBlocks( clientId, validBlocks );
						} else {
							// If no valid blocks found, start with empty state
							replaceInnerBlocks( clientId, [] );
						}
					}
				}
			} catch ( error ) {
				console.error( 'Error parsing form content:', error );
				setState( prev => ( {
					...prev,
					error: __( 'Failed to load form for editing. Please try again.', 'gutena-forms' )
				} ) );
				return;
			}
		}

		setState( prev => ( { ...prev, isEditing: newEditingState } ) );
	}, [ state.isEditing, state.formContent, clientId, replaceInnerBlocks ] );

	// Optimized save function with better error handling
	const handleSaveForm = useCallback( async () => {
		if ( ! formId || state.saving ) return;

		setState( prev => ( { ...prev, saving: true, error: null } ) );

		try {
			const currentBlocks = getBlocks( clientId );

			// Filter out any existing-forms blocks from the current blocks to prevent duplication
			const filteredBlocks = currentBlocks.filter( block => block.name !== 'gutena/existing-forms' );

			// Create a gutena/forms wrapper block with the original attributes
			const formBlock = {
				name: 'gutena/forms',
				attributes: state.formContent?.attributes || {},
				innerBlocks: filteredBlocks
			};

			// Serialize the blocks to HTML
			const serializedContent = wp.blocks.serialize( [ formBlock ] );

			const response = await fetch( `/wp-json/gutena-forms/v1/forms/${formId}/update`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': wpApiSettings.nonce
				},
				body: JSON.stringify( { content: serializedContent } )
			} );

			if ( response.ok ) {
				const updatedForm = await response.json();

				// Update cache
				formContentCache.set( formId, updatedForm );

				setState( prev => ( {
					...prev,
					formContent: updatedForm,
					isEditing: false,
					saving: false
				} ) );

				wp.data.dispatch( 'core/notices' ).createNotice( 'success',
					__( 'Form updated successfully!', 'gutena-forms' ),
					{ isDismissible: true }
				);
			} else {
				throw new Error( 'Failed to update form' );
			}
		} catch ( error ) {
			console.error( 'Error saving form:', error );
			setState( prev => ( {
				...prev,
				saving: false,
				error: __( 'Failed to update form. Please try again.', 'gutena-forms' )
			} ) );
		}
	}, [ formId, state.saving, state.formContent, clientId, getBlocks ] );

	// Memoized form preview component
	const FormPreview = useMemo( () => {
		if ( ! state.formContent?.content ) {
			return (
				<div style={ { padding: '20px', textAlign: 'center', color: '#666' } }>
					<p>{ __( 'No form content available.', 'gutena-forms' ) }</p>
				</div>
			);
		}

		// Parse and display the actual form content
		try {
			const blocks = wp.blocks.parse( state.formContent.content );
			const formBlock = blocks.find( block => block.name === 'gutena/forms' );

			if ( formBlock?.innerBlocks && formBlock.innerBlocks.length > 0 ) {
				// Render the actual form content
				return (
					<div style={ {
						background: 'white',
						padding: '16px',
						border: '1px solid #ddd',
						borderRadius: '4px',
						textAlign: 'left'
					} }>
						<div style={ {
							fontSize: '14px',
							color: '#666',
							marginBottom: '12px',
							fontWeight: '500'
						} }>
							{ __( 'Form Preview:', 'gutena-forms' ) }
						</div>
						<div dangerouslySetInnerHTML={ {
							__html: wp.blocks.serialize( formBlock.innerBlocks )
						} } />
					</div>
				);
			}
		} catch ( error ) {
			console.error( 'Error parsing form content for preview:', error );
		}

		// Fallback preview
		return (
			<div style={ {
				background: 'white',
				padding: '16px',
				border: '1px solid #ddd',
				borderRadius: '4px',
				textAlign: 'left'
			} }>
				<div style={ {
					fontSize: '14px',
					color: '#666',
					marginBottom: '12px',
					fontWeight: '500'
				} }>
					{ __( 'Form Preview:', 'gutena-forms' ) }
				</div>
				<div style={ {
					fontSize: '13px',
					color: '#888',
					lineHeight: '1.4'
				} }>
					{ __( 'This form contains form fields and will be displayed on the frontend. Click "Edit Form" to modify the structure.', 'gutena-forms' ) }
				</div>
			</div>
		);
	}, [ state.formContent ] );

	const blockProps = useBlockProps();

	// Early returns for different states
	if ( state.loading ) {
		return (
			<div { ...blockProps }>
				<Placeholder>
					<Spinner />
					<p>{ __( 'Loading forms...', 'gutena-forms' ) }</p>
				</Placeholder>
			</div>
		);
	}

	if ( state.error ) {
		return (
			<div { ...blockProps }>
				<Notice status="error" isDismissible={ false }>
					{ state.error }
				</Notice>
			</div>
		);
	}

	if ( ! formId ) {
		return (
			<div { ...blockProps }>
				<InspectorControls>
					<PanelBody title={ __( 'Form Settings', 'gutena-forms' ) }>
						<SelectControl
							label={ __( 'Select Form', 'gutena-forms' ) }
							value={ formId }
							options={ formOptions }
							onChange={ ( value ) => setAttributes( { formId: parseInt( value ) } ) }
						/>
					</PanelBody>
				</InspectorControls>
				<Placeholder>
					<p>{ __( 'Please select a form from the sidebar.', 'gutena-forms' ) }</p>
				</Placeholder>
			</div>
		);
	}

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Form Settings', 'gutena-forms' ) }>
					<SelectControl
						label={ __( 'Select Form', 'gutena-forms' ) }
						value={ formId }
						options={ formOptions }
						onChange={ ( value ) => setAttributes( { formId: parseInt( value ) } ) }
					/>
					{ formId && (
						<div style={ { marginTop: '16px' } }>
							<Button
								variant={ state.isEditing ? 'primary' : 'secondary' }
								onClick={ handleEditToggle }
								disabled={ state.saving }
							>
								{ state.isEditing ? __( 'Cancel Editing', 'gutena-forms' ) : __( 'Edit Form', 'gutena-forms' ) }
							</Button>
							{ state.isEditing && (
								<Button
									variant="primary"
									onClick={ handleSaveForm }
									disabled={ state.saving }
									style={ { marginLeft: '8px' } }
								>
									{ state.saving ? __( 'Saving...', 'gutena-forms' ) : __( 'Save Changes', 'gutena-forms' ) }
								</Button>
							) }
						</div>
					) }
				</PanelBody>
			</InspectorControls>

			<div className="gutena-forms-existing-form">
				{ formId && (
					<div style={ { marginBottom: '16px', padding: '12px', background: '#f0f0f1', borderRadius: '4px' } }>
						<strong>{ __( 'Selected Form:', 'gutena-forms' ) }</strong> { state.formContent?.title || __( 'Loading...', 'gutena-forms' ) }
						{ state.isEditing && (
							<p style={ { margin: '8px 0 0 0', fontSize: '13px', color: '#666' } }>
								{ __( 'You are now editing this form. Make your changes and click "Save Changes" to update the form.', 'gutena-forms' ) }
							</p>
						) }
					</div>
				) }

				{ state.isEditing ? (
					<InnerBlocks
						allowedBlocks={ [
							'core/columns',
							'core/group',
							'core/image',
							'core/paragraph',
							'gutena/field-group',
							'core/buttons',
							'core/heading',
							'core/spacer',
							'core/separator',
							'core/list',
							'core/quote',
							'core/table',
							'core/code',
							'core/preformatted',
							'core/verse',
							'core/pullquote',
							'core/audio',
							'core/video',
							'core/file',
							'core/gallery',
							'core/embed',
							'core/shortcode',
							'core/html',
							'gutena/form-field',
							'gutena/form-error-msg',
							'gutena/form-confirm-msg',
						] }
						templateLock={ false }
					/>
				) : (
					<div style={ { padding: '20px', textAlign: 'center', background: '#f9f9f9', border: '1px dashed #ccc', borderRadius: '4px' } }>
						<div style={ { marginBottom: '16px' } }>
							<strong>{ __( 'Form Preview', 'gutena-forms' ) }</strong>
						</div>
						<p style={ { marginBottom: '16px' } }>
							{ __( 'This form will be displayed on the frontend. Click "Edit Form" to modify the form structure.', 'gutena-forms' ) }
						</p>
						{ FormPreview }
					</div>
				) }
			</div>
		</div>
	);
};

export default Edit;
