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
import { SettingsTemplates } from '../utils/templates';
import GutenaFormsProBadge from './gutena-forms-pro-badge';
import Activecampaign from '../icons/activecampaign';
import Brevo from '../icons/brevo';
import Mailchimp from '../icons/mailchimp';
import {Button} from "@wordpress/components";

const GutenaFormsSettingsMetaBox = ( { id, title, description, items, isPro = false, onClick, goBack } ) => {
	const { settings_id } = useParams();
	const [ settings, setSettings ] = useState( false );
	const [ fieldValue, setFieldValue ] = useState( {} );
	const [ loading, setLoading ] = useState( true );
	const [ template, setTemplate ] = useState( false );

	useEffect(
		() => {
			setLoading( true );
			const settings = {};
			items.map( ( item, id ) => {
				if ( 'template' === item.type ) {
					setTemplate( item.name );
				} else {
					fieldValue[ item.id ] = item.value || item.default;
					settings[ id ] = {
						id: item.id,
						type: item.type,
						label: item.name,
						desc: item.desc,
						value: fieldValue[ item.id ],
						attrs: item.attrs || {},
					}
				}
			} );

			setSettings( settings );
			setFieldValue( fieldValue );
			setLoading( false );
		},
		[] );

	const handleFieldChange = ( id, newValue ) => {
		fieldValue[ id ] = newValue;
		setFieldValue( fieldValue );
	}

	const handleSubmit = ( e ) => {
		gutenaFormsUpdateSettings( settings_id, fieldValue )
			.then( response => {
				toast.success(
					__( 'Settings updated successfully.', 'gutena-forms' )
				);
			} )
	};

	const GutenaFormsRenderSettingsField = ( { field } ) => {
		let fieldElement;

		switch ( field.type ) {
			case 'toggle':
				fieldElement = (
					<GutenaFormsToggleField
						id={ field.id }
						label={ field.label }
						desc={ field.desc }
						checked={ field.value }
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

			default:
				console.log( 'Field not found', field )
				fieldElement = null;
				break;
		}

		return fieldElement;
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
	};

	return (
		<div className={ 'gutena-forms__meta-box-wrapper' } onClick={ showProPopup }>
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
					return (
						<div key={ index }>
							<GutenaFormsRenderSettingsField field={ settings[ key ] } />
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
