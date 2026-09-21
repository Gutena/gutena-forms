import {
	getContentMergeTags,
	getFormInputTagItems,
} from './email-notifications-merge-tags';

export const STATIC_CONFIRMATION_MERGE_TAGS = [
	'{site_name}',
	'{site_url}',
	'{submission_date}',
	'{form-title}',
	'{form_title}',
	'{admin_email}',
];

export const getConfirmationMergeTags = ( formFields = [] ) =>
	Array.from(
		new Set( [ ...STATIC_CONFIRMATION_MERGE_TAGS, ...getContentMergeTags( formFields ) ] )
	);

const formatTagLabel = ( tag ) =>
	tag
		.replace( /^\{|\}$/g, '' )
		.replace( /[-_]/g, ' ' )
		.replace( /\b\w/g, ( char ) => char.toUpperCase() );

export const getConfirmationTagItems = ( formFields = [] ) => {
	const staticItems = STATIC_CONFIRMATION_MERGE_TAGS.map( ( tag ) => ( {
		label: formatTagLabel( tag ),
		tag,
	} ) );

	return [ ...staticItems, ...getFormInputTagItems( formFields ) ];
};
