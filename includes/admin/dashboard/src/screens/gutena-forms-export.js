/**
 * Export Gutena Forms & Entries settings screen.
 *
 * @since 2.1.0
 * @package Gutena Forms
 */

import { __ } from '@wordpress/i18n';
import { Button, CheckboxControl } from '@wordpress/components';
import { useEffect, useRef, useState } from '@wordpress/element';
import ExportIcon from '../icons/export';
import {
	gutenaFormsFetchAllForms,
	gutenaFormsFetchFormFields,
	gutenaFormsExportEntries,
	gutenaFormsExportForms,
	gutenaFormsDownloadBase64File,
} from '../api';
import { toast } from 'react-toastify';

const EXPORT_FORMAT_IDS = [ 'csv', 'xlsx', 'pdf' ];

const GutenaFormsExport = () => {
	const exportFormats = [
		{ id: 'csv', label: __( 'Export in CSV', 'gutena-forms' ) },
		{
			id: 'xlsx',
			label: __( 'Export in Microsoft Excel (.xlsx)', 'gutena-forms' ),
		},
		{ id: 'pdf', label: __( 'Export in PDF', 'gutena-forms' ) },
	];

	const [ forms, setForms ] = useState( [] );
	const [ entriesFormId, setEntriesFormId ] = useState( '' );
	const [ entryFields, setEntryFields ] = useState( [] );
	const [ fieldsLoading, setFieldsLoading ] = useState( false );
	const [ selectedFields, setSelectedFields ] = useState( [] );
	const [ exportFormat, setExportFormat ] = useState( EXPORT_FORMAT_IDS[ 1 ] );
	const [ selectedFormIds, setSelectedFormIds ] = useState( [] );
	const [ formsDropdownOpen, setFormsDropdownOpen ] = useState( false );
	const [ entriesDropdownOpen, setEntriesDropdownOpen ] = useState( false );
	const [ exportingEntries, setExportingEntries ] = useState( false );
	const [ exportingForms, setExportingForms ] = useState( false );
	const formsDropdownRef = useRef( null );
	const entriesDropdownRef = useRef( null );

	useEffect( () => {
		gutenaFormsFetchAllForms()
			.then( ( response ) => {
				setForms( Array.isArray( response ) ? response : [] );
			} )
			.catch( () => {
				setForms( [] );
			} );
	}, [] );

	useEffect( () => {
		if ( ! entriesFormId ) {
			setEntryFields( [] );
			setSelectedFields( [] );
			setFieldsLoading( false );
			return;
		}

		let cancelled = false;
		setFieldsLoading( true );
		setEntryFields( [] );
		setSelectedFields( [] );

		gutenaFormsFetchFormFields( entriesFormId )
			.then( ( fields ) => {
				if ( cancelled ) {
					return;
				}
				const safeFields = Array.isArray( fields ) ? fields : [];
				setEntryFields( safeFields );
				setSelectedFields( safeFields.map( ( field ) => field.id ) );
				setFieldsLoading( false );
			} )
			.catch( () => {
				if ( cancelled ) {
					return;
				}
				setEntryFields( [] );
				setSelectedFields( [] );
				setFieldsLoading( false );
				toast.error(
					__( 'Failed to load form fields.', 'gutena-forms' )
				);
			} );

		return () => {
			cancelled = true;
		};
	}, [ entriesFormId ] );

	useEffect( () => {
		const handleOutsideClick = ( event ) => {
			if (
				formsDropdownRef.current &&
				! formsDropdownRef.current.contains( event.target )
			) {
				setFormsDropdownOpen( false );
			}
			if (
				entriesDropdownRef.current &&
				! entriesDropdownRef.current.contains( event.target )
			) {
				setEntriesDropdownOpen( false );
			}
		};

		document.addEventListener( 'mousedown', handleOutsideClick );
		return () =>
			document.removeEventListener( 'mousedown', handleOutsideClick );
	}, [] );

	const allFormIds = forms.map( ( form ) => String( form.id ) );
	const selectAllForms =
		allFormIds.length > 0 &&
		allFormIds.every( ( id ) => selectedFormIds.includes( id ) );

	const selectedEntriesForm = forms.find(
		( form ) => String( form.id ) === String( entriesFormId )
	);

	const canDownloadEntries =
		Boolean( entriesFormId ) &&
		selectedFields.length > 0 &&
		Boolean( exportFormat ) &&
		! fieldsLoading &&
		! exportingEntries;

	const canExportForms =
		selectedFormIds.length > 0 && ! exportingForms;

	const toggleField = ( fieldId ) => {
		setSelectedFields( ( prev ) =>
			prev.includes( fieldId )
				? prev.filter( ( id ) => id !== fieldId )
				: [ ...prev, fieldId ]
		);
	};

	const toggleSelectAllForms = () => {
		if ( selectAllForms ) {
			setSelectedFormIds( [] );
			return;
		}
		setSelectedFormIds( allFormIds );
	};

	const toggleFormSelection = ( formId ) => {
		const id = String( formId );
		setSelectedFormIds( ( prev ) =>
			prev.includes( id )
				? prev.filter( ( item ) => item !== id )
				: [ ...prev, id ]
		);
	};

	const removeSelectedForm = ( formId ) => {
		setSelectedFormIds( ( prev ) =>
			prev.filter( ( id ) => id !== String( formId ) )
		);
	};

	const handleDownloadEntries = () => {
		if ( ! canDownloadEntries ) {
			return;
		}

		setExportingEntries( true );
		gutenaFormsExportEntries( {
			form_id: entriesFormId,
			fields: selectedFields,
			format: exportFormat,
		} )
			.then( ( response ) => {
				gutenaFormsDownloadBase64File(
					response.file,
					response.filename,
					response.mime
				);
				toast.success(
					__( 'Entries exported successfully.', 'gutena-forms' )
				);
			} )
			.catch( ( error ) => {
				toast.error(
					error?.message ||
						__( 'Failed to export entries.', 'gutena-forms' )
				);
			} )
			.finally( () => {
				setExportingEntries( false );
			} );
	};

	const handleExportForms = () => {
		if ( ! canExportForms ) {
			return;
		}

		setExportingForms( true );
		gutenaFormsExportForms( selectedFormIds )
			.then( ( response ) => {
				gutenaFormsDownloadBase64File(
					response.file,
					response.filename,
					response.mime
				);
				toast.success(
					__( 'Forms exported successfully.', 'gutena-forms' )
				);
			} )
			.catch( ( error ) => {
				toast.error(
					error?.message ||
						__( 'Failed to export forms.', 'gutena-forms' )
				);
			} )
			.finally( () => {
				setExportingForms( false );
			} );
	};

	return (
		<div className="gutena-forms__import-export gutena-forms__export">
			{ /* Export Entries */ }
			<section className="gutena-forms__export-section">
				<div className="gutena-forms__import-export-section-heading">
					<h3>{ __( 'Export Entries', 'gutena-forms' ) }</h3>
					<p>
						{ __(
							'Select a form and the fields you want to export. Then download your entries in CSV, XLSX, or PDF format.',
							'gutena-forms'
						) }
					</p>
				</div>

				<div
					className="gutena-forms__export-dropdown"
					ref={ entriesDropdownRef }
				>
					<button
						type="button"
						className="gutena-forms__export-dropdown-trigger"
						onClick={ () =>
							setEntriesDropdownOpen( ( open ) => ! open )
						}
					>
						<span>
							{ selectedEntriesForm
								? selectedEntriesForm.title
								: __( 'Select a Form', 'gutena-forms' ) }
						</span>
						<span
							className="gutena-forms__export-dropdown-chevron"
							aria-hidden="true"
						/>
					</button>
					{ entriesDropdownOpen && (
						<ul className="gutena-forms__export-dropdown-menu">
							{ forms.length === 0 && (
								<li className="gutena-forms__export-dropdown-empty">
									{ __(
										'No forms available.',
										'gutena-forms'
									) }
								</li>
							) }
							{ forms.map( ( form ) => (
								<li key={ form.id }>
									<button
										type="button"
										onClick={ () => {
											setEntriesFormId(
												String( form.id )
											);
											setEntriesDropdownOpen( false );
										} }
									>
										{ form.title }
									</button>
								</li>
							) ) }
						</ul>
					) }
				</div>

				{ entriesFormId && (
					<>
						<div className="gutena-forms__export-options-block">
							<h4>
								{ __( 'Form Fields', 'gutena-forms' ) }
							</h4>
							<div className="gutena-forms__export-checkbox-list gutena-forms__export-fields-grid">
								{ fieldsLoading && (
									<p className="gutena-forms__export-fields-status">
										{ __(
											'Loading fields…',
											'gutena-forms'
										) }
									</p>
								) }
								{ ! fieldsLoading &&
									entryFields.length === 0 && (
										<p className="gutena-forms__export-fields-status">
											{ __(
												'No fields found for this form.',
												'gutena-forms'
											) }
										</p>
									) }
								{ ! fieldsLoading &&
									entryFields.map( ( field ) => (
										<CheckboxControl
											key={ field.id }
											label={ field.label }
											checked={ selectedFields.includes(
												field.id
											) }
											onChange={ () =>
												toggleField( field.id )
											}
										/>
									) ) }
							</div>
						</div>

						<div className="gutena-forms__export-options-block">
							<h4>
								{ __( 'Export Options', 'gutena-forms' ) }
							</h4>
							<div className="gutena-forms__export-checkbox-list">
								{ exportFormats.map( ( format ) => (
									<CheckboxControl
										key={ format.id }
										label={ format.label }
										checked={
											exportFormat === format.id
										}
										onChange={ ( checked ) => {
											if ( checked ) {
												setExportFormat( format.id );
											}
										} }
									/>
								) ) }
							</div>
						</div>
					</>
				) }

				<Button
					className={ `gutena-forms__primary-button gutena-forms__import-export-action ${
						! canDownloadEntries ? 'is-disabled' : ''
					}` }
					disabled={ ! canDownloadEntries }
					isBusy={ exportingEntries }
					onClick={ handleDownloadEntries }
				>
					{ exportingEntries
						? __( 'Exporting…', 'gutena-forms' )
						: __( 'Download Export File', 'gutena-forms' ) }
				</Button>
			</section>

			<div className="gutena-forms__export-divider" />

			{ /* Export Forms */ }
			<section className="gutena-forms__export-section">
				<div className="gutena-forms__import-export-section-heading">
					<h3>{ __( 'Export Forms', 'gutena-forms' ) }</h3>
					<p>
						{ __(
							'Select one or more Gutena forms to export. Clicking the Export button generates a JSON file that can be imported into another Gutena Forms site for migration or backup.',
							'gutena-forms'
						) }
					</p>
				</div>

				<div className="gutena-forms__export-select-all">
					<CheckboxControl
						label={ __( 'Select All Forms', 'gutena-forms' ) }
						checked={ selectAllForms }
						onChange={ toggleSelectAllForms }
						disabled={ forms.length === 0 }
					/>
				</div>

				<div
					className="gutena-forms__export-dropdown gutena-forms__export-multi"
					ref={ formsDropdownRef }
				>
					<button
						type="button"
						className="gutena-forms__export-dropdown-trigger"
						onClick={ () =>
							setFormsDropdownOpen( ( open ) => ! open )
						}
					>
						<div className="gutena-forms__export-tags">
							{ selectedFormIds.length === 0 && (
								<span className="gutena-forms__export-placeholder">
									{ __( 'Select forms', 'gutena-forms' ) }
								</span>
							) }
							{ selectedFormIds.map( ( id ) => {
								const form = forms.find(
									( item ) => String( item.id ) === id
								);
								if ( ! form ) {
									return null;
								}
								return (
									<span
										key={ id }
										className="gutena-forms__export-tag"
									>
										{ form.title }
										<button
											type="button"
											className="gutena-forms__export-tag-remove"
											aria-label={ __(
												'Remove',
												'gutena-forms'
											) }
											onClick={ ( event ) => {
												event.stopPropagation();
												removeSelectedForm( id );
											} }
										>
											×
										</button>
									</span>
								);
							} ) }
						</div>
						<span
							className="gutena-forms__export-dropdown-chevron"
							aria-hidden="true"
						/>
					</button>
					{ formsDropdownOpen && (
						<ul className="gutena-forms__export-dropdown-menu">
							{ forms.length === 0 && (
								<li className="gutena-forms__export-dropdown-empty">
									{ __(
										'No forms available.',
										'gutena-forms'
									) }
								</li>
							) }
							{ forms.map( ( form ) => {
								const id = String( form.id );
								const checked =
									selectedFormIds.includes( id );
								return (
									<li key={ id }>
										<label className="gutena-forms__export-dropdown-option">
											<input
												type="checkbox"
												checked={ checked }
												onChange={ () =>
													toggleFormSelection( id )
												}
											/>
											<span>{ form.title }</span>
										</label>
									</li>
								);
							} ) }
						</ul>
					) }
				</div>

				<Button
					className={ `gutena-forms__primary-button gutena-forms__import-export-action ${
						! canExportForms ? 'is-disabled' : ''
					}` }
					disabled={ ! canExportForms }
					isBusy={ exportingForms }
					onClick={ handleExportForms }
				>
					<ExportIcon />
					{ exportingForms
						? __( 'Exporting…', 'gutena-forms' )
						: __( 'Export', 'gutena-forms' ) }
				</Button>
			</section>
		</div>
	);
};

export default GutenaFormsExport;
