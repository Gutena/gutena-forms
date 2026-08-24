/**
 * Import Gutena Forms settings screen.
 *
 * @since 2.1.0
 * @package Gutena Forms
 */

import { __, sprintf } from '@wordpress/i18n';
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

const PRICING_URL =
	'https://gutenaforms.com/pricing/?utm_source=admin_dashboard&utm_medium=website&utm_campaign=free_plugin';

const PRO_FIELD_LABELS = {
	date: __( 'Date Field', 'gutena-forms' ),
	time: __( 'Time Field', 'gutena-forms' ),
	rating: __( 'Rating Field', 'gutena-forms' ),
	phone: __( 'Phone Field', 'gutena-forms' ),
	country: __( 'Country Field', 'gutena-forms' ),
	state: __( 'State Field', 'gutena-forms' ),
	file: __( 'File Upload Field', 'gutena-forms' ),
	'file-upload': __( 'File Upload Field', 'gutena-forms' ),
	url: __( 'URL Field', 'gutena-forms' ),
	hidden: __( 'Hidden Field', 'gutena-forms' ),
	password: __( 'Password Field', 'gutena-forms' ),
};

const PRO_BLOCK_NAME_TOKENS = [
	'date',
	'time',
	'rating',
	'phone',
	'country',
	'state',
	'file-upload',
	'url',
	'hidden',
	'password',
];

const PRO_SCHEMA_FIELD_TYPES = [
	...PRO_BLOCK_NAME_TOKENS,
	'file',
];

const hasGutenaFormsPro =
	typeof gutenaFormsAdmin !== 'undefined' &&
	!! gutenaFormsAdmin.hasPro;

const getProFieldTypeLabel = ( token ) =>
	PRO_FIELD_LABELS[ token ] || token;

const formatProFieldEntry = ( token, titleName ) => {
	const typeLabel = getProFieldTypeLabel( token );
	const title = titleName || typeLabel;

	return {
		type: typeLabel,
		title,
		key: `${ typeLabel }:${ title }`,
	};
};

const addProFieldEntry = ( found, token, titleName ) => {
	const entry = formatProFieldEntry( token, titleName );
	found[ entry.key ] = entry;
};

const getProFieldTokenFromBlockName = ( blockName ) =>
	PRO_BLOCK_NAME_TOKENS.find(
		( token ) =>
			`gutena/${ token }-field` === blockName ||
			`gutena/${ token }-field-group` === blockName
	);

const collectProFieldsFromBlockTree = ( block, found ) => {
	if ( ! block || 'object' !== typeof block ) {
		return;
	}

	const blockName = (
		( block.blockName || '' ) + ''
	).toLowerCase();
	const token = getProFieldTokenFromBlockName( blockName );

	if ( token ) {
		const titleName =
			block?.attrs?.fieldName || block?.attrs?.nameAttr || '';
		addProFieldEntry( found, token, titleName );
	}

	if ( Array.isArray( block.innerBlocks ) ) {
		block.innerBlocks.forEach( ( inner ) =>
			collectProFieldsFromBlockTree( inner, found )
		);
	}
};

const detectProFieldsInForm = ( form ) => {
	const found = {};

	if ( form?.block && 'object' === typeof form.block ) {
		collectProFieldsFromBlockTree( form.block, found );
	}

	if ( 'string' === typeof form?.content ) {
		PRO_BLOCK_NAME_TOKENS.forEach( ( token ) => {
			if (
				form.content.includes( `"gutena/${ token }-field"` ) ||
				form.content.includes( `gutena/${ token }-field-group` )
			) {
				addProFieldEntry( found, token, '' );
			}
		} );
	}

	const schemaFields = form?.schema?.form_fields;
	if ( schemaFields && 'object' === typeof schemaFields ) {
		Object.entries( schemaFields ).forEach( ( [ nameAttr, field ] ) => {
			const type = ( field?.fieldType || '' ).toLowerCase();
			if ( PRO_SCHEMA_FIELD_TYPES.includes( type ) ) {
				const titleName =
					field?.fieldName ||
					nameAttr.replace( /_/g, ' ' );
				addProFieldEntry( found, type, titleName );
			}
		} );
	}

	return Object.values( found );
};

const detectProFields = ( payload ) => {
	if ( ! payload || ! Array.isArray( payload.forms ) ) {
		return [];
	}

	const warnings = [];
	payload.forms.forEach( ( form, index ) => {
		if ( ! form || 'object' !== typeof form ) {
			return;
		}

		const fields = detectProFieldsInForm( form );
		if ( fields.length === 0 ) {
			return;
		}

		warnings.push( {
			form:
				( 'string' === typeof form.title && form.title ) ||
				( 'string' === typeof form?.block?.attrs?.formName &&
					form.block.attrs.formName ) ||
				sprintf(
					/* translators: %d: form index */
					__( 'Form #%d', 'gutena-forms' ),
					index + 1
				),
			fields,
		} );
	} );

	return warnings;
};

const GutenaFormsImport = () => {
	const fileInputRef = useRef( null );
	const [ fileName, setFileName ] = useState( '' );
	const [ payload, setPayload ] = useState( null );
	const [ error, setError ] = useState( '' );
	const [ importing, setImporting ] = useState( false );
	const [ pending, setPending ] = useState( null );

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
		setPending( null );

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

				const warnings = hasGutenaFormsPro
					? []
					: detectProFields( parsed );

				if ( warnings.length > 0 ) {
					setPending( { payload: parsed, warnings } );
					setPayload( null );
				} else {
					setPayload( parsed );
					setPending( null );
				}
				setError( '' );
			} catch ( e ) {
				setPayload( null );
				setPending( null );
				setError( INVALID_FILE_MESSAGE );
			}
		};
		reader.onerror = () => {
			setPayload( null );
			setPending( null );
			setError( INVALID_FILE_MESSAGE );
		};
		reader.readAsText( file );
	};

	const handleProceedImport = () => {
		if ( ! pending?.payload ) {
			return;
		}
		const payloadToImport = pending.payload;
		setPayload( payloadToImport );
		setPending( null );
		runImport( payloadToImport );
	};

	const handleCancelImport = () => {
		setPending( null );
		setPayload( null );
		setFileName( '' );
		setError( '' );
		resetFileInput();
	};

	const runImport = ( payloadToImport ) => {
		if ( ! payloadToImport ) {
			setError( INVALID_FILE_MESSAGE );
			return;
		}

		setImporting( true );
		gutenaFormsImportForms( payloadToImport )
			.then( ( response ) => {
				toast.success(
					response?.message ||
						__( 'Forms imported successfully.', 'gutena-forms' )
				);

				if (
					Array.isArray( response?.pro_fields ) &&
					response.pro_fields.length > 0
				) {
					toast.warning(
						__(
							'Some imported forms contained Gutena Forms Pro fields. These fields were removed during the import.',
							'gutena-forms'
						),
						{ autoClose: false }
					);
				}

				setFileName( '' );
				setPayload( null );
				setPending( null );
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

	const handleImport = () => {
		if ( ! fileName || ! payload || error ) {
			setError( INVALID_FILE_MESSAGE );
			return;
		}
		runImport( payload );
	};

	const canImport = Boolean( fileName ) && Boolean( payload ) && ! error && ! importing;

	const proFields = pending
		? [
				...new Map(
					pending.warnings
						.flatMap( ( warning ) => warning.fields )
						.map( ( field ) => [ field.key, field ] )
				).values(),
		  ]
		: [];

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

			{ pending && (
				<div
					className="gutena-forms__import-pro-warning"
					role="alertdialog"
					aria-label={ __(
						'Gutena Forms Pro fields detected',
						'gutena-forms'
					) }
				>
					<div className="gutena-forms__import-pro-warning-body">
						<div className="gutena-forms__import-pro-warning-header">
							<span className="gutena-forms__import-error-icon">
								<AlertError />
							</span>
							<p>
								{ __(
									'Below fields required Gutena Forms Pro!',
									'gutena-forms'
								) }
							</p>
						</div>

						{ proFields.length > 0 && (
							<div className="gutena-forms__import-pro-warning-fields">
								{ proFields.map( ( field ) => (
									<span
										className="gutena-forms__import-pro-warning-field-tag"
										key={ field.key }
									>
										{ field.type }: { field.title }
									</span>
								) ) }
							</div>
						) }
					</div>

					<div className="gutena-forms__import-pro-warning-actions">
						<div className="gutena-forms__import-pro-warning-actions-left">
							<Button
								className="gutena-forms__import-pro-warning-upgrade"
								href={ PRICING_URL }
								target="_blank"
								rel="noopener noreferrer"
							>
								{ __( 'Upgrade to Pro', 'gutena-forms' ) }
							</Button>
							<button
								type="button"
								className="gutena-forms__import-pro-warning-proceed"
								onClick={ handleProceedImport }
								disabled={ importing }
							>
								{ importing
									? __( 'Importing…', 'gutena-forms' )
									: __( 'Import Anyway', 'gutena-forms' ) }
							</button>
						</div>
						<button
							type="button"
							className="gutena-forms__import-pro-warning-cancel"
							onClick={ handleCancelImport }
							disabled={ importing }
						>
							{ __( 'Cancel', 'gutena-forms' ) }
						</button>
					</div>
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
