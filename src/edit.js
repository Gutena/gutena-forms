import { __ } from '@wordpress/i18n';
import { get } from 'lodash';
import { useEffect } from '@wordpress/element';
import { gfIsEmpty } from './helper';
import {
	InspectorControls,
	__experimentalBlockVariationPicker,
	InnerBlocks,
	useBlockProps,
	store as blockEditorStore,
	LineHeightControl,
	PanelColorSettings,
	FontSizePicker,
	__experimentalFontFamilyControl as FontFamilyControl,
} from '@wordpress/block-editor';
import { store as editorStore } from '@wordpress/editor';
import { store as coreStore } from '@wordpress/core-data';
import { useDispatch, useSelect } from '@wordpress/data';
import {
	PanelBody,
	PanelRow,
	TextControl,
	ToggleControl,
	RangeControl,
	RadioControl,
	SelectControl,
	__experimentalUseCustomUnits as useCustomUnits,
	__experimentalToolsPanel as ToolsPanel,
	__experimentalToolsPanelItem as ToolsPanelItem,
	__experimentalUnitControl as UnitControl,
	__experimentalVStack as VStack,
	__experimentalParseQuantityAndUnitFromRawValue as parseQuantityAndUnitFromRawValue,
} from '@wordpress/components';
import {
	createBlocksFromInnerBlocksTemplate,
	store as blocksStore,
} from '@wordpress/blocks';
import './editor.scss';
import variations from './variations';
/** Hook that retrieves the given setting for the block instance in use.
 * https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#usesetting
 */

const Placeholder = ( { clientId, name, setAttributes } ) => {
	const { blockType, defaultVariation, variations } = useSelect(
		( select ) => {
			const {
				getBlockVariations,
				getBlockType,
				getDefaultBlockVariation,
			} = select( blocksStore );

			return {
				blockType: getBlockType( name ),
				defaultVariation: getDefaultBlockVariation( name, 'block' ),
				variations: getBlockVariations( name, 'block' ),
			};
		},
		[ name ]
	);
	const { replaceInnerBlocks } = useDispatch( blockEditorStore );
	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<__experimentalBlockVariationPicker
				icon={ get( blockType, [ 'icon', 'src' ] ) }
				label={ get( blockType, [ 'title' ] ) }
				variations={ variations }
				onSelect={ ( nextVariation = defaultVariation ) => {
					if ( nextVariation.attributes ) {
						setAttributes( nextVariation.attributes );
					}
					if ( nextVariation.innerBlocks ) {
						replaceInnerBlocks(
							clientId,
							createBlocksFromInnerBlocksTemplate(
								nextVariation.innerBlocks
							),
							true
						);
					}
				} }
				allowSkip
			/>
		</div>
	);
};

const MAX_SPACE_VALUES = {
	px: 100,
	em: 20,
	rem: 20,
	vh: 1,
	vw: 1,
};

export default function Edit( props ) {
	//props
	const { className, attributes, setAttributes, isSelected, clientId } =
		props;

	//Attributes
	const {
		formID,
		formName,
		formClasses,
		showLabel,
		inputLabelGap,
		inputBgColor,
		inputBorderWidth,
		inputBorderRadius,
		inputBorderColor,
		inputFocusBorderColor,
		inputBottomBorderOnly,
		labelTypography,
		labelColor,
		placeholderTypography,
		placeholderColor,
		afterSubmitAction,
		afterSubmitHide,
		redirectUrl,
		adminEmails,
		adminEmailSubject,
		emailNotifyAdmin,
		emailNotifyUser,
		messages,
		formStyle,
		style,
		recaptcha,
	} = attributes;

	//Get Post ID
	const currentPostId = useSelect( ( select ) => {
		return select( editorStore ).getCurrentPostId();
	}, [] );

	
	//Set Form ID
	useEffect( () => {
		let shouldRunFormID = true;
		if ( shouldRunFormID && gfIsEmpty( formID ) ) {
			const d = new Date();
			let randomID = Math.random().toString(16).slice(5);

			/* \W is the equivalent of [^0-9a-zA-Z_] - it includes the underscore character */
			randomID = randomID.replace(/\W/g, '');

			let GutenaFormsID =
				( 'gutena_forms_ID_' +
				randomID +
				'_' +
				d.getDate() +
				'' +
				( d.getMonth() + 1 ) +
				'' +
				d.getFullYear() +
				'' +
				d.getHours() +
				'' +
				d.getMinutes() +
				'' +
				d.getSeconds() );
			setAttributes( { formID: GutenaFormsID } );
		}

		//cleanup
		return () => {
			shouldRunFormID = false;
		};
	}, [] );

	//Check Inner Blocks
	const hasInnerBlocks = useSelect(
		( select ) =>
			select( blockEditorStore ).getBlocks( clientId ).length > 0,
		[ clientId ]
	);

	//Get Author Email
	const currentUser = useSelect( ( select ) => {
		return '' == adminEmails
			? select( coreStore ).getUsers( { who: 'authors' } )
			: [];
	}, [] );

	//Set Author Email
	useEffect( () => {
		let shouldRunAuthorEmail = true;
		if ( shouldRunAuthorEmail ) {
			if (
				'' == adminEmails &&
				'undefined' !== typeof currentUser &&
				null !== currentUser &&
				'undefined' !== typeof currentUser[ 0 ].email &&
				null !== currentUser[ 0 ].email
			) {
				setAttributes( { adminEmails: currentUser[ 0 ].email } );
			}
		}

		//cleanup
		return () => {
			shouldRunAuthorEmail = false;
		};
	}, [ currentUser ] );

	//Template
	const TEMPLATE =
		gfIsEmpty( variations ) || gfIsEmpty( variations[ 0 ].innerBlocks )
			? [ [ 'gutena/field-group' ] ]
			: variations[ 0 ].innerBlocks;

	//Spacing units
	const units = useCustomUnits( {
		availableUnits: [ 'px', 'em', 'rem', 'vh', 'vw' ],
		defaultValues: { px: 0, em: 0, rem: 0, vh: 0, vw: 0 },
	} );

	const getQtyOrunit = ( rawUnit, quantityOrUnit = 'unit' ) => {
		const [ quantityToReturn, unitToReturn ] =
			parseQuantityAndUnitFromRawValue( rawUnit );
		let unit =
			'undefined' === typeof unitToReturn || null === unitToReturn
				? 'px'
				: unitToReturn;
		let Qty =
			'undefined' === typeof quantityToReturn ||
			null === quantityToReturn ||
			'' == quantityToReturn
				? 0
				: quantityToReturn;
		return 'unit' === quantityOrUnit ? unit : quantityToReturn;
	};

	//Form Styles : local css variable for forms inner blocks styles
	useEffect( () => {
		let shouldRun = true;
		if ( shouldRun ) {
			let newFormCss = ` .${ formNameClass() } {
				${
					gfIsEmpty( labelColor )
						? ''
						: '--wp--gutena-forms--label-color:' + labelColor + ';'
				}
				${
					gfIsEmpty( placeholderColor )
						? ''
						: '--wp--gutena-forms--placeholder-color:' +
						  placeholderColor +
						  ';'
				}
				${
					gfIsEmpty( inputBgColor )
						? ''
						: '--wp--gutena-forms--input-bg-color:' +
						  inputBgColor +
						  ';'
				}
				${
					gfIsEmpty( inputBorderColor )
						? ''
						: '--wp--gutena-forms--input-border-color:' +
						  inputBorderColor +
						  ';'
				}
				${
					gfIsEmpty( inputFocusBorderColor )
						? ''
						: '--wp--gutena-forms--input-focus-border-color:' +
						  inputFocusBorderColor +
						  ';'
				}
				${
					gfIsEmpty( labelTypography?.fontFamily )
						? ''
						: '--wp--gutena-forms--label-font-family:' +
						labelTypography?.fontFamily +
						  ';'
				}
				${
					gfIsEmpty( labelTypography )
						? ''
						: '--wp--gutena-forms--label-font-size:' +
						  labelTypography?.fontSize +
						  ';'
				}
				${
					gfIsEmpty( labelTypography )
						? ''
						: '--wp--gutena-forms--label-line-height:' +
						  labelTypography?.lineHeight +
						  ';'
				}
				${
					gfIsEmpty( labelTypography )
						? ''
						: '--wp--gutena-forms--label-font-weight:' +
						  labelTypography?.fontWeight +
						  ';'
				}
				${
					gfIsEmpty( placeholderTypography?.fontFamily )
						? ''
						: '--wp--gutena-forms--placeholder-font-family:' +
						  placeholderTypography?.fontFamily +
						  ';'
				}
				${
					gfIsEmpty( placeholderTypography )
						? ''
						: '--wp--gutena-forms--placeholder-font-size:' +
						  placeholderTypography?.fontSize +
						  ';'
				}
				${
					gfIsEmpty( placeholderTypography )
						? ''
						: '--wp--gutena-forms--placeholder-line-height:' +
						  placeholderTypography?.lineHeight +
						  ';'
				}
				${
					gfIsEmpty( placeholderTypography )
						? ''
						: '--wp--gutena-forms--placeholder-font-weight:' +
						  placeholderTypography?.fontWeight +
						  ';'
				}
				${
					gfIsEmpty( inputBorderWidth )
						? ''
						: '--wp--gutena-forms--input-border-width:' +
						  inputBorderWidth +
						  ';'
				}
				${
					gfIsEmpty( inputBorderRadius )
						? ''
						: '--wp--gutena-forms--input-border-radius:' +
						  inputBorderRadius +
						  ';'
				}
				${
					gfIsEmpty( labelColor )
						? ''
						: '--wp--gutena-forms--label-color:' + labelColor + ';'
				}
				${
					gfIsEmpty( labelColor )
						? ''
						: '--wp--gutena-forms--label-color:' + labelColor + ';'
				}
				
				--wp--style--block-gap:${
					'undefined' === typeof style?.spacing?.blockGap
						? '2em'
						: style?.spacing?.blockGap
				};
			}

			
			${
				gfIsEmpty( style?.spacing?.blockGap )
					? ''
					: `.editor-styles-wrapper .${ formNameClass() } > .block-editor-inner-blocks > .block-editor-block-list__layout > * + * {
						margin-block-start: ${ style?.spacing?.blockGap };
						margin-block-end: 0;
					}
					`
			}
			

			 .${ formNameClass() } .wp-block-gutena-field-group {--wp--style--block-gap:${
				'undefined' === typeof inputLabelGap ? '0.5em' : inputLabelGap
			};}
			`;
			//Set Form Styles
			setAttributes( { formStyle: newFormCss } );
		}

		//cleanup
		return () => {
			shouldRun = false;
		};
	}, [
		formName,
		inputLabelGap,
		inputBgColor,
		inputBorderWidth,
		inputBorderRadius,
		inputBorderColor,
		inputFocusBorderColor,
		labelTypography,
		labelColor,
		placeholderTypography,
		placeholderColor,
		style,
	] );

	const formNameClass = () => {
		return (
			'gutena-forms-' +
			( 'undefined' === typeof formName || null === formName
				? 'contact-form'
				: formName.toLowerCase().replace( / /g, '-' ) )
		);
	};

	//Save Form Classnames for gutena forms block
	useEffect( () => {
		let shouldRunFormClassnames = true;
		if ( shouldRunFormClassnames ) {
			let formClassName = formNameClass();
			formClassName += ' ' + formID;
			formClassName += showLabel ? '' : ' not-show-form-labels';
			formClassName += inputBottomBorderOnly
				? ' input-box-border-bottom-only'
				: '';
			formClassName += afterSubmitHide ? ' hide-form-after-submit' : '';
			formClassName += afterSubmitAction
				? ' after_submit_' + afterSubmitAction
				: '';
			formClassName += gfIsEmpty( labelTypography?.fontFamily ) ? '': ' has-label-font-family';
			formClassName += gfIsEmpty( placeholderTypography?.fontFamily ) ? '': ' has-placeholder-font-family';
			setAttributes( { formClasses: formClassName } );
		}

		//cleanup
		return () => {
			shouldRunFormClassnames = false;
		};
	}, [
		showLabel,
		inputBottomBorderOnly,
		formName,
		formID,
		afterSubmitHide,
		afterSubmitAction,
		labelTypography,
		placeholderTypography
	] );

	const ALLOWED_BLOCKS = [
		'core/columns',
		'core/group',
		'core/image',
		'core/paragraph',
		'gutena/field-group',
		'core/buttons',
	];

	const blockProps = useBlockProps( {
		className: formClasses,
	} );

	return (
		<>
			<style>{ formStyle }</style>
			<InspectorControls>
				<PanelBody title="Form settings" initialOpen={ true }>
					<PanelRow>
						<TextControl
							label={ __( 'Form name', 'gutena-forms' ) }
							value={ formName }
							onChange={ ( formName ) =>
								setAttributes( { formName } )
							}
						/>
					</PanelRow>
					<PanelRow>
						<ToggleControl
							label={ __( 'Show label', 'gutena-forms' ) }
							help={
								showLabel
									? __(
											'Toggle to hide input label',
											'gutena-forms'
									  )
									: __(
											'Toggle to show input label',
											'gutena-forms'
									  )
							}
							checked={ showLabel }
							onChange={ ( showLabel ) =>
								setAttributes( { showLabel } )
							}
						/>
					</PanelRow>
					<p ><span className="block-editor-block-card__title" >{ __( 'Note : ', 'gutena-forms' ) }</span>
					<span className="gf-text-muted" >
					{ __( 'To reuse this form, please make it a reusable block. Please avoid copying or duplicating this block.', 'gutena-forms' ) }
					</span>
					</p>
				</PanelBody>
				<PanelBody title="Google reCAPTCHA" initialOpen={ false }>
				<VStack >
					<p><a href="https://gutena.io/how-to-generate-google-recaptcha-site-key-and-secret-key" target="_blank">{ __( 'reCAPTCHA', 'gutena-forms' ) }</a> { __( ' v3 and v2 help you protect your sites from fraudulent activities, spam, and abuse. By using this integration in your forms, you can block spam form submissions.', 'gutena-forms' ) } </p>

					<ToggleControl
						label={ __( 'Enable', 'gutena-forms' ) }
						checked={ recaptcha?.enable }
						onChange={ ( recaptcha_status ) =>
							setAttributes( { recaptcha:{
								...recaptcha,
								enable:recaptcha_status
							} } )
						}
					/>	
					{ ( ! gfIsEmpty( recaptcha?.enable ) && recaptcha?.enable ) &&
					<>
						<RadioControl
							className="gutena-forms-horizontal-radio"
							label={ __( 'reCAPTCHA Type', 'gutena-forms' ) }
							selected={ recaptcha?.type }
							options={ [
								{ label: 'v2', value: 'v2' },
								{ label: 'v3', value: 'v3' },
							] }
							onChange={ ( type ) =>
								setAttributes( { recaptcha:{
									...recaptcha,
									type
								} } )
							}
						/>
					
						<TextControl
							label={ __( 'Site Key', 'gutena-forms' ) }
							value={ recaptcha?.site_key }
							onChange={ ( site_key ) =>
								setAttributes( { recaptcha:{
									...recaptcha,
									site_key
								} } )
							}
						/>
						<TextControl
							label={ __( 'Secret key', 'gutena-forms' ) }
							value={ recaptcha?.secret_key }
							onChange={ ( secret_key ) =>
								setAttributes( { recaptcha:{
									...recaptcha,
									secret_key
								} } )
							}
						/>
					</>
					}
					</VStack>
				</PanelBody>
				<PanelColorSettings
					title={ __( 'Form colors', 'gutena-forms' ) }
					colorSettings={ [
						{
							value: labelColor,
							onChange: ( labelColor ) =>
								setAttributes( { labelColor } ),
							label: __( 'Label color', 'gutena-forms' ),
							disableCustomColors: false,
						},
						{
							value: placeholderColor,
							onChange: ( placeholderColor ) =>
								setAttributes( { placeholderColor } ),
							label: __( 'Placeholder color', 'gutena-forms' ),
							disableCustomColors: false,
						},
						{
							value: inputBgColor,
							onChange: ( inputBgColor ) =>
								setAttributes( { inputBgColor } ),
							label: __(
								'Input box background color',
								'gutena-forms'
							),
							disableCustomColors: false,
						},
						{
							value: inputBorderColor,
							onChange: ( inputBorderColor ) =>
								setAttributes( { inputBorderColor } ),
							label: __(
								'Input box border color',
								'gutena-forms'
							),
							disableCustomColors: false,
						},
						{
							value: inputFocusBorderColor,
							onChange: ( inputFocusBorderColor ) =>
								setAttributes( { inputFocusBorderColor } ),
							label: __(
								'Input box on focus border color',
								'gutena-forms'
							),
							disableCustomColors: false,
						},
					] }
					enableAlpha={ true }
				/>
				{ showLabel ? (
					<ToolsPanel
						label={ __( 'Label typography', 'gutena-forms' ) }
						resetAll={ () =>
							setAttributes( {
								labelTypography: {
									...labelTypography,
									fontSize: undefined,
									lineHeight: undefined,
									fontWeight: undefined,
									fontFamily: undefined,
								},
							} )
						}
					>
						<ToolsPanelItem
							hasValue={ () => !! labelTypography?.fontFamily }
							label={ __( 'Font Family' ) }
							onDeselect={ () =>
								setAttributes( {
									labelTypography: {
										...labelTypography,
										fontFamily: undefined,
									},
								} )
							}
						>
						<FontFamilyControl
							value={ labelTypography?.fontFamily }
							onChange={ ( fontFamily ) =>
								setAttributes( {
									labelTypography: {
										...labelTypography,
										fontFamily: fontFamily,
									},
								} )
							}
							size="__unstable-large"
							__nextHasNoMarginBottom
						/>
						</ToolsPanelItem>

						<ToolsPanelItem
							hasValue={ () => !! labelTypography?.fontSize }
							label={ __( 'Font size' ) }
							onDeselect={ () =>
								setAttributes( {
									labelTypography: {
										...labelTypography,
										fontSize: undefined,
									},
								} )
							}
						>
							<FontSizePicker
								onChange={ ( fontSize ) =>
									setAttributes( {
										labelTypography: {
											...labelTypography,
											fontSize: fontSize,
										},
									} )
								}
								value={ labelTypography?.fontSize }
								withReset={ false }
							/>
						</ToolsPanelItem>

						<ToolsPanelItem
							className="single-column"
							hasValue={ () => !! labelTypography?.lineHeight }
							label={ __( 'Line height' ) }
							onDeselect={ () =>
								setAttributes( {
									labelTypography: {
										...labelTypography,
										lineHeight: undefined,
									},
								} )
							}
						>
							<LineHeightControl
								__unstableInputWidth="100%"
								__nextHasNoMarginBottom={ true }
								value={ labelTypography?.lineHeight }
								onChange={ ( lineHeight ) =>
									setAttributes( {
										labelTypography: {
											...labelTypography,
											lineHeight: lineHeight,
										},
									} )
								}
							/>
						</ToolsPanelItem>

						<ToolsPanelItem
							hasValue={ () => !! labelTypography?.fontWeight }
							label={ __( 'Font weight' ) }
							onDeselect={ () =>
								setAttributes( {
									labelTypography: {
										...labelTypography,
										fontWeight: undefined,
									},
								} )
							}
						>
							<RangeControl
								label={ __( 'Font weight', 'gutena-forms' ) }
								value={ labelTypography?.fontWeight }
								onChange={ ( fontWeight ) =>
									setAttributes( {
										labelTypography: {
											...labelTypography,
											fontWeight: fontWeight,
										},
									} )
								}
								min={ 100 }
								max={ 900 }
								step={ 100 }
							/>
						</ToolsPanelItem>
					</ToolsPanel>
				) : (
					''
				) }
				<ToolsPanel
					label={ __( 'Placeholder typography', 'gutena-forms' ) }
					resetAll={ () =>
						setAttributes( {
							placeholderTypography: {
								...placeholderTypography,
								fontSize: undefined,
								lineHeight: undefined,
								fontWeight: undefined,
								fontFamily: undefined,
							},
						} )
					}
				>
					<ToolsPanelItem
							hasValue={ () => !! placeholderTypography?.fontFamily }
							label={ __( 'Font Family' ) }
							onDeselect={ () =>
								setAttributes( {
									placeholderTypography: {
										...placeholderTypography,
										fontFamily: undefined,
									},
								} )
							}
						>
						<FontFamilyControl
							value={ placeholderTypography?.fontFamily }
							onChange={ ( fontFamily ) =>
								setAttributes( {
									placeholderTypography: {
										...placeholderTypography,
										fontFamily: fontFamily,
									},
								} )
							}
							size="__unstable-large"
							__nextHasNoMarginBottom
						/>
					</ToolsPanelItem>

					<ToolsPanelItem
						hasValue={ () => !! placeholderTypography?.fontSize }
						label={ __( 'Font size' ) }
						onDeselect={ () =>
							setAttributes( {
								placeholderTypography: {
									...placeholderTypography,
									fontSize: undefined,
								},
							} )
						}
					>
						<FontSizePicker
							onChange={ ( fontSize ) =>
								setAttributes( {
									placeholderTypography: {
										...placeholderTypography,
										fontSize: fontSize,
									},
								} )
							}
							value={ placeholderTypography?.fontSize }
							withReset={ false }
						/>
					</ToolsPanelItem>

					<ToolsPanelItem
						className="single-column"
						hasValue={ () => !! placeholderTypography?.lineHeight }
						label={ __( 'Line height' ) }
						onDeselect={ () =>
							setAttributes( {
								placeholderTypography: {
									...placeholderTypography,
									lineHeight: undefined,
								},
							} )
						}
					>
						<LineHeightControl
							__unstableInputWidth="100%"
							__nextHasNoMarginBottom={ true }
							value={ placeholderTypography?.lineHeight }
							onChange={ ( lineHeight ) =>
								setAttributes( {
									placeholderTypography: {
										...placeholderTypography,
										lineHeight: lineHeight,
									},
								} )
							}
						/>
					</ToolsPanelItem>

					<ToolsPanelItem
						hasValue={ () => !! placeholderTypography?.fontWeight }
						label={ __( 'Font weight' ) }
						onDeselect={ () =>
							setAttributes( {
								placeholderTypography: {
									...placeholderTypography,
									fontWeight: undefined,
								},
							} )
						}
					>
						<RangeControl
							label={ __( 'Font weight', 'gutena-forms' ) }
							value={ placeholderTypography?.fontWeight }
							onChange={ ( fontWeight ) =>
								setAttributes( {
									placeholderTypography: {
										...placeholderTypography,
										fontWeight: fontWeight,
									},
								} )
							}
							min={ 100 }
							max={ 900 }
							step={ 100 }
						/>
					</ToolsPanelItem>
				</ToolsPanel>
				<PanelBody title="Input settings" className="gutena-forms-panel" initialOpen={ false }>
					<PanelRow>
						<fieldset className="components-border-radius-control">
							<legend>
								{ __( 'Label gap', 'gutena-forms' ) }
							</legend>
							<div className="components-border-radius-control__wrapper">
								<RangeControl
									className="components-border-radius-control__range-control"
									value={ getQtyOrunit(
										inputLabelGap,
										'Qty'
									) }
									withInputField={ false }
									onChange={ ( qty ) => {
										setAttributes( {
											inputLabelGap:
												qty +
												getQtyOrunit( inputLabelGap ),
										} );
									} }
									min={ 0 }
									max={
										MAX_SPACE_VALUES[
											getQtyOrunit( inputLabelGap )
										]
									}
									step={ 1 }
								/>
								<UnitControl
									className="components-border-radius-control__unit-control"
									units={ units }
									value={ inputLabelGap }
									onChange={ ( inputLabelGap ) => {
										setAttributes( { inputLabelGap } );
									} }
								/>
							</div>
						</fieldset>
					</PanelRow>
					<PanelRow>
						<fieldset className="components-border-radius-control">
							<legend>
								{ __( 'Border width', 'gutena-forms' ) }
							</legend>
							<div className="components-border-radius-control__wrapper">
								<RangeControl
									className="components-border-radius-control__range-control"
									value={ getQtyOrunit(
										inputBorderWidth,
										'Qty'
									) }
									withInputField={ false }
									onChange={ ( qty ) => {
										setAttributes( {
											inputBorderWidth:
												qty +
												getQtyOrunit(
													inputBorderWidth
												),
										} );
									} }
									min={ 1 }
									max={
										MAX_SPACE_VALUES[
											getQtyOrunit( inputBorderWidth )
										]
									}
									step={ 1 }
								/>
								<UnitControl
									className="components-border-radius-control__unit-control"
									units={ units }
									value={ inputBorderWidth }
									onChange={ ( inputBorderWidth ) => {
										setAttributes( { inputBorderWidth } );
									} }
								/>
							</div>
						</fieldset>
					</PanelRow>
					<PanelRow>
						<fieldset className="components-border-radius-control">
							<legend>
								{ __( 'Border radius', 'gutena-forms' ) }
							</legend>
							<div className="components-border-radius-control__wrapper">
								<RangeControl
									className="components-border-radius-control__range-control"
									value={ getQtyOrunit(
										inputBorderRadius,
										'Qty'
									) }
									withInputField={ false }
									onChange={ ( qty ) =>
										setAttributes( {
											inputBorderRadius:
												qty +
												getQtyOrunit(
													inputBorderRadius
												),
										} )
									}
									min={ 0 }
									max={
										MAX_SPACE_VALUES[
											getQtyOrunit( inputBorderRadius )
										]
									}
									step={ 1 }
								/>
								<UnitControl
									className="components-border-radius-control__unit-control"
									units={ units }
									value={ inputBorderRadius }
									onChange={ ( inputBorderRadius ) => {
										setAttributes( { inputBorderRadius } );
									} }
								/>
							</div>
						</fieldset>
					</PanelRow>
					<PanelRow>
						<ToggleControl
							label={ __( 'Bottom border only', 'gutena-forms' ) }
							help={
								inputBottomBorderOnly
									? __(
											'Toggle to show all side border',
											'gutena-forms'
									  )
									: __(
											'Toggle to show show only bottom border',
											'gutena-forms'
									  )
							}
							checked={ inputBottomBorderOnly }
							onChange={ ( inputBottomBorderOnly ) =>
								setAttributes( { inputBottomBorderOnly } )
							}
						/>
					</PanelRow>
				</PanelBody>
				<PanelBody title="Notification" initialOpen={ true }>
					<PanelRow>
						<ToggleControl
							label={ __( 'Admin notification', 'gutena-forms' ) }
							help={
								emailNotifyAdmin
									? __(
											'Toggle to stop email notification',
											'gutena-forms'
									  )
									: __(
											'Toggle to enable email notification after form submission',
											'gutena-forms'
									  )
							}
							checked={ emailNotifyAdmin }
							onChange={ ( emailNotifyAdmin ) =>
								setAttributes( { emailNotifyAdmin } )
							}
						/>
					</PanelRow>
					{ emailNotifyAdmin ? (
						<>
							<PanelRow>
								<TextControl
									label={ __( 'Email to', 'gutena-forms' ) }
									value={ adminEmails }
									onChange={ ( adminEmails ) =>
										setAttributes( { adminEmails } )
									}
								/>
							</PanelRow>
							<PanelRow>
								<TextControl
									label={ __(
										'Email subject',
										'gutena-forms'
									) }
									value={ adminEmailSubject }
									onChange={ ( adminEmailSubject ) =>
										setAttributes( { adminEmailSubject } )
									}
								/>
							</PanelRow>
						</>
					) : (
						''
					) }
				</PanelBody>
				<PanelBody title="Confirmation" initialOpen={ true }>
					<PanelRow>
						<SelectControl
							label="Action"
							value={ afterSubmitAction }
							options={ [
								{
									label: __( 'Message', 'gutena-forms' ),
									value: 'message',
								},
								{
									label: __( 'Send to URL', 'gutena-forms' ),
									value: 'redirect_url',
								},
							] }
							onChange={ ( afterSubmitAction ) =>
								setAttributes( { afterSubmitAction } )
							}
							help={ __(
								'Confirmation and error message are available for edit at the bottom of the form',
								'gutena-forms'
							) }
							__nextHasNoMarginBottom
						/>
					</PanelRow>
					{ 'redirect_url' === afterSubmitAction ? (
						<PanelRow>
							<TextControl
								type="url"
								label={ __( 'Send to URL', 'gutena-forms' ) }
								value={ redirectUrl }
								onChange={ ( redirectUrl ) =>
									setAttributes( { redirectUrl } )
								}
							/>
						</PanelRow>
					) : (
						''
					) }
					{ 'message' === afterSubmitAction ? (
						<PanelRow>
							<ToggleControl
								label={ __(
									'Hide form after submission',
									'gutena-forms'
								) }
								help={
									afterSubmitHide
										? __(
												'Toggle to not hide form',
												'gutena-forms'
										  )
										: __(
												'Toggle to hide form',
												'gutena-forms'
										  )
								}
								checked={ afterSubmitHide }
								onChange={ ( afterSubmitHide ) =>
									setAttributes( { afterSubmitHide } )
								}
							/>
						</PanelRow>
					) : (
						''
					) }
				</PanelBody>
			</InspectorControls>
			{ hasInnerBlocks ? (
				<form method="post" { ...blockProps }>
					<input type="hidden" name="formid" value={ formID } />
					<InnerBlocks
						template={ TEMPLATE }
						allowedBlocks={ ALLOWED_BLOCKS }
					/>
				</form>
			) : (
				<Placeholder { ...props } />
			) }
		</>
	);
}
