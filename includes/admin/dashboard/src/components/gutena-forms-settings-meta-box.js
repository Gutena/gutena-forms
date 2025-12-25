import { useEffect, useState } from '@wordpress/element';
import { useParams } from 'react-router';
import GutenaFormsNumberField from './fields/gutena-forms-number-field';
import GutenaFormsToggleField from './fields/gutena-forms-toggle-field';
import GutenaFormsEmailField from './fields/gutena-forms-email-field';
import GutenaFormsSubmitButton from './fields/gutena-forms-submit-button';
import { gutenaFormsUpdateSettings } from "../api";
import { toast } from 'react-toastify';
import { __ } from '@wordpress/i18n';

const GutenaFormsSettingsMetaBox = ( { title, description, items } ) => {
	const { settings_id } = useParams();
	const [ settings, setSettings ] = useState( false );
	const [ fieldValue, setFieldValue ] = useState( {} );
	const [ loading, setLoading ] = useState( true );

	useEffect(
		() => {
			setLoading( true );
			const settings = {};
			items.map( ( item, id ) => {
				if ( 'template' === item.type ) {

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
			default:
				console.log( 'Field not found', field )
				fieldElement = null;
				break;
		}

		return fieldElement;
	}

	return (
		<div>
			<h2>{ title }</h2>
			<p>{ description }</p>

			<div className={ 'gutena-forms__settings-meta-box' }>
				{ ! loading && settings && Object.keys( settings ).map( ( key, index ) => {
					return (
						<div key={ index }>
							<GutenaFormsRenderSettingsField field={ settings[ key ] } />
						</div>
					);
				} ) }
			</div>
		</div>
	);
};

export default GutenaFormsSettingsMetaBox;
