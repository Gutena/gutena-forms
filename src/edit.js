import { __ } from '@wordpress/i18n';
import { get } from 'lodash';
import { useEffect } from '@wordpress/element';
import { gfIsEmpty, getInnerBlocksbyNameAttr, slugToName } from './helper';
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
	useSettings,
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
import RangeControlUnit from './components/RangeControlUnit';
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
		emailFromName,
		adminEmails,
		replyToEmail,
		replyToName,
		replyToLastName,
		adminEmailSubject,
		emailNotifyAdmin,
		emailNotifyUser,
		messages={},
		formStyle,
		style,
		recaptcha,
		cloudflareTurnstile
	} = attributes;

	const {
		getClientIdsOfDescendants,
		getBlock
	} = useSelect( blockEditorStore );

	/**
	 * Returns an array of font family names from a given object.
	 * Supports the legacy format of theme and custom properties, as well as the new format of a single array.
	 *
	 * @param {Object} fontFamilies - An object containing the font families.
	 * @return {Array} An array of font family names.
	 */
	const getFontFamiliesList = ( fontFamilies ) => {
		if ( gfIsEmpty( fontFamilies ) ) {
			return [];
		}

		if ( ! Array.isArray( fontFamilies ) ) {
			/** https://github.com/WordPress/gutenberg/pull/59846 **/
			const { theme, custom } = fontFamilies;
			fontFamilies = theme !== undefined ? theme : [];
			if ( custom !== undefined ) {
				fontFamilies = [ ...fontFamilies, ...custom ];
			}
		}

		if ( gfIsEmpty( fontFamilies ) || 0 == fontFamilies.length ) {
			return [];
		}

		return fontFamilies;
	}

	const [ fontFamilies ] = useSettings( 'typography.fontFamilies' );
	const fontFamiliesList = getFontFamiliesList( fontFamilies );
	const hasfontFamilies = 0 < fontFamiliesList.length;

	const getEmailFields = () => {
		let emailOptions = [
			{ label: __( 'Select', 'gutena-forms' ), value: '' }
		];
		const blocks = getBlock( clientId );
		if ( ! gfIsEmpty( blocks ) ) {
			let emailFields = getInnerBlocksbyNameAttr( blocks.innerBlocks, 'gutena/form-field', 'fieldType', 'email' );
			if ( 0 < emailFields.length  ) {
				for (let i = 0; i < emailFields.length; i++) {
					let emailAttr = emailFields[i].attributes;
					if ( 'email' === emailAttr.fieldType && ! gfIsEmpty( emailAttr.nameAttr ) ) {
						emailOptions.push({ label: emailAttr.fieldName, value: emailAttr.nameAttr });
					}
				}
			}
		}
		return emailOptions;
	}

	const getTextFields = () => {
		let textOptions = [
			{ label: __( 'Select', 'gutena-forms' ), value: '' }
		];
		const blocks = getBlock( clientId );
		if ( ! gfIsEmpty( blocks ) ) {
			let textFields = getInnerBlocksbyNameAttr( blocks.innerBlocks, 'gutena/form-field', 'fieldType', 'text' );
			if ( 0 < textFields.length  ) {
				for (let i = 0; i < textFields.length; i++) {
					let fieldAttr = textFields[i].attributes;
					if ( 'text' === fieldAttr.fieldType && ! gfIsEmpty( fieldAttr.nameAttr ) ) {
						textOptions.push({ label: fieldAttr.fieldName, value: fieldAttr.nameAttr });
					}
				}
			}
		}
		return textOptions;
	}

	//Set Form ID
	useEffect( () => {
		let shouldRunFormID = true;
		//set formID
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

			//set recaptcha and formID if not set initially as per data available
			if ( ! gfIsEmpty( recaptcha ) && gfIsEmpty( recaptcha.secret_key ) && ! gfIsEmpty( gutenaFormsBlock ) && ! gfIsEmpty( gutenaFormsBlock.grecaptcha_type ) && ! gfIsEmpty( gutenaFormsBlock.grecaptcha_site_key ) && ! gfIsEmpty( gutenaFormsBlock.grecaptcha_secret_key ) ) {
				setAttributes( {
					recaptcha: {
						...recaptcha,
						type: gutenaFormsBlock.grecaptcha_type,
						site_key: gutenaFormsBlock.grecaptcha_site_key,
						secret_key: gutenaFormsBlock.grecaptcha_secret_key,
					},
					formID: GutenaFormsID
				} );
			} else {
				setAttributes( { formID: GutenaFormsID } );
			}
		}
		//set replyToEmailID
		if ( shouldRunFormID && gfIsEmpty( replyToEmail ) && gfIsEmpty( replyToName ) ) {
			let emailOptions = getEmailFields();
			let textOptions = getTextFields();
			if ( 1 < emailOptions.length && ! gfIsEmpty( emailOptions[1].value ) && 1 < textOptions.length && ! gfIsEmpty( textOptions[1].value ) ) {
				setAttributes( {
					replyToEmail: emailOptions[1].value,
					replyToName: textOptions[1].value
				} );
			}
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
					<TextControl
						label={ __( 'Form name', 'gutena-forms' ) }
						value={ formName }
						onChange={ ( formName ) =>
							setAttributes( { formName } )
						}
					/>
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
					<p ><span className="block-editor-block-card__title" >{ __( 'Note : ', 'gutena-forms' ) }</span>
					<span className="gf-text-muted" >
						<span>
							{ __( 'To reuse this form, please make it a ', 'gutena-forms' ) }
						</span>
						<a href="https://gutena.io/reuse-gutena-forms-on-multiple-pages" target="_blank">
							{ __( 'Synced Patterns', 'gutena-forms' ) }
						</a>
						<span>
							{ __( ' ( Reusable Block ). Avoid copying or duplicating this block.', 'gutena-forms' ) }
						</span>
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

				{/* Cloudflare - Turnstile start */}
				<PanelBody title="Cloudflare Turnstile" initialOpen={ false }>
					<VStack>
						<p>
							<a>Cloudflare Turnstile</a>
						</p>

						<ToggleControl
							label={ 'Enable' }
							checked={ cloudflareTurnstile?.enable }
							onChange={ ( turnstile_status ) =>
								setAttributes( { cloudflareTurnstile:{
									...cloudflareTurnstile,
									enable:turnstile_status
								} } )
							}
						/>

						{
							( ! gfIsEmpty( cloudflareTurnstile?.enable ) && cloudflareTurnstile?.enable ) &&
							<>
								<TextControl
									label={ __( 'Site Key', 'gutena-forms' ) }
									value={ cloudflareTurnstile?.site_key }
									onChange={ ( site_key ) =>
										setAttributes( { cloudflareTurnstile:{
											...cloudflareTurnstile,
											site_key
										} } )
									}
								/>

								<TextControl
									label={ __( 'Secret Key', 'gutena-forms' ) }
									value={ cloudflareTurnstile?.secret_key }
									onChange={ ( secret_key ) =>
										setAttributes( { cloudflareTurnstile:{
											...cloudflareTurnstile,
											secret_key
										} } )
									}
								/>
							</>
						}

					</VStack>
				</PanelBody>
				{/* Cloudflare - Turnstile end */}

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
						{
							hasfontFamilies && (
								<FontFamilyControl
									fontFamilies={ fontFamiliesList }
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
							)
						}
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
								__nextHasNoMarginBottom
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
						{
							hasfontFamilies && (
								<FontFamilyControl
									fontFamilies={ fontFamiliesList }
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
							)
						}
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
							__nextHasNoMarginBottom
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
					<RangeControlUnit
                        rangeLabel={ __("Label gap", "gutena-forms")  }
                        attrValue={ inputLabelGap }
                        onChangeFunc={ ( inputLabelGap ) => setAttributes( { inputLabelGap } ) }
                        rangeMin={ 0 }
                        rangeMax={ {
                            px: 200,
                            em: 10,
                            rem: 5,
                        } }
                        rangeStep={ 1 }
                    />

					<RangeControlUnit
                        rangeLabel={ __("Border width", "gutena-forms")  }
                        attrValue={ inputBorderWidth }
                        onChangeFunc={ ( inputBorderWidth ) => setAttributes( { inputBorderWidth } ) }
                        rangeMin={ 1 }
                        rangeMax={ {
                            px: 200,
                            em: 10,
                            rem: 5,
                        } }
                        rangeStep={ 1 }
                    />

					<RangeControlUnit
                        rangeLabel={ __("Border radius", "gutena-forms")  }
                        attrValue={ inputBorderRadius }
                        onChangeFunc={ ( inputBorderRadius ) => setAttributes( { inputBorderRadius } ) }
                        rangeMin={ 0 }
                        rangeMax={ {
                            px: 200,
                            em: 10,
                            rem: 5,
                        } }
                        rangeStep={ 1 }
                    />

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
					<TextControl
						label={ __( 'From Name', 'gutena-forms' ) }
						value={ emailFromName }
						onChange={ ( emailFromName ) =>
							setAttributes( { emailFromName } )
						}
					/>
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
					{ emailNotifyAdmin ? (
						<>
							<TextControl
								label={ __( 'Email to', 'gutena-forms' ) }
								value={ adminEmails }
								onChange={ ( adminEmails ) =>
									setAttributes( { adminEmails } )
								}
							/>

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

							<SelectControl
								label={ __( 'Reply To Email', 'gutena-forms' ) }
								value={ replyToEmail }
								options={ getEmailFields() }
								onChange={ ( replyToEmail ) =>
									setAttributes( { replyToEmail } )
								}
								help={ __(
									'Select email field for reply to address',
									'gutena-forms'
								) }
								__nextHasNoMarginBottom
							/>

							<SelectControl
								label={ __( 'Reply To Name ( First Name )', 'gutena-forms' ) }
								value={ replyToName }
								options={ getTextFields() }
								onChange={ ( replyToName ) =>
									setAttributes( { replyToName } )
								}
								help={ __(
									'Select first or full name field for reply to address',
									'gutena-forms'
								) }
								__nextHasNoMarginBottom
							/>
							<SelectControl
								label={ __( 'Reply To Name ( Last Name )', 'gutena-forms' ) }
								value={ replyToLastName }
								options={ getTextFields() }
								onChange={ ( replyToLastName ) =>
									setAttributes( { replyToLastName } )
								}
								help={ __(
									'Select last name field for reply to address',
									'gutena-forms'
								) }
								__nextHasNoMarginBottom
							/>

						</>
					) : (
						''
					) }
				</PanelBody>
				<PanelBody title={__( 'Confirmation', 'gutena-forms' ) } initialOpen={ true }>
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
				<PanelBody title={__( 'Messages', 'gutena-forms' ) } initialOpen={ false }>
					<TextControl
						type="text"
						label={ __( 'Required Field', 'gutena-forms' ) }
						value={ gfIsEmpty( messages?.required_msg ) ? '' : messages?.required_msg }
						onChange={ ( required_msg ) =>
							setAttributes( { messages:{
								...messages,
								required_msg
							} } )
						}
						placeholder={ gutenaFormsBlock?.required_msg }
					/>
					<TextControl
						type="text"
						label={ __( 'Required Select Field', 'gutena-forms' ) }
						value={ gfIsEmpty( messages?.required_msg_select ) ? '' : messages?.required_msg_select }
						onChange={ ( required_msg_select ) =>
							setAttributes( { messages:{
								...messages,
								required_msg_select
							} } )
						}
						placeholder={ gutenaFormsBlock?.required_msg_select }
					/>
					<TextControl
						type="text"
						label={ __( 'Required Checkbox or Radio Field', 'gutena-forms' ) }
						value={ gfIsEmpty( messages?.required_msg_check ) ? '' : messages?.required_msg_check }
						onChange={ ( required_msg_check ) =>
							setAttributes( { messages:{
								...messages,
								required_msg_check
							} } )
						}
						placeholder={ gutenaFormsBlock?.required_msg_check }
					/>
					<TextControl
						type="text"
						label={ __( 'Required Opt-in checkbox', 'gutena-forms' ) }
						value={ gfIsEmpty( messages?.required_msg_optin ) ? '' : messages?.required_msg_optin }
						onChange={ ( required_msg_optin ) =>
							setAttributes( { messages:{
								...messages,
								required_msg_optin
							} } )
						}
						help={ __( 'Privacy policy, Terms', 'gutena-forms' ) }
						placeholder={ gutenaFormsBlock?.required_msg_optin }
					/>
					<TextControl
						type="text"
						label={ __( 'Invalid Email', 'gutena-forms' ) }
						value={ gfIsEmpty( messages?.invalid_email_msg ) ? '' : messages?.invalid_email_msg }
						onChange={ ( invalid_email_msg ) =>
							setAttributes( { messages:{
								...messages,
								invalid_email_msg
							} } )
						}
						placeholder={ gutenaFormsBlock?.invalid_email_msg }
					/>
					<TextControl
						type="text"
						label={ __( 'Minimum value', 'gutena-forms' ) }
						value={ gfIsEmpty( messages?.min_value_msg ) ? '' : messages?.min_value_msg }
						onChange={ ( min_value_msg ) =>
							setAttributes( { messages:{
								...messages,
								min_value_msg
							} } )
						}
						placeholder={ gutenaFormsBlock?.min_value_msg }
					/>
					<TextControl
						type="text"
						label={ __( 'Maximum value', 'gutena-forms' ) }
						value={ gfIsEmpty( messages?.max_value_msg ) ? '' : messages?.max_value_msg }
						onChange={ ( max_value_msg ) =>
							setAttributes( { messages:{
								...messages,
								max_value_msg
							} } )
						}
						placeholder={ gutenaFormsBlock?.max_value_msg }
					/>
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
