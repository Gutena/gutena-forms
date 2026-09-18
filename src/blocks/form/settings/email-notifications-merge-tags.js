const EMAIL_RECIPIENT_BASE_TAGS = [ '{admin_email}', '{user_email}', '{field:email}' ];

const CONTENT_BASE_TAGS = [
	'{first_name}',
	'{last_name}',
	'{email}',
	'{message}',
	'{country}',
	'{state}',
	'{number}',
	'{url}',
	'{textarea}',
	'{phone}',
	'{checkbox}',
	'{all_data}',
	'{site_url}',
	'{admin_email}',
	'{site_title}',
	'{form_title}',
	'{user_email}',
	'{user_name}',
];

const fieldTag = ( nameAttr ) => `{field:${ nameAttr }}`;

export const getRecipientMergeTags = ( formFields = [] ) => {
	const tags = [ ...EMAIL_RECIPIENT_BASE_TAGS ];

	formFields
		.filter( ( field ) => 'email' === field.fieldType )
		.forEach( ( field ) => {
			tags.push( fieldTag( field.nameAttr ) );
		} );

	return Array.from( new Set( tags ) );
};

export const getContentMergeTags = ( formFields = [] ) => {
	const tags = [ ...CONTENT_BASE_TAGS ];

	formFields.forEach( ( field ) => {
		tags.push( fieldTag( field.nameAttr ) );
		if ( field.fieldName ) {
			tags.push( `{${ field.fieldName }}` );
		}
	} );

	return Array.from( new Set( tags ) );
};

export const FROM_EMAIL_MERGE_TAGS = [ '{admin_email}', '{user_email}' ];

export const getFormInputTagItems = ( formFields = [] ) =>
	formFields.map( ( field ) => ( {
		label: field.fieldName,
		tag: fieldTag( field.nameAttr ),
	} ) );
