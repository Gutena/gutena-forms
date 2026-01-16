import { useEffect, useState, useRef } from '@wordpress/element';
import { Icon, Panel, PanelRow, PanelBody, PanelHeader, Button, Dashicon } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { InnerBlocks, useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { addQueryArgs } from '@wordpress/url';
import Logo from './logo';
import { __ } from '@wordpress/i18n';
import { dispatch } from '@wordpress/data';

const Edit = ( { attributes, setAttributes } ) => {
	const blockProps = useBlockProps();

	const [ formIds, setFormIds ] = useState( [] );
	const [ formId, setFormId ] = useState( false );
	const [ form, setForm ] = useState( null );

	const selectReference = useRef( null );

	useEffect( () => {
		apiFetch( {
			path: '/gutena-forms/v1/forms/get-ids',
		} )
			.then( ( response ) => {
				setFormIds( response.forms );
			} );

		// focus select box on mount
		if ( selectReference.current ) {
			selectReference.current.focus();
			selectReference.current.click();
		}

		if ( attributes.formID ) {
			setFormId( attributes.formID );
		}

		// hide block all field blocks when existing form block available.
		const blockToToggle = [
			'gutena/textarea-field-group',
			'gutena/text-field-group',
			'gutena/range-field-group',
			'gutena/radio-field-group',
			'gutena/optin-field-group',
			'gutena/number-field-group',
			'gutena/email-field-group',
			'gutena/dropdown-field-group',
			'gutena/checkbox-field-group',
			'gutena/country-field-group',
			'gutena/date-field-group',
			'gutena/file-upload-field-group',
			'gutena/hidden-field-group',
			'gutena/password-field-group',
			'gutena/phone-field-group',
			'gutena/rating-field-group',
			'gutena/state-field-group',
			'gutena/time-field-group',
			'gutena/url-field-group',
		];

		dispatch( 'core/edit-post' )
			.hideBlockTypes( blockToToggle );

	}, [] );

	useEffect( () => {
		if ( ! formId || 'false' === formId ) {
			return;
		}

		apiFetch( {
			path: addQueryArgs(
				'/gutena-forms/v1/forms/get',
				{ id: formId }
			),
		} )
			.then( ( response ) => {
				setForm( response.form.content );
			} );
	}, [ formId ] );

	const DropDownIcon = () => (
		<Icon
			icon={ () => (
				<svg xmlns="http://www.w3.org/2000/svg" width="10" height="6" viewBox="0 0 10 6" fill="none">
					<path fillRule="evenodd" clipRule="evenodd" d="M0.219176 0.219196C0.359801 0.0787457 0.550426 -0.000144178 0.749176 -0.000144195C0.947927 -0.000144213 1.13855 0.0787456 1.27918 0.219196L4.74918 3.6892L8.21918 0.219195C8.36135 0.0867153 8.5494 0.0145918 8.7437 0.0180197C8.938 0.0214477 9.12339 0.100161 9.2608 0.237574C9.39821 0.374987 9.47692 0.560372 9.48035 0.754673C9.48378 0.948974 9.41166 1.13702 9.27918 1.27919L5.27918 5.2792C5.13855 5.41965 4.94793 5.49854 4.74918 5.49854C4.55043 5.49854 4.3598 5.41965 4.21918 5.2792L0.219176 1.2792C0.0787254 1.13857 -0.000163476 0.947946 -0.000163494 0.749196C-0.000163511 0.550445 0.0787254 0.359822 0.219176 0.219196Z" fill="#777777"/>
				</svg>
			) }
		/>
	);

	let elementComponent;

	if ( formId && 'false' !== formId && form ) {
		elementComponent = (
			<div>
				<div dangerouslySetInnerHTML={ { __html: form } }></div>
			</div>
		);
	} else {
		elementComponent = (
			<div style={ { display: 'flex', justifyContent: 'center', alignItems: 'center', width: '100%', height: '220px', border: '1px solid #E2E2E2', background: '#FAFAFA', } }>
				{
					attributes.formID ? (
						<div>Loading form</div>
					) : (
						<div style={ { display: 'flex', alignItems: 'center', flexDirection: 'column' } }>

							<div style={ { display: 'block', marginBottom: '20px' } }>
								<Logo />
							</div>

							<div style={ { width: '320px', borderRadius: '4px', background: '#FAFAFA', border: '1px solid #E2E2E2', position: 'relative' } }>
							<span
								style={ { position: 'absolute', top: '-2px', right: '8px', } }
							>
								<DropDownIcon />
							</span>
								<select
									onChange={ ( event ) => {
										setFormId( event.target.value );
										setAttributes( { formID: event.target.value } );
									} }
									style={ { width: '100%', border: 'none' }}
									ref={ selectReference }
								>
									<option value={ false }>Choose an existing form</option>
									{ formIds.map( ( form ) => (
										<option key={ form.id } value={ form.id }>
											{ form.title }
										</option>
									) ) }
								</select>
							</div>
						</div>
					)
				}
			</div>
		);
	}

	return (
		<div>
			<InspectorControls>
				{
					formId && 'false' !== formId && form &&
					(
						<Panel>
							<PanelHeader>
								{ __( 'Form Settings', 'gutena-forms' ) }
							</PanelHeader>
							<PanelBody>

								<div className={ 'notice notice-warning' } style={{ margin: '0 0 15px 0'}}>
									<p>
										{ __( 'Note: For editing Gutena Forms please refer to the Gutena Forms Editor - ', 'gutena-forms' ) }
										<Button
											href={ `post.php?post=${ formId }&action=edit` }
											style={ { padding: 0, margin: 0, textDecoration: 'underline', height: 0, color: '#007cba', boxShadow: 'none' } }
											target={ '_blank' }
										>
											{ __( 'Edit Form', 'gutena-forms' ) }
											<Dashicon
												icon={ 'external' }
											/>
										</Button>
									</p>
								</div>

								<Button
									style={ { width: '100%', display: 'block' } }
									isSecondary
									onClick={ () => {
										setFormId( false );
										setForm( null );
										setAttributes( { formID: false } );
									} }
								>
									{ __( 'Change Form', 'gutena-forms' ) }
								</Button>
							</PanelBody>
						</Panel>
					)
				}
			</InspectorControls>
			<div { ...blockProps }>
				{ elementComponent }
			</div>
		</div>
	);
};

export default Edit;
