/**
 * Import Gutena Forms settings screen.
 *
 * @since 2.1.0
 * @package Gutena Forms
 */

import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { useRef, useState } from '@wordpress/element';
import Download from '../icons/download';
import AlertError from '../icons/alert-error';
import { gutenaFormsImportForms } from '../api';
import { toast } from 'react-toastify';

const INVALID_FILE_MESSAGE = __(
	'Unable to import the file. Please upload a valid Gutena Forms export (.json) file.',
	'gutena-forms'
);

const GutenaFormsImport = () => {
	const fileInputRef = useRef( null );
	const [ fileName, setFileName ] = useState( '' );
	const [ payload, setPayload ] = useState( null );
	const [ error, setError ] = useState( '' );
	const [ importing, setImporting ] = useState( false );

	const openFilePicker = () => {
		if ( fileInputRef.current ) {
			fileInputRef.current.click();
		}
	};

	const resetFileInput = () => {
		if ( fileInputRef.current ) {
			fileInputRef.current.value = '';
		}
	};

	const handleFileChange = ( event ) => {
		const file = event.target.files?.[ 0 ];
		setError( '' );
		setPayload( null );

		if ( ! file ) {
			setFileName( '' );
			return;
		}

		const isJson =
			file.name.toLowerCase().endsWith( '.json' ) ||
			'application/json' === file.type;

		setFileName( file.name );

		if ( ! isJson ) {
			setError( INVALID_FILE_MESSAGE );
			return;
		}

		const reader = new window.FileReader();
		reader.onload = () => {
			try {
				const parsed = JSON.parse( String( reader.result || '' ) );
				const pluginOk =
					parsed &&
					( 'gutena-forms' === parsed.plugin ||
						'gutena-forms' === parsed.generator );
				const formsOk =
					parsed &&
					Array.isArray( parsed.forms ) &&
					parsed.forms.length > 0;

				if ( ! pluginOk || ! formsOk ) {
					setPayload( null );
					setError( INVALID_FILE_MESSAGE );
					return;
				}

				setPayload( parsed );
				setError( '' );
			} catch ( e ) {
				setPayload( null );
				setError( INVALID_FILE_MESSAGE );
			}
		};
		reader.onerror = () => {
			setPayload( null );
			setError( INVALID_FILE_MESSAGE );
		};
		reader.readAsText( file );
	};

	const handleImport = () => {
		if ( ! fileName || ! payload || error ) {
			setError( INVALID_FILE_MESSAGE );
			return;
		}

		setImporting( true );
		gutenaFormsImportForms( payload )
			.then( ( response ) => {
				toast.success(
					response?.message ||
						__( 'Forms imported successfully.', 'gutena-forms' )
				);
				setFileName( '' );
				setPayload( null );
				setError( '' );
				resetFileInput();
			} )
			.catch( ( err ) => {
				const message =
					err?.message ||
					INVALID_FILE_MESSAGE;
				setError( message );
				toast.error( message );
			} )
			.finally( () => {
				setImporting( false );
			} );
	};

	const canImport = Boolean( fileName ) && Boolean( payload ) && ! error && ! importing;

	return (
		<div className="gutena-forms__import-export gutena-forms__import">
			<div className="gutena-forms__import-file-field">
				<label className="gutena-forms__import-export-label">
					{ __( 'Choose Your File', 'gutena-forms' ) }
				</label>
				<div className="gutena-forms__import-file-row">
					<div className="gutena-forms__import-file-name">
						{ fileName ||
							__( 'No file chosen', 'gutena-forms' ) }
					</div>
					<Button
						className="gutena-forms__import-choose-button"
						onClick={ openFilePicker }
						disabled={ importing }
					>
						{ __( 'Choose a File', 'gutena-forms' ) }
					</Button>
					<input
						ref={ fileInputRef }
						type="file"
						accept=".json,application/json"
						className="gutena-forms__import-file-input"
						onChange={ handleFileChange }
					/>
				</div>
			</div>

			{ error && (
				<div className="gutena-forms__import-error" role="alert">
					<span className="gutena-forms__import-error-icon">
						<AlertError />
					</span>
					<span>{ error }</span>
				</div>
			) }

			<Button
				className={ `gutena-forms__primary-button gutena-forms__import-export-action ${
					! canImport ? 'is-disabled' : ''
				}` }
				disabled={ ! canImport }
				isBusy={ importing }
				onClick={ handleImport }
			>
				<Download />
				{ importing
					? __( 'Importing…', 'gutena-forms' )
					: __( 'Import', 'gutena-forms' ) }
			</Button>
		</div>
	);
};

export default GutenaFormsImport;
