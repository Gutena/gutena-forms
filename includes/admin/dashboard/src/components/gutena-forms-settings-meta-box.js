import { useEffect, useLayoutEffect, useRef, useState } from '@wordpress/element';
import {NavLink, useParams} from 'react-router';
import GutenaFormsNumberField from './fields/gutena-forms-number-field';
import GutenaFormsToggleField from './fields/gutena-forms-toggle-field';
import GutenaFormsEmailField from './fields/gutena-forms-email-field';
import GutenaFormsSubmitButton from './fields/gutena-forms-submit-button';
import GutenaFormsTextField from './fields/gutena-forms-text-field';
import GutenaFormsTextareaField from './fields/gutena-forms-textarea-field';
import GutenaFormsHtmlEditorField from './fields/gutena-forms-html-editor-field';
import GutenaFormsMergeTagsField from './fields/gutena-forms-merge-tags-field';
import GutenaFormsRadioGroup from './fields/gutena-forms-radio-group';
import GutenaFormsSelectField from './fields/gutena-forms-select-field';
import GutenaFormsUrlField from './fields/gutena-forms-url-field';
import { gutenaFormsUpdateSettings } from "../api";
import { toast } from 'react-toastify';
import { __ } from '@wordpress/i18n';
import { SettingsTemplates, FieldTemplates } from '../utils/templates';
import GutenaFormsProBadge from './gutena-forms-pro-badge';
import Activecampaign from '../icons/activecampaign';
import Brevo from '../icons/brevo';
import Mailchimp from '../icons/mailchimp';
import Recaptcha from "../icons/recaptcha";
import Cloudflare from "../icons/cloudflare";

const GutenaFormsSettingsMetaBox = ( { id, title, description, items, isPro = false, onClick, goBack } ) => {
	const { settings_id } = useParams();
	const [ settings, setSettings ] = useState( false );
	const [ fieldValue, setFieldValue ] = useState( {} );
	const [ loading, setLoading ] = useState( true );
	const [ template, setTemplate ] = useState( false );
	const [ activeMergeField, setActiveMergeField ] = useState( 'subject' );
	const pendingMergeCursor = useRef( null );
	const mergeEditorRefs = useRef( {} );

	const getFieldLabel = ( field ) => {
		if ( field?.attrs?.required ) {
			return `${ field.label } *`;
		}
		return field.label;
	};

	const buildTagItemsFromTags = ( tags = [] ) =>
		tags.map( ( tag ) => ( {
			label: tag
				.replace( /^\{|\}$/g, '' )
				.replace( /[-_]/g, ' ' )
				.replace( /\b\w/g, ( char ) => char.toUpperCase() ),
			tag,
		} ) );

	const isValidFromEmail = ( emailValue, mergeTags = [] ) => {
		const trimmed = String( emailValue || '' ).trim();
		if ( '' === trimmed ) {
			return true;
		}
		if ( mergeTags.includes( trimmed ) ) {
			return true;
		}
		return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( trimmed );
	};

	useLayoutEffect( () => {
		const pending = pendingMergeCursor.current;
		if ( ! pending ) {
			return;
		}

		const element = document.getElementById( pending.field );
		if ( element ) {
			element.focus();
			element.setSelectionRange( pending.pos, pending.pos );
		}

		pendingMergeCursor.current = null;
	}, [ fieldValue ] );

	useEffect(
		() => {
			setLoading( true );
			const parsedSettings = [];
			const initialFieldValue = {};
			setTemplate( false );

			items.forEach( ( item, id ) => {
				if ( 'template' === item.type ) {
					setTemplate( item.name );
				} else if ( 'field-template' === item.type ) {
					parsedSettings.push( { id, ...item } );
				} else if ( 'merge-tags' === item.type ) {
					parsedSettings.push( {
						id: item.id,
						type: item.type,
						label: item.name,
						desc: item.desc,
						attrs: item.attrs || {},
					} );
				} else {
					// Use ?? so boolean false (e.g. enable off) is kept — `||` dropped it and left fields editable on first load.
					initialFieldValue[ item.id ] = item.value ?? item.default;
					parsedSettings.push( {
						id: item.id,
						type: item.type,
						label: item.name,
						desc: item.desc,
						attrs: item.attrs || {},
					} );
				}
			} );

			setSettings( parsedSettings );
			setFieldValue( initialFieldValue );
			setLoading( false );
		},
		[ items ] );

	const handleFieldChange = ( id, newValue ) => {
		setFieldValue( ( prevValue ) => ( {
			...prevValue,
			[ id ]: newValue,
		} ) );
	};

	const insertMergeTag = ( tag ) => {
		const targetField = activeMergeField || 'message';

		if ( mergeEditorRefs.current[ targetField ] ) {
			mergeEditorRefs.current[ targetField ]( tag );
			return;
		}

		const currentValue = fieldValue?.[ targetField ] || '';
		const element = document.getElementById( targetField );
		const start = element && typeof element.selectionStart === 'number'
			? element.selectionStart
			: currentValue.length;
		const end = element && typeof element.selectionEnd === 'number'
			? element.selectionEnd
			: start;
		const nextValue = `${ currentValue.slice( 0, start ) }${ tag }${ currentValue.slice( end ) }`;

		pendingMergeCursor.current = {
			field: targetField,
			pos: start + tag.length,
		};
		handleFieldChange( targetField, nextValue );
	};

	const shouldRenderField = ( fieldId ) => {
		const isRecaptchaSettings = 'recaptcha' === id || 'google-recaptcha' === settings_id;
		if ( isRecaptchaSettings ) {
			const recaptchaType = fieldValue?.type || 'v2';
			if ( fieldId.startsWith( 'v2_' ) ) {
				return 'v2' === recaptchaType;
			}

			if ( fieldId.startsWith( 'v3_' ) ) {
				return 'v3' === recaptchaType;
			}

			return true;
		}

		if ( 'form-confirmation' === settings_id ) {
			const confirmationType = fieldValue?.confirmation_type || 'message';
			const redirectType = fieldValue?.redirect_type || 'page';

			if ( 'confirmation_type' === fieldId || 'submit_button' === fieldId ) {
				return true;
			}

			if ( 'merge-tags' === fieldId ) {
				return 'message' === confirmationType;
			}

			if ( [ 'success_message', 'error_message', 'after_submit' ].includes( fieldId ) ) {
				return 'message' === confirmationType;
			}

			if ( 'redirect_type' === fieldId ) {
				return 'redirect' === confirmationType;
			}

			if ( 'redirect_page_id' === fieldId ) {
				return 'redirect' === confirmationType && 'page' === redirectType;
			}

			if ( 'redirect_url' === fieldId ) {
				return 'redirect' === confirmationType && 'custom_url' === redirectType;
			}
		}

		return true;
	}

	const handleSubmit = () => {
		if ( 'auto-responder' === settings_id ) {
			if ( ! fieldValue?.send_email_to?.trim() ) {
				toast.error( __( 'Send Email To is required.', 'gutena-forms' ) );
				return;
			}

			if ( ! fieldValue?.subject?.trim() ) {
				toast.error( __( 'Subject is required.', 'gutena-forms' ) );
				return;
			}

			if (
				fieldValue?.from_email?.trim() &&
				! isValidFromEmail(
					fieldValue.from_email,
					[ '{admin_email}' ]
				)
			) {
				toast.error( __( 'Please enter a valid From Email address.', 'gutena-forms' ) );
				return;
			}
		}

		gutenaFormsUpdateSettings( settings_id, fieldValue )
			.then( () => {
				toast.success(
					__( 'Settings updated successfully.', 'gutena-forms' )
				);
			} )
	};

	const getFieldDisabledState = ( field ) => {
		if ( ! field || 'toggle' === field.type || 'submit' === field.type ) {
			return false;
		}

		const dependsOn = field?.attrs?.depends_on;
		const dependsValue = field?.attrs?.depends_value ?? true;

		if ( dependsOn ) {
			return fieldValue?.[ dependsOn ] !== dependsValue;
		}

		const hasDefaultController = (
			typeof fieldValue?.enable !== 'undefined' ||
			typeof fieldValue?.enabled !== 'undefined'
		);

		if ( ! hasDefaultController ) {
			return false;
		}

		const isEnabled = fieldValue?.enable ?? fieldValue?.enabled ?? true;
		return ! isEnabled;
	}

	const renderSettingsField = ( field ) => {
		let fieldElement;
		const isDisabled = getFieldDisabledState( field );

		switch ( field.type ) {
			case 'toggle':
				fieldElement = (
					<GutenaFormsToggleField
						id={ field.id }
						label={ field.label }
						desc={ field.desc }
						checked={ fieldValue[ field.id ] }
						onChange={ ( newValue ) => handleFieldChange( field.id, newValue ) }
					/>
				);
				break;

			case 'number':
				fieldElement = (
					<GutenaFormsNumberField
						id={ field.id }
						label={ field.label }
						desc={ field.desc }
						value={ fieldValue[ field.id ] }
						onChange={ ( newValue ) => handleFieldChange( field.id, newValue ) }
						min={ field.attrs.min }
						max={ field.attrs.max }
						step={ field.attrs.step }
						disabled={ isDisabled }
					/>
				);
				break;

			case 'email':
				fieldElement = (
					<GutenaFormsEmailField
						id={ field.id }
						label={ getFieldLabel( field ) }
						desc={ field.desc }
						value={ fieldValue[ field.id ] }
						onChange={ ( newValue ) => handleFieldChange( field.id, newValue ) }
						onFocus={ field.attrs?.merge_tag_field ? () => setActiveMergeField( field.id ) : undefined }
						disabled={ isDisabled }
						multiple={ !! field.attrs?.multiple }
						allowMergeTags={ !! field.attrs?.allow_merge_tags }
						mergeTags={ field.attrs?.merge_tags || [] }
						showValidation={ !! field.attrs?.allow_merge_tags }
					/>
				);
				break;

			case 'submit':
				fieldElement = (
					<GutenaFormsSubmitButton
						label={ field.label }
						onClick={ handleSubmit }
					/>
				);
				break;

			case 'text':
				fieldElement = (
					<GutenaFormsTextField
						id={ field.id }
						label={ getFieldLabel( field ) }
						desc={ field.desc }
						value={ fieldValue[ field.id ] }
						onChange={ ( newValue ) => handleFieldChange( field.id, newValue ) }
						onFocus={ field.attrs?.merge_tag_field ? () => setActiveMergeField( field.id ) : undefined }
						placeholder={ field.attrs.placeholder }
						disabled={ isDisabled }
					/>
				);
				break;

			case 'html-editor': {
				const editorMergeTags = field.attrs?.merge_tags;
				const resolvedTagItems = editorMergeTags && editorMergeTags.length
					? editorMergeTags
					: ( settings?.find( ( item ) => 'merge-tags' === item.type )?.attrs?.tags || [] );
				const messageTagItems = buildTagItemsFromTags( resolvedTagItems );

				fieldElement = (
					<GutenaFormsHtmlEditorField
						id={ field.id }
						label={ getFieldLabel( field ) }
						value={ fieldValue[ field.id ] }
						onChange={ ( newValue ) => handleFieldChange( field.id, newValue ) }
						onFocus={ () => setActiveMergeField( field.id ) }
						onRegisterInsert={ ( insertFn ) => {
							mergeEditorRefs.current[ field.id ] = insertFn;
						} }
						placeholder={ field.attrs?.placeholder }
						disabled={ isDisabled }
						tagItems={ messageTagItems }
					/>
				);
				break;
			}

			case 'textarea':
				fieldElement = (
					<GutenaFormsTextareaField
						id={ field.id }
						label={ field.label }
						desc={ field.desc }
						value={ fieldValue[ field.id ] }
						onChange={ ( newValue ) => handleFieldChange( field.id, newValue ) }
						onFocus={ field.attrs?.merge_tag_field ? () => setActiveMergeField( field.id ) : undefined }
						placeholder={ field.attrs?.placeholder }
						rows={ field.attrs?.rows || 5 }
						disabled={ isDisabled }
					/>
				);
				break;

			case 'merge-tags':
				fieldElement = (
					<GutenaFormsMergeTagsField
						tags={ field.attrs?.tags || [] }
						onInsert={ insertMergeTag }
						disabled={
							typeof fieldValue?.enable !== 'undefined'
								? ! fieldValue?.enable
								: false
						}
					/>
				);
				break;

			case 'radio-group':
				fieldElement = (
					<GutenaFormsRadioGroup
						id={ field.id }
						label={ field.label }
						desc={ field.desc }
						value={ fieldValue[ field.id ] }
						onChange={ ( newValue ) => handleFieldChange( field.id, newValue ) }
						options={ field.attrs.options }
						variant={ field.attrs?.variant || 'segmented' }
						disabled={ isDisabled }
					/>
				)
				break;

			case 'select':
				fieldElement = (
					<GutenaFormsSelectField
						id={ field.id }
						label={ field.label }
						desc={ field.desc }
						value={ fieldValue[ field.id ] }
						options={ field.attrs?.options || {} }
						onChange={ ( newValue ) => handleFieldChange( field.id, newValue ) }
						disabled={ isDisabled }
					/>
				);
				break;

			case 'url':
				fieldElement = (
					<GutenaFormsUrlField
						id={ field.id }
						label={ field.label }
						desc={ field.desc }
						value={ fieldValue[ field.id ] }
						onChange={ ( newValue ) => handleFieldChange( field.id, newValue ) }
						placeholder={ field.attrs?.placeholder }
						disabled={ isDisabled }
					/>
				);
				break;

			case 'field-template':
				const FieldTemplate = FieldTemplates[ field.name ];
				fieldElement = (
					<>
						{ FieldTemplate && <FieldTemplate { ...field } /> }
					</>
				);
				break;

			default:
				console.log( 'Field not found', field )
				fieldElement = null;
				break;
		}

		return (
			<div className={ 'gutena-forms__field-container' }>
				{ fieldElement }
			</div>
		);
	};

	const ScreenTemplate = SettingsTemplates[ template ];
	const showProPopup = () => {
		if ( ! isPro ) {
			return;
		}

		onClick();
	}

	const IconMap = {
		'active-campaign': <Activecampaign />,
		'brevo': <Brevo />,
		'mailchimp': <Mailchimp />,
		'recaptcha': <Recaptcha />,
		'cloudflare': <Cloudflare />,
	};

	return (
		<div className={ `gutena-forms__meta-box-container${ 'auto-responder' === id ? ' gutena-forms__email-notifications-settings' : '' }${ 'form-confirmation' === id ? ' gutena-forms__form-confirmation-settings' : '' }` } onClick={ showProPopup }>
			<h2 className={ 'gutena-forms__page-title' }>
				<div>
					{ IconMap[ id ] && IconMap[ id ] } { title }
					{
						isPro && (
							<GutenaFormsProBadge />
						)
					}
				</div>
				<div>
					{ goBack && (
						<div className={ 'gutena-forms__submit-button secondary' }>
							<NavLink
								to={ goBack }
							>
								{ __( 'Go Back', 'gutena-forms' ) }
							</NavLink>
						</div>
					) }
				</div>
			</h2>
			<p
				className={ 'gutena-forms__settings-meta-box-desc' }
				dangerouslySetInnerHTML={ { __html: description } }
			/>

			{ 'auto-responder' === id && (
				<div className="gutena-forms__auto-responder-notice">
					<span className="dashicons dashicons-info" aria-hidden="true" />
					<p>
						<strong>{ __( 'Note:', 'gutena-forms' ) }</strong>
						{ ' ' }
						{ __(
							'These settings apply as defaults when a new form is created. Existing forms keep their own saved notification settings.',
							'gutena-forms'
						) }
					</p>
				</div>
			) }

			{ 'form-confirmation' === id && (
				<div className="gutena-forms__auto-responder-notice">
					<span className="dashicons dashicons-info" aria-hidden="true" />
					<p>
						<strong>{ __( 'Note:', 'gutena-forms' ) }</strong>
						{ ' ' }
						{ __(
							'These settings apply as defaults when a new form is created. Existing forms keep their own saved confirmation settings.',
							'gutena-forms'
						) }
					</p>
				</div>
			) }

			<div className={ 'gutena-forms__settings-meta-box' }>
				{ ! template && ! loading && settings && settings.map( ( field ) => {
					if ( field.type !== 'merge-tags' && ! shouldRenderField( field.id ) ) {
						return null;
					}

					return (
						<div key={ field.id } className="gutena-forms__settings-field-row">
							{ renderSettingsField( field ) }
						</div>
					);
				} ) }
				{
					template && ScreenTemplate && (
						<ScreenTemplate />
					)
				}

				{
					isPro && (
						<div className="gutena-forms__settings-meta-box--overlay"></div>
					)
				}
			</div>
		</div>
	);
};

export default GutenaFormsSettingsMetaBox;
