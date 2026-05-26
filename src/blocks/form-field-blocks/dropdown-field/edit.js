import { __ } from '@wordpress/i18n';
import {
	useCallback,
	useEffect,
	useMemo,
	useRef,
	useState,
} from '@wordpress/element';
import { select } from '@wordpress/data';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	ToggleControl,
	FormTokenField,
} from '@wordpress/components';
import { gfIsEmpty, gfSanitizeName } from '../../../shared/utils/helper';

const isFieldNameAttrReserved = ( nameAttrCheck, clientIdCheck ) => {
	const blocksClientIds =
		select( 'core/block-editor' ).getClientIdsWithDescendants();
	return gfIsEmpty( blocksClientIds )
		? false
		: blocksClientIds.some( ( blockClientId ) => {
				const attrs =
					select( 'core/block-editor' ).getBlockAttributes(
						blockClientId
					);
				return (
					clientIdCheck !== blockClientId &&
					! gfIsEmpty( attrs?.nameAttr ) &&
					attrs.nameAttr === nameAttrCheck
				);
		  } );
};

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

function getDefaultSelectedValue( isRequired, selectOptions ) {
	if ( isRequired ) {
		return 'select';
	}
	if ( Array.isArray( selectOptions ) && selectOptions.length ) {
		const first = selectOptions.find( ( o ) => ! gfIsEmpty( o ) );
		return first ?? '';
	}
	return '';
}

function DropdownCustomEditor( {
	nameAttr,
	fieldName,
	isRequired,
	selectOptions,
	fieldClasses,
} ) {
	const wrapRef = useRef( null );
	const [ isOpen, setIsOpen ] = useState( false );
	const [ activeIndex, setActiveIndex ] = useState( 0 );

	const rows = useMemo( () => {
		const list = [];
		if ( isRequired ) {
			list.push( {
				value: 'select',
				label: __( 'Select an Option', 'gutena-forms' ),
				disabled: false,
			} );
		}
		if ( Array.isArray( selectOptions ) ) {
			selectOptions.forEach( ( item ) => {
				if ( gfIsEmpty( item ) ) {
					return;
				}
				list.push( { value: item, label: item, disabled: false } );
			} );
		}
		return list;
	}, [ isRequired, selectOptions ] );

	const [ selectedValue, setSelectedValue ] = useState( () =>
		getDefaultSelectedValue( isRequired, selectOptions )
	);

	useEffect( () => {
		const next = getDefaultSelectedValue( isRequired, selectOptions );
		if ( ! rows.some( ( r ) => r.value === selectedValue ) ) {
			setSelectedValue( next );
		}
	}, [ isRequired, selectOptions, rows, selectedValue ] );

	const selectedRow = useMemo(
		() => rows.find( ( r ) => r.value === selectedValue ) ?? rows[ 0 ],
		[ rows, selectedValue ]
	);

	const displayLabel = selectedRow?.label ?? '';

	const enabledIndices = useMemo( () => {
		return rows
			.map( ( r, i ) => ( r.disabled ? -1 : i ) )
			.filter( ( i ) => i >= 0 );
	}, [ rows ] );

	const close = useCallback( () => setIsOpen( false ), [] );

	useEffect( () => {
		if ( ! isOpen ) {
			return;
		}
		const onDoc = ( e ) => {
			if ( wrapRef.current && ! wrapRef.current.contains( e.target ) ) {
				close();
			}
		};
		document.addEventListener( 'click', onDoc, true );
		return () => document.removeEventListener( 'click', onDoc, true );
	}, [ isOpen, close ] );

	const selectIndex = useCallback(
		( index ) => {
			const row = rows[ index ];
			if ( ! row || row.disabled ) {
				return;
			}
			setSelectedValue( row.value );
			setActiveIndex( index );
			close();
		},
		[ rows, close ]
	);

	const openList = useCallback( () => {
		const idx = Math.max(
			0,
			rows.findIndex( ( r ) => r.value === selectedValue )
		);
		setActiveIndex( idx );
		setIsOpen( true );
	}, [ rows, selectedValue ] );

	const onTriggerKeyDown = useCallback(
		( e ) => {
			if ( ! enabledIndices.length ) {
				return;
			}
			if ( e.key === 'ArrowDown' ) {
				e.preventDefault();
				if ( ! isOpen ) {
					openList();
				} else {
					const pos = enabledIndices.indexOf( activeIndex );
					const next =
						enabledIndices[
							Math.min( enabledIndices.length - 1, pos + 1 )
						];
					setActiveIndex( next );
				}
			} else if ( e.key === 'ArrowUp' ) {
				e.preventDefault();
				if ( ! isOpen ) {
					openList();
				} else {
					const pos = enabledIndices.indexOf( activeIndex );
					const next = enabledIndices[ Math.max( 0, pos - 1 ) ];
					setActiveIndex( next );
				}
			} else if ( e.key === 'Enter' || e.key === ' ' ) {
				e.preventDefault();
				if ( isOpen ) {
					selectIndex( activeIndex );
				} else {
					openList();
				}
			} else if ( e.key === 'Escape' && isOpen ) {
				e.preventDefault();
				close();
			} else if ( e.key === 'Home' && isOpen ) {
				e.preventDefault();
				setActiveIndex( enabledIndices[ 0 ] );
			} else if ( e.key === 'End' && isOpen ) {
				e.preventDefault();
				setActiveIndex( enabledIndices[ enabledIndices.length - 1 ] );
			}
		},
		[ enabledIndices, isOpen, activeIndex, openList, selectIndex, close ]
	);

	const labelId = `${ nameAttr }-gf-label`;
	const nativeId = getNativeSelectId( nameAttr );
	const listboxId = getListboxId( nameAttr );

	return (
		<div
			ref={ wrapRef }
			className={ `gf-dropdown-custom${ isOpen ? ' is-open' : '' }` }
			data-gf-dropdown-custom="1"
			data-gf-field-label={ fieldName }
		>
			<select
				id={ nativeId }
				name={ nameAttr }
				className={ `gf-dropdown-custom__native ${ fieldClasses }` }
				tabIndex={ -1 }
				aria-hidden="true"
				value={ selectedValue }
				onChange={ ( e ) => setSelectedValue( e.target.value ) }
			>
				{ rows.map( ( row ) => (
					<option key={ row.value } value={ row.value }>
						{ row.label }
					</option>
				) ) }
			</select>
			<button
				type="button"
				id={ nameAttr }
				className="gf-dropdown-custom__trigger"
				aria-haspopup="listbox"
				aria-expanded={ isOpen }
				aria-controls={ listboxId }
				onClick={ ( ev ) => {
					ev.preventDefault();
					setIsOpen( ( o ) => ! o );
				} }
				onKeyDown={ onTriggerKeyDown }
			>
				<span className="gf-dropdown-custom__value" aria-hidden="true">
					{ displayLabel }
				</span>
				<span className="gf-dropdown-custom__icon" aria-hidden="true" />
			</button>
			<div className="gf-dropdown-custom__popover" hidden={ ! isOpen }>
				<ul
					id={ listboxId }
					className="gf-dropdown-custom__list"
					role="listbox"
					tabIndex={ -1 }
					aria-labelledby={ labelId }
					onKeyDown={ ( e ) => {
						if ( e.key === 'Escape' ) {
							e.preventDefault();
							close();
						} else if ( e.key === 'Enter' ) {
							e.preventDefault();
							selectIndex( activeIndex );
						}
					} }
				>
					{ rows.map( ( row, index ) => (
						<li
							key={ row.value }
							id={ `${ nativeId }-opt-${ index }` }
							className={ `gf-dropdown-custom__option${
								index === activeIndex ? ' is-active' : ''
							}` }
							role="option"
							aria-selected={ row.value === selectedValue }
							aria-disabled={ row.disabled ? 'true' : undefined }
							onMouseEnter={ () => setActiveIndex( index ) }
							onClick={ () => selectIndex( index ) }
							onKeyDown={ ( e ) => {
								if ( e.key === 'Enter' || e.key === ' ' ) {
									e.preventDefault();
									selectIndex( index );
								}
							} }
						>
							{ row.label }
						</li>
					) ) }
				</ul>
			</div>
		</div>
	);
}

export default function Edit( { attributes, setAttributes, clientId } ) {
	const {
		nameAttr,
		fieldName,
		isRequired,
		selectOptions,
		autocomplete,
		description,
	} = attributes;

	useEffect( () => {
		if (
			! gfIsEmpty( nameAttr ) &&
			! isFieldNameAttrReserved( nameAttr, clientId )
		) {
			return;
		}

		for ( let index = 0; index < 5000; index++ ) {
			const nextName = `f_${ index }`;
			if ( ! isFieldNameAttrReserved( nextName, clientId ) ) {
				setAttributes( { nameAttr: nextName } );
				break;
			}
		}
	}, [] );

	const fieldClasses = useMemo(
		() => getFieldClasses( { isRequired, autocomplete } ),
		[ isRequired, autocomplete ]
	);

	const blockProps = useBlockProps( {
		className:
			'wp-block-gutena-field-group wp-block-gutena-dropdown-field field-group-type-select standalone-dropdown-field',
	} );

	const labelId = `${ nameAttr }-gf-label`;

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Field settings', 'gutena-forms' ) }
					initialOpen={ true }
				>
					<TextControl
						label={ __( 'Label', 'gutena-forms' ) + ' *' }
						value={ fieldName ?? '' }
						onChange={ ( nextLabel ) => {
							const updates = { fieldName: nextLabel };
							if (
								gfIsEmpty( nameAttr ) ||
								0 === nameAttr.indexOf( 'f_' )
							) {
								updates.nameAttr = gfSanitizeName( nextLabel );
							}
							setAttributes( updates );
						} }
					/>
					<TextControl
						label={ __( 'Name attribute', 'gutena-forms' ) + ' *' }
						value={ nameAttr ?? '' }
						onChange={ ( nextNameAttr ) =>
							setAttributes( {
								nameAttr: gfSanitizeName( nextNameAttr ),
							} )
						}
						help={ __(
							'Used as input name in form submission.',
							'gutena-forms'
						) }
					/>
					<FormTokenField
						label={ __( 'Options', 'gutena-forms' ) }
						value={ selectOptions }
						suggestions={ selectOptions }
						onChange={ ( nextOptions ) =>
							setAttributes( { selectOptions: nextOptions } )
						}
					/>
					<ToggleControl
						label={ __( 'Required', 'gutena-forms' ) }
						checked={ !! isRequired }
						onChange={ ( nextRequired ) =>
							setAttributes( { isRequired: nextRequired } )
						}
					/>
					<ToggleControl
						label={ __( 'Autocomplete', 'gutena-forms' ) }
						checked={ !! autocomplete }
						onChange={ ( nextAutocomplete ) =>
							setAttributes( { autocomplete: nextAutocomplete } )
						}
					/>
					<TextControl
						label={ __( 'Help text', 'gutena-forms' ) }
						value={ description ?? '' }
						onChange={ ( nextDescription ) =>
							setAttributes( { description: nextDescription } )
						}
					/>
				</PanelBody>
			</InspectorControls>
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
					<DropdownCustomEditor
						nameAttr={ nameAttr }
						fieldName={ fieldName }
						isRequired={ isRequired }
						selectOptions={ selectOptions }
						fieldClasses={ fieldClasses }
					/>
				</div>
				{ ! gfIsEmpty( description ) && (
					<p className="gutena-forms-dropdown-field-description">
						{ description }
					</p>
				) }
				<p className="gutena-forms-field-error-msg" />
			</div>
		</>
	);
}
