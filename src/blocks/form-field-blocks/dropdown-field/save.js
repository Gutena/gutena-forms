import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { gfIsEmpty } from '../../../shared/utils/helper';

function getFieldClasses( { isRequired, autocomplete } ) {
	const parts = [ 'gutena-forms-field', 'select-field' ];
	if ( isRequired ) {
		parts.push( 'required-field' );
	}
	if ( autocomplete ) {
		parts.push( 'autocomplete' );
	}
	return parts.join( ' ' );
}

function getNativeSelectId( nameAttr ) {
	return `${ nameAttr }__gf-native`;
}

function getListboxId( nameAttr ) {
	return `${ nameAttr }__gf-listbox`;
}

export default function Save( { attributes } ) {
	const {
		nameAttr,
		fieldName,
		isRequired,
		selectOptions,
		autocomplete,
		description,
	} = attributes;

	const blockProps = useBlockProps.save( {
		className:
			'wp-block-gutena-field-group wp-block-gutena-dropdown-field field-group-type-select standalone-dropdown-field',
	} );

	const fieldClasses = getFieldClasses( { isRequired, autocomplete } );
	const nativeId = getNativeSelectId( nameAttr );
	const listboxId = getListboxId( nameAttr );

	const options = [];
	if ( isRequired ) {
		options.push(
			<option key="gf-placeholder" value="select">
				{ __( 'Select an Option', 'gutena-forms' ) }
			</option>
		);
	}
	if ( Array.isArray( selectOptions ) ) {
		selectOptions.forEach( ( item, index ) => {
			if ( gfIsEmpty( item ) ) {
				return;
			}
			options.push(
				<option key={ index } value={ item }>
					{ item }
				</option>
			);
		} );
	}

	const labelId = `${ nameAttr }-gf-label`;

	const firstRealOption = Array.isArray( selectOptions )
		? selectOptions.find( ( item ) => ! gfIsEmpty( item ) )
		: '';
	const initialTriggerLabel = isRequired
		? __( 'Select an Option', 'gutena-forms' )
		: firstRealOption || '';

	return (
		<div { ...blockProps }>
			<label
				id={ labelId }
				htmlFor={ nameAttr }
				className="heading-input-label-gutena"
			>
				{ fieldName }
				{ isRequired ? ' *' : '' }
			</label>
			<div className="wp-block-gutena-form-field">
				<div
					className="gf-dropdown-custom"
					data-gf-dropdown-custom="1"
					data-gf-field-label={ fieldName }
				>
					<select
						id={ nativeId }
						name={ nameAttr }
						className={ `gf-dropdown-custom__native ${ fieldClasses }` }
						tabIndex={ -1 }
						aria-hidden="true"
						required={ isRequired }
					>
						{ options }
					</select>
					<button
						type="button"
						id={ nameAttr }
						className="gf-dropdown-custom__trigger"
						aria-haspopup="listbox"
						aria-expanded="false"
						aria-controls={ listboxId }
					>
						<span
							className="gf-dropdown-custom__value"
							aria-hidden="true"
						>
							{ initialTriggerLabel }
						</span>
						<span
							className="gf-dropdown-custom__icon"
							aria-hidden="true"
						/>
					</button>
					<div className="gf-dropdown-custom__popover" hidden>
						<ul
							id={ listboxId }
							className="gf-dropdown-custom__list"
							role="listbox"
							tabIndex={ -1 }
							aria-labelledby={ labelId }
						/>
					</div>
				</div>
			</div>
			{ ! gfIsEmpty( description ) && (
				<p className="gutena-forms-dropdown-field-description">
					{ description }
				</p>
			) }
			<p className="gutena-forms-field-error-msg" />
		</div>
	);
}
