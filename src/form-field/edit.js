import { __ } from '@wordpress/i18n';
import { applyFilters, hasFilter } from '@wordpress/hooks';
import { useState, useEffect, useRef } from '@wordpress/element';
import {
	useBlockProps,
	InspectorControls,
	store as blockEditorStore,
	BlockControls,
} from '@wordpress/block-editor';
import { gfIsEmpty, gfSanitizeName, getInnerBlocksbyNameAttr } from '../helper';
import { useDispatch, useSelect, select } from '@wordpress/data';
import {
	PanelBody,
	PanelRow,
	TextControl,
	ToggleControl,
	RangeControl,
	SelectControl,
	FormTokenField,
	ToolbarGroup, 
	ToolbarButton
} from '@wordpress/components';
import SelectFieldType from '../components/SelectFieldType';
import { gutenaFormsIcon } from '../icon';


//check for duplicate name attr
const isFieldNameAttrReserved = ( nameAttrCheck, clientIdCheck ) => {
    const blocksClientIds = select( 'core/block-editor' ).getClientIdsWithDescendants();
    return gfIsEmpty( blocksClientIds ) ? false : blocksClientIds.some( ( blockClientId ) => {
        const { nameAttr  } = select( 'core/block-editor' ).getBlockAttributes( blockClientId );
		//different Client Id but same name attribute means duplicate
        return clientIdCheck !== blockClientId && nameAttr === nameAttrCheck;
    } );
};

export default function edit( {
	className,
	attributes,
	setAttributes,
	isSelected,
	clientId,
	context,
	gutenaExtends={}
} ) {

	const {
		nameAttr,
		fieldName,
		fieldClasses,
		fieldType,
		isRequired,
		placeholder,
		defaultValue,
		autocomplete,
		autoCapitalize,
		textAreaRows,
		maxlength,
		minMaxStep,
		preFix,
		sufFix,
		selectOptions,
		optionsColumns,
		optionsInline,
		multiSelect,
		errorRequiredMsg,
		errorInvalidInputMsg,
		description,
		settings,
		style,
	} = attributes;

	const previousFieldType = useRef( fieldType );

	useEffect(() => {
		previousFieldType.current = fieldType;
	}, [fieldType]);

	//Fields which use input tag
	const textLikeInput = [ 'text', 'email', 'number' ];
	
	//Field types
	const formfieldTypes = [
		{ label: 'Text', value: 'text' },
		{ label: 'Number', value: 'number' },
		{ label: 'Range', value: 'range' },
		{ label: 'TextArea', value: 'textarea' },
		{ label: 'Email', value: 'email' },
		{ label: 'Dropdown', value: 'select' },
		{ label: 'Radio', value: 'radio' },
		{ label: 'Checkbox', value: 'checkbox' },
		{ label: 'Opt-in Checkbox - Privacy policy, Terms', value: 'optin' },
	];

	let addNewFieldTypes = [];
	if ( hasFilter( 'gutenaforms.field.types' ) ) {
		addNewFieldTypes = applyFilters(
			'gutenaforms.field.types',
			addNewFieldTypes
		);
	}

	const fieldTypes = ( gfIsEmpty( addNewFieldTypes ) || 0 ===  addNewFieldTypes.length ) ? formfieldTypes: [...formfieldTypes, ...addNewFieldTypes];

	//get new field types
	const newFieldTypes = ( gfIsEmpty( addNewFieldTypes ) || 0 ===  addNewFieldTypes.length ) ? [] : addNewFieldTypes.map(field => field.value);
	

	const [ selectInputOption, setSelectInputOption ] = useState(
		selectOptions[ 0 ]
	);

	const [ htmlInputValue, setHtmlInputValue ] = useState('');

	const {
		selectBlock,
		moveBlocksDown, 
		moveBlocksUp,
		updateBlockAttributes //Use to to update block attributes using clientId
	} = useDispatch( blockEditorStore );

	const { 
		gutenaFormClientID, 
		labelClientId, 
		labelRootClientId,
		parentFieldGroupClientID, 
		parentFieldGroupClassName, 
		parentCoreGroupClientID,
		parentCoreGroupLayout
	} = useSelect(
		( select ) => {
			//get parent gutena form clientIds
			let gutenaFormClientID = select( blockEditorStore ).getBlockParentsByBlockName( clientId,'gutena/forms', true );
			gutenaFormClientID = gfIsEmpty( gutenaFormClientID ) ? gutenaFormClientID: gutenaFormClientID[0];

			//Returns the client ID of the block adjacent one at the given reference startClientId and modifier directionality. Directionality multiplier (1 next, -1 previous).
			//Get Input label ClientID 
			let labelBlockPosition = ( ! gfIsEmpty( previousFieldType ) && 'optin' == previousFieldType?.current ) ? 1 : -1;
			let labelClientId = select( blockEditorStore ).getAdjacentBlockClientId( clientId, labelBlockPosition );

			//Parent field-group block
			let parentFieldGroupClientID = select( blockEditorStore ).getBlockParentsByBlockName( clientId,'gutena/field-group', true );

			let parentCoreGroupClientID = select( blockEditorStore ).getBlockParentsByBlockName( clientId,'core/group', true );

			let labelRootClientId = '';

			if ( ! gfIsEmpty( labelClientId ) ) {
				labelRootClientId = select( blockEditorStore ).getBlockRootClientId( labelClientId );
			}

			
			let parentFieldGroupClassName = '';
			if ( ! gfIsEmpty( parentFieldGroupClientID ) ) {
				parentFieldGroupClientID = parentFieldGroupClientID[0];
				let parentFieldGroupAttr = select( blockEditorStore ).getBlockAttributes( parentFieldGroupClientID );

				if ( ! gfIsEmpty( parentFieldGroupAttr ) && ! gfIsEmpty( parentFieldGroupAttr.className ) ) {
					parentFieldGroupClassName = parentFieldGroupAttr.className;
				}
			}
			let parentCoreGroupLayout = {};
			if ( ! gfIsEmpty( parentCoreGroupClientID ) ) {
				parentCoreGroupClientID = parentCoreGroupClientID[0];
				let parentCoreGroupAttr = select( blockEditorStore ).getBlockAttributes( parentCoreGroupClientID );
				if ( ! gfIsEmpty( parentCoreGroupAttr ) && ! gfIsEmpty( parentCoreGroupAttr.layout ) ) {
					parentCoreGroupLayout = parentCoreGroupAttr.layout;
				}
			}

			return {
				gutenaFormClientID,
				labelClientId,
				labelRootClientId,
				parentFieldGroupClientID,
				parentFieldGroupClassName,
				parentCoreGroupClientID,
				parentCoreGroupLayout
			};

		},
		[ clientId, fieldType ]
	);
	
	/********************************
	 Set Field Name : START
	 *******************************/
	//Get Input Label from paragraph label block
	//Get Input label Content
	const inputLabel = useSelect(
		( select ) => {
			if ( gfIsEmpty( labelClientId ) ) {
				return null;
			}
			let labelAttr =
				select( blockEditorStore ).getBlockAttributes( labelClientId );
			return gfIsEmpty( labelAttr ) || gfIsEmpty( labelAttr.content )
				? ''
				: labelAttr.content.replace( /(<([^>]+)>)|\*/gi, '' ).trim();
		},
		[ labelClientId ]
	);



	//set name attribute if default or duplicate
	useEffect( () => {
		let shouldSetNameAttr = true;
		if ( shouldSetNameAttr ) {
			if ( 'input_1' == nameAttr || '' == nameAttr || ( ! gfIsEmpty( nameAttr ) && isFieldNameAttrReserved( nameAttr, clientId ) ) ) {
				for ( let index = 0; index < 5000; index++ ) {
					let newNameAttr = 'f_'+index;
					if ( ! isFieldNameAttrReserved( newNameAttr, clientId ) ) {
						//rename label and name attribute
						setAttributes( { 
							nameAttr: newNameAttr
						} );
						break;
					}
				}
			}
		}
		
		//cleanup
		return () => {
			shouldSetNameAttr = false;
		};
		
	}, [] );

	//Prepare field name attribute: replace space with underscore and remove unwanted characters
	const prepareFieldNameAttr = ( fieldName ) => {
		fieldName = fieldName.toLowerCase().replace( / /g, '_' );
		fieldName = fieldName.replace(/\W/g, '');
		return fieldName;
	}

	const setFieldNameAttr = ( fieldName, onChange = false ) => {
		//Set form field name
		setAttributes( { fieldName } );
		
		//On change from setting sidebar : set label content in label paragraph block
		if ( onChange && ! gfIsEmpty( labelClientId ) ) {
			updateBlockAttributes( labelClientId, { content: fieldName } );
		}
	};

	useEffect( () => {
		let shouldRunInputLabel = true;
		if ( shouldRunInputLabel ) {
			setFieldNameAttr( inputLabel );
		}

		//cleanup
		return () => {
			shouldRunInputLabel = false;
		};
	}, [ inputLabel ] );

	/********************************
	 Set Field Name : END
	 *******************************/

	// Remove unwanted field syles
	const remove_unnecessary_styles = ( ) => {
		//Input type range styles
		let remove_button = document.querySelector('.block-editor-block-styles__variants [aria-label="Border Style"]');
		if ( ! gfIsEmpty( remove_button ) ) {
			remove_button.style.display = ( isSelected && 'range' === fieldType ) ? 'inline-block': 'none';
		}
	}

	//Run on select block
	useEffect( () => {
		let shouldRunRemoveStyle = true;
		if ( shouldRunRemoveStyle ) {
			// Remove unwanted field syles
			remove_unnecessary_styles();
		}

		//cleanup
		return () => {
			shouldRunRemoveStyle = false;
		};
	}, [ isSelected ] );

	//Save form field Classnames for gutena forms field block
	useEffect( () => {
		let shouldRunFieldClassnames = true;
		if ( shouldRunFieldClassnames ) {
			// Remove unwanted field syles
			remove_unnecessary_styles();

			let InputClassName = `gutena-forms-field ${ fieldType }-field ${ isRequired ? 'required-field' : ''
			} ${ autocomplete ? 'autocomplete': '' } `;
			if ( -1 !== ['radio', 'checkbox'].indexOf( fieldType ) ) {
				InputClassName += optionsInline ? ' inline-options' : ' has-'+optionsColumns+'-col';
			}

			if ( 'optin' ===  fieldType && ! isRequired ) {
				setAttributes( { 
					fieldClasses: InputClassName,
					isRequired: true
				 } );
			} else {
				setAttributes( { fieldClasses: InputClassName } );
			}

			//add class of field type to parent gutena field group block
			let fieldGroupClassToAdd = ' field-group-type-'+fieldType+' ';
			if ( ! gfIsEmpty( parentFieldGroupClientID ) && -1 === parentFieldGroupClassName.indexOf( fieldGroupClassToAdd ) ) {
				updateBlockAttributes( parentFieldGroupClientID, { className: parentFieldGroupClassName + fieldGroupClassToAdd } );
			}

			//convert stack into row for optin field type
			if ( ! gfIsEmpty( parentCoreGroupClientID ) && ! gfIsEmpty( parentCoreGroupLayout ) ) {
				if ( 'optin' ===  fieldType  && ! gfIsEmpty( parentCoreGroupLayout.orientation ) && 'vertical' === parentCoreGroupLayout.orientation ) {
					//for backwardButton decrease index (move left/up) for forwardButton increase index (move right/down)
					if ( ! gfIsEmpty( labelClientId ) && ! gfIsEmpty( labelRootClientId ) ) {
						updateBlockAttributes( labelClientId, { content: __( 'I agree to the Terms', 'gutena-forms' ) } );
						moveBlocksDown( [ labelClientId ], labelRootClientId );
					}

					updateBlockAttributes( parentCoreGroupClientID, { layout: {
						...parentCoreGroupLayout,
						orientation: undefined,
						flexWrap:'nowrap'
					} } );
					
				} else if ( 'optin' !==  fieldType && gfIsEmpty( parentCoreGroupLayout.orientation )  ) {
					updateBlockAttributes( parentCoreGroupClientID, { layout: {
						...parentCoreGroupLayout,
						orientation: 'vertical',
						flexWrap: undefined
					} } );

					if ( ! gfIsEmpty( labelClientId ) && ! gfIsEmpty( labelRootClientId ) ) {
						updateBlockAttributes( labelClientId, { content: '' } );
						moveBlocksUp( [ labelClientId ], labelRootClientId );
					}
				}
			}
		}

		//cleanup
		return () => {
			shouldRunFieldClassnames = false;
		};
	}, [ fieldType, isRequired, optionsInline, optionsColumns, autocomplete ] );

	/********************************
	 Input Field Component : START
	 *******************************/
	const inputFieldComponent = () => {
		//Input Field
		if ( 0 <= textLikeInput.indexOf( fieldType ) ) {
			return (
				<input
					type={ fieldType }
					className={ fieldClasses }
					value={ htmlInputValue ?? '' }
					onChange={
						(e) => setHtmlInputValue(e.target.value)
					}
					placeholder={
						placeholder ? placeholder : __( 'Placeholder…' )
					}
					required={ isRequired ? 'required' : '' }
				/>
			);
		}

		//Input Field range
		if ( 'range' === fieldType ) {
			return (
				<div className='gf-range-container'>
					<input
						type={ fieldType }
						className={ fieldClasses }
						required={ isRequired ? 'required' : '' }
						value={ htmlInputValue ?? '' }
						onChange={
							(e) => setHtmlInputValue(e.target.value)
						}
					/>
					<p className='gf-range-values' >
						{
							! gfIsEmpty( minMaxStep?.min ) && 
							<span className='gf-prefix-value-wrapper'>
								<span className='gf-prefix'>{ gfIsEmpty( preFix ) ? '': preFix }</span>
								<span className='gf-value'>{ minMaxStep?.min }</span>
								<span className='gf-suffix'>{ gfIsEmpty( sufFix ) ? '': sufFix }</span>
							</span> 
						}
						{
							! gfIsEmpty( htmlInputValue ) && 
							<span className='gf-prefix-value-wrapper'>
								<span className='gf-prefix'>{ gfIsEmpty( preFix ) ? '': preFix }</span>
								<span className='gf-value range-input-value'>{ htmlInputValue }</span>
								<span className='gf-suffix'>{ gfIsEmpty( sufFix ) ? '': sufFix }</span>
							</span> 
						}
						{
							! gfIsEmpty( minMaxStep?.max ) && 
							<span className='gf-prefix-value-wrapper'>
								<span className='gf-prefix'>{ gfIsEmpty( preFix ) ? '': preFix }</span>
								<span className='gf-value'>{ minMaxStep?.max }</span>
								<span className='gf-suffix'>{ gfIsEmpty( sufFix ) ? '': sufFix }</span>
							</span> 
						}
					</p>
				</div>
			);
		}

		//Textarea Field
		if ( 'textarea' === fieldType ) {
			return (
				<textarea
					className={ fieldClasses }
					placeholder={
						placeholder ? placeholder : __( 'Placeholder…' )
					}
					required={ isRequired ? 'required' : '' }
					rows={ textAreaRows }
				></textarea>
			);
		}

		if ( 'select' === fieldType ) {
			return (
				<select
					className={ fieldClasses }
					value={ selectInputOption }
					onChange={ ( e ) => setSelectInputOption( e.target.value ) }
					required={ isRequired ? 'required' : '' }
				>
					{ selectOptions.map( ( item, index ) => {
						return (
							<option key={ index } value={ item }>
								{ item }
							</option>
						);
					} ) }
				</select>
			);
		}

		if ( 'radio' === fieldType || 'checkbox' === fieldType ) {
			return (
				<div
					className={ fieldClasses }
				>
				{ 
				selectOptions.map( ( item, index ) => {
					return (
						<label key={ index } className={ fieldType+'-container' } > 
							{ item }
							<input 
							type={ fieldType } 
							name={ fieldName } 
							value={ item }
							checked={ item === selectInputOption}
							onChange={ ( e ) => setSelectInputOption( e.target.value ) }
							/>
							<span className="checkmark"></span>
						</label>
					);
				} ) 
				}
				</div>
			);
		}

		if ( 'optin' === fieldType ) {
			return (
				<div
					className={ fieldClasses }
				>
				<label className={ 'checkbox-container' } > 
					<input 
					type="checkbox"
					name={ fieldName } 
					value='1'
					checked={ ! gfIsEmpty( htmlInputValue ) }
					onChange={ ( e ) => setSelectInputOption( '1' === htmlInputValue ? '':'1' ) }
					/>
					<span className="checkmark"></span>
				</label>
				</div>
			);
		}

		if ( ! gfIsEmpty( gutenaExtends?.inputFieldComponent ) && 0 <= newFieldTypes.indexOf( fieldType ) ) {
		   return gutenaExtends.inputFieldComponent();
		}
	};

	/********************************
	 Input Field Component : END
	 *******************************/

	const blockProps = useBlockProps( {
		className: `gutena-forms-${ fieldType }-field field-name-${ nameAttr } ${ optionsInline ? 'gf-inline-content' : '' }`,
	} );

	
	return (
		<>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						icon={ gutenaFormsIcon }
						label={ __( 'Select form block', 'gutena-forms' ) }
						onClick={ () => {
							if ( ! gfIsEmpty( gutenaFormClientID ) ) {
								selectBlock( gutenaFormClientID );
							}
						} }
					/>
				</ToolbarGroup>
			</BlockControls>
			<InspectorControls>
				<PanelBody title={ __( 'Field Type', 'gutena-forms' ) } initialOpen={ true }>
					<SelectFieldType 
						fieldType={ fieldType }
						fieldTypes={ fieldTypes }
						newFieldTypes={ newFieldTypes }
						onChangeFunc={ ( fieldType ) => setAttributes( { fieldType } ) }
					/>
					{ ! gfIsEmpty( gutenaExtends?.gfcontrols ) && gutenaExtends.gfcontrols() }
					{ ( -1 !== ['select', 'checkbox', 'radio'].indexOf( fieldType ) ) && (
						<FormTokenField
							label={ autocomplete ? __( 'Preferences', 'gutena-forms' ) : __( 'Options', 'gutena-forms' ) }
							value={ selectOptions }
							suggestions={ selectOptions }
							onChange={ ( selectOptions ) =>
								setAttributes( { selectOptions } )
							}
						/>
					) }
					{ -1 !== ['radio', 'checkbox'].indexOf( fieldType ) && (
						<>
						<ToggleControl
							label={ __( 'Show Inline', 'gutena-forms' ) }
							className="gf-mt-1"
							help={
								optionsInline
									? __(
											'Toggle to make options show in columns',
											'gutena-forms'
									)
									: __(
											'Toggle to make options show inline',
											'gutena-forms'
									)
							}
							checked={ optionsInline }
							onChange={ ( optionsInline ) =>
								setAttributes( { optionsInline } )
							}
						/>
						{
							! optionsInline &&
							<RangeControl
								label={ __( 'Columns', 'gutena-forms' ) }
								value={ optionsColumns }
								onChange={ ( optionsColumns ) =>
									setAttributes( { optionsColumns } )
								}
								min={ 1 }
								max={ 6 }
								step={ 1 }
							/>
						}
						</>
					) }
					{ ( 'number' === fieldType || 'range' === fieldType ) && (
						<>
						<h2 className="block-editor-block-card__title gf-mt-1 ">{ __( 'Value', 'gutena-forms' ) }</h2>
						<PanelRow className="gf-child-mb-0 gf-mb-24">
						<TextControl
							label={ __( 'Minimum', 'gutena-forms' ) }
							value={ minMaxStep?.min }
							type="number"
							onChange={ ( min ) =>
								setAttributes( { minMaxStep:{
									...minMaxStep,
									min
								} } )
							}
						/>
						<TextControl
							label={ __( 'Maximum', 'gutena-forms' ) }
							value={ minMaxStep?.max }
							type="number"
							onChange={ ( max ) =>
								setAttributes( { minMaxStep:{
									...minMaxStep,
									max
								} } )
							}
						/>
						<TextControl
							label={ __( 'Step', 'gutena-forms' ) }
							value={ minMaxStep?.step }
							type="number"
							onChange={ ( step ) =>
								setAttributes( { minMaxStep:{
									...minMaxStep,
									step
								} } )
							}
						/>
						</PanelRow>
						<PanelRow className="gf-child-mb-0 gf-mb-24">
							<TextControl
								label={ __( 'Prefix', 'gutena-forms' ) }
								value={ preFix }
								onChange={ ( preFix ) =>
									setAttributes( { preFix } )
								}
							/>
							<TextControl
								label={ __( 'Suffix', 'gutena-forms' ) }
								value={ sufFix }
								onChange={ ( sufFix ) =>
									setAttributes( { sufFix } )
								}
							/>
						</PanelRow>
						</>
					) }
				</PanelBody>
				<PanelBody title={ __( 'Field settings', 'gutena-forms' ) } initialOpen={ true }>
					<TextControl
						label={ __( 'Label', 'gutena-forms' )+' * ' }
						className={ gfIsEmpty( fieldName ) ? ' gf-required-field':'' }
						help={ gfIsEmpty( fieldName ) ? __(
							'Please add label to the field',
							'gutena-forms'
						):'' }
						value={ fieldName ?? '' }
						onChange={ ( fieldName ) =>
							setFieldNameAttr( fieldName, true )
						}
					/>
					
					{ ! gfIsEmpty( gutenaExtends?.gfSettings ) && gutenaExtends.gfSettings() }
					{ -1 !== ['text', 'textarea'].indexOf( fieldType ) && (
						<RangeControl
							label={ __( 'Maxlength', 'gutena-forms' ) }
							value={ maxlength }
							onChange={ ( maxlength ) =>
								setAttributes( { maxlength } )
							}
							min={ 0 }
							max={ 500 }
							step={ 25 }
						/>
					) }
					{ 'textarea' === fieldType && (
						<RangeControl
							label={ __( 'Textarea Rows', 'gutena-forms' ) }
							value={ textAreaRows }
							onChange={ ( textAreaRows ) =>
								setAttributes( { textAreaRows } )
							}
							min={ 2 }
							max={ 20 }
							step={ 1 }
						/>
					) }
					<PanelRow>
						<TextControl
							label={ __( 'Placeholder', 'gutena-forms' ) }
							value={ placeholder }
							onChange={ ( placeholder ) =>
								setAttributes( { placeholder } )
							}
						/>
					</PanelRow>
					<PanelRow>
						<ToggleControl
							label={ __( 'Required', 'gutena-forms' ) }
							help={
								isRequired
									? __(
											'Toggle to make input field not required',
											'gutena-forms'
									  )
									: __(
											'Toggle to make input field required',
											'gutena-forms'
									  )
							}
							checked={ isRequired }
							disabled={ 'optin' ===  fieldType }
							onChange={ ( isRequired ) =>
								setAttributes( { isRequired } )
							}
						/>
					</PanelRow>
					{ ['text','textarea','number'].includes( fieldType ) && (
						<TextControl
							label={ __( 'Default Value', 'gutena-forms' ) }
							value={ defaultValue }
							type='text'
							onChange={ ( defaultValue ) =>
								setAttributes( { defaultValue } )
							}
						/>
					) }
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				{ fieldType.length > 0 ? inputFieldComponent() : '' }
			</div>
		</>
	);
}
