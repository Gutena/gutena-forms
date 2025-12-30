import { useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { addQueryArgs } from '@wordpress/url';

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
		<div>
			{
				attributes.formID ? (
					<div>Loading form</div>
				) : (
					<select
						onChange={ ( event ) => {
							setFormId( event.target.value );
							setAttributes( { formID: event.target.value } );
						} }
					>
						<option value={ false }>Select a form</option>
						{ formIds.map( ( form ) => (
							<option key={ form.id } value={ form.id }>
								{ form.title }
							</option>
						) ) }
					</select>
				)
			}
		</div>
	);
};

export default Edit;
