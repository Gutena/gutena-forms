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

/**
 * All listed keys must be strictly true (used for MCP depends_on / visible_when).
 *
 * @param {string[]|undefined} keys
 * @param {Record<string, unknown>} values
 * @return {boolean}
 */
const gutenaFormsSettingsDepsSatisfied = ( keys, values ) => {
	if ( ! keys || ! keys.length ) {
		return true;
	}
	return keys.every( ( k ) => values[ k ] === true );
};

const GutenaFormsSettingsMetaBox = ( { id, title, description, items, isPro = false, onClick, goBack } ) => {
	const { settings_id } = useParams();
	const [ settings, setSettings ] = useState( false );
	const [ fieldValue, setFieldValue ] = useState( {} );
	const [ loading, setLoading ] = useState( true );
	const [ template, setTemplate ] = useState( false );

	useEffect(
		() => {
			setLoading( true );
			const nextSettings = {};
			const nextFieldValue = {};
			items.forEach( ( item, idx ) => {
				if ( 'template' === item.type ) {
					setTemplate( item.name );
				} else if ( 'field-template' === item.type ) {
					nextSettings[ idx ] = item;
				} else {
					const initial =
						item.value !== undefined && item.value !== null
							? item.value
							: item.default;
					nextFieldValue[ item.id ] = initial;
					nextSettings[ idx ] = {
						id: item.id,
						type: item.type,
						label: item.name,
						desc: item.desc,
						value: initial,
						attrs: item.attrs || {},
					};
				}
			} );

			setSettings( nextSettings );
			setFieldValue( nextFieldValue );
			setLoading( false );
		},
		[]
	);

	const handleFieldChange = ( fieldId, newValue ) => {
		setFieldValue( ( prev ) => {
			const next = { ...prev, [ fieldId ]: newValue };
			if ( settings_id === 'mcp' && fieldId === 'abilities_enabled' && ! newValue ) {
				next.mcp_enabled = false;
			}
			return next;
		} );
	};

	const handleSubmit = () => {
		gutenaFormsUpdateSettings( settings_id, fieldValue )
			.then( () => {
				toast.success(
					__( 'Settings updated successfully.', 'gutena-forms' )
				);
			} )
	};

	const GutenaFormsRenderSettingsField = ( { field } ) => {
		let fieldElement;

		const toggleDisabled =
			field.attrs?.depends_on?.length &&
			! gutenaFormsSettingsDepsSatisfied( field.attrs.depends_on, fieldValue );

		switch ( field.type ) {
			case 'toggle':
				fieldElement = (
					<GutenaFormsToggleField
						id={ field.id }
						label={ field.label }
						desc={ field.desc }
						checked={ field.value }
						onChange={ ( newValue ) => handleFieldChange( field.id, newValue ) }
						disabled={ !! toggleDisabled }
					/>
				);
				break;

			case 'number':
				fieldElement = (
					<GutenaFormsNumberField
						id={ field.id }
						label={ field.label }
						desc={ field.desc }
						value={ field.value }
						onChange={ ( newValue ) => handleFieldChange( field.id, newValue ) }
						min={ field.attrs.min }
						max={ field.attrs.max }
						step={ field.attrs.step }
					/>
				);
				break;

			case 'email':
				fieldElement = (
					<GutenaFormsEmailField
						id={ field.id }
						label={ field.label }
						desc={ field.desc }
						value={ field.value }
						onChange={ ( newValue ) => handleFieldChange( field.id, newValue ) }
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
						value={ field.value }
						onChange={ ( newValue ) => handleFieldChange( field.id, newValue ) }
						placeholder={ field.attrs.placeholder }
					/>
				);
				break;

			case 'radio-group':
				fieldElement = (
					<GutenaFormsRadioGroup
						id={ field.id }
						label={ field.label }
						desc={ field.desc }
						value={ field.value }
						onChange={ ( newValue ) => handleFieldChange( field.id, newValue ) }
						options={ field.attrs.options }
					/>
				)
				break;

			case 'field-template': {
				if (
					field.attrs?.visible_when?.length &&
					! gutenaFormsSettingsDepsSatisfied( field.attrs.visible_when, fieldValue )
				) {
					fieldElement = null;
					break;
				}
				const FieldTemplate = FieldTemplates[ field.name ];
				fieldElement = (
					<>
						{ FieldTemplate && <FieldTemplate { ...field } /> }
					</>
				);
				break;
			}

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
	}

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
			<h2>
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
				{ ! template && ! loading && settings && Object.keys( settings ).map( ( key, index ) => {
					const rawField = settings[ key ];
					const field =
						rawField.id !== undefined
							? {
									...rawField,
									value:
										fieldValue[ rawField.id ] !== undefined
											? fieldValue[ rawField.id ]
											: rawField.value,
							  }
							: rawField;
					return (
						<div key={ index }>
							<GutenaFormsRenderSettingsField field={ field } />
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
