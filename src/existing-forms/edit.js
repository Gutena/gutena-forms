import { useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { addQueryArgs } from '@wordpress/url';
import Logo from './logo.png';

const Edit = ( { attributes, setAttributes } ) => {
	const blockProps = useBlockProps();

	const [ formIds, setFormIds ] = useState( [] );
	const [ formId, setFormId ] = useState( false );
	const [ form, setForm ] = useState( null );

	useEffect( () => {
		apiFetch( {
			path: '/gutena-forms/v1/forms/get-ids',
		} )
			.then( ( response ) => {
				setFormIds( response.forms );
			} );

		if ( attributes.formID ) {
			setFormId( attributes.formID );
		}
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

	if ( formId && 'false' !== formId && form ) {
		return (
			<div { ...blockProps }>
				<div dangerouslySetInnerHTML={ { __html: form } }></div>
			</div>
		);
	}

	return (
		<div style={ { display: 'flex', justifyContent: 'center', alignItems: 'center', width: '100%', height: '220px' } }>
			{
				attributes.formID ? (
					<div>Loading form</div>
				) : (
					<div style={ { display: 'flex', alignItems: 'center', flexDirection: 'column' } }>
						<img src={ Logo } alt="Gutena Forms" style={ { display: 'block', marginBottom: '20px' } } />
						<select
							onChange={ ( event ) => {
								setFormId( event.target.value );
								setAttributes( { formID: event.target.value } );
							} }
							style={ { width: '320px', borderRadius: '4px', background: '#ffffff', border: '1px solid #4ba18a' }}
						>
							<option value={ false }>Select a form</option>
							{ formIds.map( ( form ) => (
								<option key={ form.id } value={ form.id }>
									{ form.title }
								</option>
							) ) }
						</select>
					</div>
				)
			}
		</div>
	);
};

export default Edit;
