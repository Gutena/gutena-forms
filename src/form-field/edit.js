import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import {
	useBlockProps,
	InspectorControls,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import { gfIsEmpty } from '../helper';
import { useDispatch, useSelect } from '@wordpress/data';
import {
	PanelBody,
	PanelRow,
	TextControl,
	ToggleControl,
	RangeControl,
	SelectControl,
	FormTokenField,
} from '@wordpress/components';

export default function edit( {
	className,
	attributes,
	setAttributes,
	isSelected,
	clientId,
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
		selectOptions,
		optionsInline,
		multiSelect,
		errorRequiredMsg,
		errorInvalidInputMsg,
		description,
		settings,
		style,
	} = attributes;

	const fieldTypeOptions = [ 'text', 'textarea', 'email', 'tel', 'url' ];

	const textLikeInput = [ 'text', 'email', 'number', 'hidden', 'tel', 'url' ];

	const fieldTypes = [
		{ label: 'Text', value: 'text' },
		{ label: 'TextArea', value: 'textarea' },
		{ label: 'Email', value: 'email' },
		{ label: 'Select', value: 'select' },
	];

	const [ selectInputOption, setSelectInputOption ] = useState(
		selectOptions[ 0 ]
	);

	/********************************
	 Set Field Name : START
	 *******************************/
	//Get Input Label from paragraph label block
	/**
	 * https://developer.wordpress.org/block-editor/reference-guides/data/data-core-block-editor/#getpreviousblockclientid
	 */
	//Get Input label ClientID
	const labelClientId = useSelect( ( select ) => {
		let labelParaClientId = select(
			blockEditorStore
		).getAdjacentBlockClientId( clientId, -1 );
		if ( gfIsEmpty( labelParaClientId ) ) {
			labelParaClientId = select(
				blockEditorStore
			).getAdjacentBlockClientId( clientId, 1 );
		}

		return labelParaClientId;
	}, [] );

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

	//Use to to update block attributes using clientId
	const { updateBlockAttributes } = useDispatch( blockEditorStore );

	//For First time field name attribute generation
	const [ NameAttrFromFieldName, setNameAttrFromFieldName ] =
		useState( false );

	//Update Styles
	useEffect( () => {
		//Set name attribute if empty or default
		if ( 'input_1' == nameAttr || '' == nameAttr ) {
			setNameAttrFromFieldName( true );
		}
	}, [] );

	const setFieldNameAttr = ( fieldName, onChange = false ) => {
		if ( gfIsEmpty( fieldName ) ) {
			return;
		}

		//Set form field name
		setAttributes( { fieldName } );

		//Set form attribute name
		if ( NameAttrFromFieldName ) {
			let inputNameAttr = fieldName.toLowerCase().replace( / /g, '_' );
			setAttributes( { nameAttr: inputNameAttr } );
		}

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

	//Save form field Classnames for gutena forms field block
	useEffect( () => {
		let shouldRunFieldClassnames = true;
		if ( shouldRunFieldClassnames ) {
			let InputClassName = `gutena-forms-field ${ fieldType }-field ${
				isRequired ? 'required-field' : ''
			}`;
			setAttributes( { fieldClasses: InputClassName } );
		}

		//cleanup
		return () => {
			shouldRunFieldClassnames = false;
		};
	}, [ fieldType, isRequired ] );

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
					placeholder={
						placeholder ? placeholder : __( 'Placeholder…' )
					}
					required={ isRequired ? 'required' : '' }
				/>
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
	};

	/********************************
	 Input Field Component : END
	 *******************************/

	const blockProps = useBlockProps( {
		className: `gutena-forms-${ fieldType }-field`,
	} );
	return (
		<>
			<InspectorControls>
				<PanelBody title="Form Field" initialOpen={ true }>
					<PanelRow>
						<SelectControl
							label="Field Type"
							value={ fieldType }
							options={ fieldTypes }
							onChange={ ( fieldType ) =>
								setAttributes( { fieldType } )
							}
							help={ __(
								'Select appropriate field type for input',
								'gutena-forms'
							) }
							__nextHasNoMarginBottom
						/>
					</PanelRow>
					{ 'select' !== fieldType && (
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
					{ 'select' === fieldType && (
						<FormTokenField
							label={ __( 'Options', 'gutena-forms' ) }
							value={ selectOptions }
							suggestions={ selectOptions }
							onChange={ ( selectOptions ) =>
								setAttributes( { selectOptions } )
							}
						/>
					) }
					<PanelRow>
						<TextControl
							label={ __( 'Field Name', 'gutena-forms' ) }
							value={ fieldName }
							onChange={ ( fieldName ) =>
								setFieldNameAttr( fieldName, true )
							}
						/>
					</PanelRow>
					<PanelRow>
						<TextControl
							label={ __(
								'Field Name Attribute',
								'gutena-forms'
							) }
							help={ __(
								'Contains only letters, numbers, and underscore',
								'gutena-forms'
							) }
							value={ nameAttr }
							onChange={ ( nameAttr ) =>
								setAttributes( { nameAttr: nameAttr.toLowerCase().replace( / /g, '_' ) } )
							}
						/>
					</PanelRow>
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
							onChange={ ( isRequired ) =>
								setAttributes( { isRequired } )
							}
						/>
					</PanelRow>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				{ fieldType.length > 0 ? inputFieldComponent() : '' }
			</div>
		</>
	);
}
