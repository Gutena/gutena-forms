import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { toast } from 'react-toastify';

const CopyIcon = () => (
	<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
		<path d="M9.91663 7.81683V9.56683C9.91663 11.9002 8.98329 12.8335 6.64996 12.8335H4.43329C2.09996 12.8335 1.16663 11.9002 1.16663 9.56683V7.35016C1.16663 5.01683 2.09996 4.0835 4.43329 4.0835H6.18329" stroke="#6E7376" strokeLinecap="round" strokeLinejoin="round"/>
		<path d="M9.91668 7.81683H8.05002C6.65002 7.81683 6.18335 7.35016 6.18335 5.95016V4.0835L9.91668 7.81683Z" stroke="#6E7376" strokeLinecap="round" strokeLinejoin="round"/>
		<path d="M6.76672 1.1665H9.10006" stroke="#6E7376" strokeWidth="0.875" strokeLinecap="round" strokeLinejoin="round"/>
		<path d="M4.08337 2.9165C4.08337 1.94817 4.86504 1.1665 5.83337 1.1665H7.36171" stroke="#6E7376" strokeLinecap="round" strokeLinejoin="round"/>
		<path d="M12.8334 4.6665V8.27734C12.8334 9.1815 12.0984 9.9165 11.1942 9.9165" stroke="#6E7376" strokeLinecap="round" strokeLinejoin="round"/>
		<path d="M12.8334 4.6665H11.0834C9.77087 4.6665 9.33337 4.229 9.33337 2.9165V1.1665L12.8334 4.6665Z" stroke="#6E7376" strokeLinecap="round" strokeLinejoin="round"/>
	</svg>
);

const GutenaFormsReadonlyCopyField = ( { label, id, desc, value } ) => {
	const inputId = `gutena-forms-readonly-${ id }`;
	const [ fieldValue, setFieldValue ] = useState( '' );

	useEffect( () => {
		setFieldValue( value || '' );
	}, [ value ] );

	const handleCopy = async () => {
		if ( ! fieldValue ) {
			return;
		}

		try {
			await navigator.clipboard.writeText( fieldValue );
			toast.success( __( 'Copied to clipboard!', 'gutena-forms' ) );
		} catch {
			const input = document.getElementById( inputId );
			if ( input ) {
				input.select();
				try {
					document.execCommand( 'copy' );
					toast.success( __( 'Copied to clipboard!', 'gutena-forms' ) );
				} catch {
					toast.error( __( 'Failed to copy.', 'gutena-forms' ) );
				}
			}
		}
	};

	return (
		<div className={ 'gutena-forms__readonly-copy-field' }>
			<label className={ 'gutena-forms__readonly-copy-field-label' } htmlFor={ inputId }>
				{ label }
			</label>
			<div className={ 'gutena-forms__readonly-copy-field-row' }>
				<input
					id={ inputId }
					className={ 'gutena-forms__readonly-copy-field-input' }
					type="text"
					value={ fieldValue }
					readOnly
				/>
				<button
					type="button"
					className={ 'gutena-forms__readonly-copy-field-button' }
					onClick={ handleCopy }
				>
					<CopyIcon />
					<span>{ __( 'Copy', 'gutena-forms' ) }</span>
				</button>
			</div>
			{ desc && (
				<p
					className={ 'gutena-forms__field-description' }
					dangerouslySetInnerHTML={ { __html: desc } }
				/>
			) }
		</div>
	);
};

export default GutenaFormsReadonlyCopyField;
