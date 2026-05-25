import { useEffect, useState } from '@wordpress/element';
import {NavLink, useParams} from 'react-router';
import GutenaFormsNumberField from './fields/gutena-forms-number-field';
import GutenaFormsToggleField from './fields/gutena-forms-toggle-field';
import GutenaFormsEmailField from './fields/gutena-forms-email-field';
import GutenaFormsSubmitButton from './fields/gutena-forms-submit-button';
import GutenaFormsTextField from './fields/gutena-forms-text-field';
import GutenaFormsRadioGroup from './fields/gutena-forms-radio-group';
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
				} else {
					initialFieldValue[ item.id ] = item.value || item.default;
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
	}

	const shouldRenderField = ( fieldId ) => {
		const isRecaptchaSettings = 'recaptcha' === id || 'google-recaptcha' === settings_id;
		if ( ! isRecaptchaSettings ) {
			return true;
		}

		const recaptchaType = fieldValue?.type || 'v2';
		if ( fieldId.startsWith( 'v2_' ) ) {
			return 'v2' === recaptchaType;
		}

		if ( fieldId.startsWith( 'v3_' ) ) {
			return 'v3' === recaptchaType;
		}

		return true;
	}

	const handleSubmit = () => {
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
						label={ field.label }
						desc={ field.desc }
						value={ fieldValue[ field.id ] }
						onChange={ ( newValue ) => handleFieldChange( field.id, newValue ) }
						disabled={ isDisabled }
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
						label={ field.label }
						desc={ field.desc }
						value={ fieldValue[ field.id ] }
						onChange={ ( newValue ) => handleFieldChange( field.id, newValue ) }
						placeholder={ field.attrs.placeholder }
						disabled={ isDisabled }
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
						disabled={ isDisabled }
					/>
				)
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
		<div className={ 'gutena-forms__meta-box-container' } onClick={ showProPopup }>
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

			<div className={ 'gutena-forms__settings-meta-box' }>
				{ ! template && ! loading && settings && settings.map( ( field ) => {
					if ( ! shouldRenderField( field.id ) ) {
						return null;
					}

					return (
						<div key={ field.id }>
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
