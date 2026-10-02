export const GENERIC_EMAIL_TAGS = [ '{admin_email}', '{user_email}' ];

export const GENERIC_CONTENT_TAGS = [
	'{site_url}',
	'{admin_email}',
	'{site_title}',
	'{form_title}',
	'{user_email}',
	'{user_name}',
];

export const FROM_EMAIL_MERGE_TAGS = [ ...GENERIC_EMAIL_TAGS ];

export const FIELD_TAG_CONFIG = {
	send_email_to: { generic: 'email', formInput: 'email' },
	subject: { generic: 'content', formInput: 'all' },
	message: { generic: 'content', formInput: 'all' },
	from_name: { generic: 'content', formInput: 'all' },
	from_email: { generic: 'email', formInput: 'email' },
	cc: { generic: 'email', formInput: 'email' },
	bcc: { generic: 'email', formInput: 'email' },
	reply_to: { generic: 'email', formInput: 'email' },
};

const fieldTag = ( nameAttr ) => `{field:${ nameAttr }}`;

const getGenericTagsByType = ( genericType ) => {
	if ( 'email' === genericType ) {
		return [ ...GENERIC_EMAIL_TAGS ];
	}

	return [ ...GENERIC_CONTENT_TAGS ];
};

const filterFormFields = ( formFields = [], filter = 'all' ) => {
	if ( 'email' === filter ) {
		return formFields.filter( ( field ) => 'email' === field.fieldType );
	}

	return formFields;
};

export const getFormInputTagItems = ( formFields = [], filter = 'all' ) =>
	filterFormFields( formFields, filter ).map( ( field ) => ( {
		label: field.fieldName || field.nameAttr,
		tag: fieldTag( field.nameAttr ),
	} ) );

export const getGenericTagsForField = ( fieldKey ) => {
	const config = FIELD_TAG_CONFIG[ fieldKey ];

	if ( ! config ) {
		return [];
	}

	return getGenericTagsByType( config.generic );
};

/**
 * Resolve merge tag UI data for a notification field.
 *
 * @param {string} fieldKey   Notification field key.
 * @param {Array}  formFields Current form fields.
 * @param {string} context    'global' | 'form'
 * @return {{ tags: string[], tagItems: Array }}
 */
export const getTagsForField = ( fieldKey, formFields = [], context = 'form' ) => {
	const config = FIELD_TAG_CONFIG[ fieldKey ];

	if ( ! config ) {
		return { tags: [], tagItems: [] };
	}

	const tags = getGenericTagsByType( config.generic );

	if ( 'global' === context ) {
		return { tags, tagItems: [] };
	}

	const tagItems = getFormInputTagItems( formFields, config.formInput );

	return { tags, tagItems };
};

/**
 * Flat list of all allowed merge tag strings for a field (validation).
 *
 * @param {string} fieldKey
 * @param {Array}  formFields
 * @param {string} context
 * @return {string[]}
 */
export const getAllowedMergeTagsForField = (
	fieldKey,
	formFields = [],
	context = 'form'
) => {
	const { tags, tagItems } = getTagsForField( fieldKey, formFields, context );

	return [ ...tags, ...tagItems.map( ( item ) => item.tag ) ];
};
