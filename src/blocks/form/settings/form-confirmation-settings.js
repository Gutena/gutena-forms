import { useState, useCallback, useRef, useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	PanelBody,
	ToggleControl,
	Button,
	Modal,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import ConfirmationMessageEditor from './form-confirmation-editor';

const MERGE_TAGS = [
	'{site_name}',
	'{site_url}',
	'{submission_date}',
	'{form_title}',
	'{user_email}',
	'{user_name}',
	'{First Name}',
	'{Last Name}',
	'{Email}',
	'{all_data}',
];

const VALUE_KEYS = [
	'type',
	'successMessage',
	'errorMessage',
	'afterSubmit',
	'redirectType',
	'redirectPage',
	'redirectUrl',
];

/**
 * Gutena-styled radio control for the confirmation modal.
 */
const ConfirmationRadio = ( { label, checked, onChange, name } ) => (
	<label className="gf-confirmation-radio">
		<input
			type="radio"
			name={ name }
			checked={ checked }
			onChange={ () => onChange( ! checked ) }
			className="gf-confirmation-radio__input"
		/>
		<span className="gf-confirmation-radio__dot" />
		<span className="gf-confirmation-radio__label">{ label }</span>
	</label>
);

/**
 * Form Confirmation configuration modal.
 */
const FormConfirmationModal = ( { initialValues, onSave, onClose } ) => {
	const [ values, setValues ] = useState( { ...initialValues } );
	const [ useGlobalDefaults, setUseGlobalDefaults ] = useState(
		initialValues.defaultSettings ?? false
	);

	const pages = useSelect( ( select ) => {
		try {
			return (
				select( coreStore ).getEntityRecords( 'postType', 'page', {
					per_page: -1,
					status: 'publish',
					orderby: 'title',
					order: 'asc',
				} ) || []
			);
		} catch ( error ) {
			return [];
		}
	}, [] );

	const pageOptions = useMemo( () => {
		const options = [
			{
				label: __( 'Select Page', 'gutena-forms' ),
				value: '0',
			},
		];
		pages.forEach( ( page ) => {
			const title =
				page.title?.rendered || page.title?.raw || `#${ page.id }`;
			options.push( {
				label: title,
				value: String( page.id ),
			} );
		} );
		return options;
	}, [ pages ] );

	const handleChange = useCallback( ( key, value ) => {
		setValues( ( prev ) => ( { ...prev, [ key ]: value } ) );
	}, [] );

	const type = values.type || 'success';
	const redirectType = values.redirectType || 'page';

	return (
		<Modal
			title={ __( 'Form Confirmation', 'gutena-forms' ) }
			onRequestClose={ onClose }
			className="gf-confirmation-modal"
			__experimentalShowHeader
			style={ {
				width: '700px',
				maxWidth: '700px',
				display: 'flex',
				flexDirection: 'column',
				maxHeight: '85vh',
				borderRadius: '10px',
			} }
		>
			<div
				className="gf-confirmation-modal__fields"
				style={ { flex: '1', overflowY: 'auto' } }
			>

				<div className="gf-confirmation-field">
					<ToggleControl
						label={ __(
							'Use global form confirmation settings',
							'gutena-forms'
						) }
						help={
							useGlobalDefaults
								? __(
										'This form will inherit the global Form Confirmation settings.',
										'gutena-forms'
								  )
								: __(
										'Override the global settings for this form.',
										'gutena-forms'
								  )
						}
						checked={ useGlobalDefaults }
						onChange={ setUseGlobalDefaults }
						__nextHasNoMarginBottom
					/>
				</div>

				{ ! useGlobalDefaults && (
				<>
				<div className="gf-confirmation-field">
					<label className="gf-confirmation-field__label">
						{ __( 'Confirmation Type', 'gutena-forms' ) }
					</label>
					<div className="gf-confirmation-field__row">
						<ConfirmationRadio
							name="gf-confirm-type"
							label={ __( 'Success Message', 'gutena-forms' ) }
							checked={ 'success' === type }
							onChange={ () =>
								handleChange( 'type', 'success' )
							}
						/>
						<ConfirmationRadio
							name="gf-confirm-type"
							label={ __( 'Redirect', 'gutena-forms' ) }
							checked={ 'redirect' === type }
							onChange={ () =>
								handleChange( 'type', 'redirect' )
							}
						/>
					</div>
				</div>

				{ 'success' === type && (
					<>
						<div className="gf-confirmation-field">
							<ConfirmationMessageEditor
								label={ __(
									'Confirmation Message',
									'gutena-forms'
								) }
								value={ values.successMessage }
								onChange={ ( value ) =>
									handleChange( 'successMessage', value )
								}
								tags={ MERGE_TAGS }
								rows={ 7 }
							/>
						</div>

						<div className="gf-confirmation-field">
							<ConfirmationMessageEditor
								label={ __( 'Error Message', 'gutena-forms' ) }
								value={ values.errorMessage }
								onChange={ ( value ) =>
									handleChange( 'errorMessage', value )
								}
								tags={ MERGE_TAGS }
								rows={ 4 }
							/>
						</div>

						<div className="gf-confirmation-field">
							<label className="gf-confirmation-field__label">
								{ __(
									'After Form Submission',
									'gutena-forms'
								) }
							</label>
							<div className="gf-confirmation-field__row">
								<ConfirmationRadio
									name="gf-after-submit"
									label={ __( 'Hide Form', 'gutena-forms' ) }
									checked={
										'hide' ===
										( values.afterSubmit || 'hide' )
									}
									onChange={ () =>
										handleChange( 'afterSubmit', 'hide' )
									}
								/>
								<ConfirmationRadio
									name="gf-after-submit"
									label={ __( 'Reset Form', 'gutena-forms' ) }
									checked={ 'reset' === values.afterSubmit }
									onChange={ () =>
										handleChange( 'afterSubmit', 'reset' )
									}
								/>
							</div>
						</div>
					</>
				) }

				{ 'redirect' === type && (
					<>
						<div className="gf-confirmation-field">
							<label className="gf-confirmation-field__label">
								{ __( 'Redirect To', 'gutena-forms' ) }
							</label>
							<div className="gf-confirmation-field__row">
								<ConfirmationRadio
									name="gf-redirect-type"
									label={ __( 'Page', 'gutena-forms' ) }
									checked={ 'page' === redirectType }
									onChange={ () =>
										handleChange( 'redirectType', 'page' )
									}
								/>
								<ConfirmationRadio
									name="gf-redirect-type"
									label={ __(
										'Custom URL',
										'gutena-forms'
									) }
									checked={ 'url' === redirectType }
									onChange={ () =>
										handleChange( 'redirectType', 'url' )
									}
								/>
							</div>
						</div>

						{ 'page' === redirectType && (
							<div className="gf-confirmation-field">
								<label className="gf-confirmation-field__label">
									{ __( 'Redirect to Page', 'gutena-forms' ) }
								</label>
								<SelectControl
									value={ String(
										values.redirectPage || 0
									) }
									options={ pageOptions }
									onChange={ ( value ) =>
										handleChange(
											'redirectPage',
											parseInt( value, 10 ) || 0
										)
									}
									__nextHasNoMarginBottom
								/>
							</div>
						) }

						{ 'url' === redirectType && (
							<div className="gf-confirmation-field">
								<label className="gf-confirmation-field__label">
									{ __(
										'Custom URL',
										'gutena-forms'
									) }
								</label>
								<TextControl
									type="url"
									value={ values.redirectUrl || '' }
									placeholder="https://example.com/thank-you"
									onChange={ ( value ) =>
										handleChange( 'redirectUrl', value )
									}
									__nextHasNoMarginBottom
								/>
							</div>
						) }
					</>
				) }
				</>
				) }
			</div>

			<div className="gf-confirmation-modal__footer">
				<Button variant="secondary" onClick={ onClose }>
					{ __( 'Cancel', 'gutena-forms' ) }
				</Button>
				<Button
					variant="primary"
					onClick={ () =>
						onSave( {
							...values,
							defaultSettings: useGlobalDefaults,
						} )
					}
				>
					{ __( 'Save', 'gutena-forms' ) }
				</Button>
			</div>
		</Modal>
	);
};

/**
 * Form Confirmation inspector panel.
 */
const FormConfirmationSettings = ( { formConfirmation, setAttributes } ) => {
	const [ isModalOpen, setIsModalOpen ] = useState( false );
	const wasEnabledOnOpen = useRef( false );

	const enabled = formConfirmation?.enabled ?? false;

	const globalDefaults =
		'undefined' !== typeof gutenaFormsBlock &&
		gutenaFormsBlock?.form_confirmation_defaults
			? gutenaFormsBlock.form_confirmation_defaults
			: {};

	// Values pre-filled into the modal: per-form customized values or
	// inherited global defaults.
	const modalInitialValues = useMemo( () => {
		const base = {
			type: 'success',
			successMessage: '',
			errorMessage: '',
			afterSubmit: 'hide',
			redirectType: 'page',
			redirectPage: 0,
			redirectUrl: '',
			...globalDefaults,
		};

		if (
			formConfirmation &&
			false === formConfirmation.defaultSettings
		) {
			VALUE_KEYS.forEach( ( key ) => {
				if (
					'undefined' !== typeof formConfirmation[ key ] &&
					null !== formConfirmation[ key ]
				) {
					base[ key ] = formConfirmation[ key ];
				}
			} );
		}

		return base;
	}, [ formConfirmation, globalDefaults ] );

	const handleToggle = useCallback(
		( value ) => {
			if ( value ) {
				// First enable: open the modal. The toggle stays ON only when
				// the modal is saved; cancelling reverts it to OFF.
				wasEnabledOnOpen.current = false;
				setAttributes( {
					formConfirmation: { ...formConfirmation, enabled: true },
				} );
				setIsModalOpen( true );
			} else {
				setAttributes( {
					formConfirmation: { ...formConfirmation, enabled: false },
				} );
			}
		},
		[ formConfirmation, setAttributes ]
	);

	const handleConfigure = useCallback( () => {
		wasEnabledOnOpen.current = true;
		setIsModalOpen( true );
	}, [] );

	const handleModalClose = useCallback( () => {
		if ( ! wasEnabledOnOpen.current ) {
			// Closed before the first save: the toggle returns to OFF.
			setAttributes( {
				formConfirmation: { ...formConfirmation, enabled: false },
			} );
		}
		setIsModalOpen( false );
	}, [ formConfirmation, setAttributes ] );

	const handleModalSave = useCallback(
		( values ) => {
			setAttributes( {
				formConfirmation: {
					...values,
					enabled: true,
				},
			} );
			setIsModalOpen( false );
		},
		[ setAttributes ]
	);

	return (
		<PanelBody title={ __( 'Form Confirmation', 'gutena-forms' ) } initialOpen={ true }>
			<ToggleControl
				label={ __( 'Enable Form Confirmation', 'gutena-forms' ) }
				help={
					enabled
						? __(
								'Toggle to use the default form confirmation behavior.',
								'gutena-forms'
						  )
						: __(
								'Toggle to configure the confirmation message or redirect after form submission.',
								'gutena-forms'
						  )
				}
				checked={ enabled }
				onChange={ handleToggle }
			/>
			{ enabled && (
				<Button
					variant="secondary"
					onClick={ handleConfigure }
					className="gf-configure-button"
				>
					{ __( 'Configure', 'gutena-forms' ) }
				</Button>
			) }
			{ isModalOpen && (
				<FormConfirmationModal
					initialValues={ modalInitialValues }
					onSave={ handleModalSave }
					onClose={ handleModalClose }
				/>
			) }
		</PanelBody>
	);
};

export default FormConfirmationSettings;
