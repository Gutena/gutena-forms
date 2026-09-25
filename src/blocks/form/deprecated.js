import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import metadata from './block.json';

const sharedAttributes = metadata.attributes;

const legacySpacingSupports = {
	margin: true,
	padding: true,
	blockGap: true,
};

function FormSave( { attributes, withWrapper } ) {
	const { formID, formClasses } = attributes;

	const blockProps = useBlockProps.save( {
		className: formClasses,
	} );

	return (
		<form method="post" encType="multipart/form-data" { ...blockProps }>
			<input type="hidden" name="formid" value={ formID } />
			{ withWrapper ? (
				<div className="gutena-forms-content-wrapper">
					<InnerBlocks.Content />
				</div>
			) : (
				<InnerBlocks.Content />
			) }
		</form>
	);
}

/**
 * Deprecated save versions for backward-compatible block validation.
 *
 * Order matters: WordPress tries each definition until saved markup validates.
 */
const deprecated = [
	{
		attributes: sharedAttributes,
		supports: {
			...metadata.supports,
			spacing: legacySpacingSupports,
		},
		save( props ) {
			return <FormSave { ...props } withWrapper={ false } />;
		},
		migrate( attributes, innerBlocks ) {
			return [ attributes, innerBlocks ];
		},
	},
	{
		attributes: sharedAttributes,
		supports: {
			...metadata.supports,
			spacing: legacySpacingSupports,
		},
		save( props ) {
			return <FormSave { ...props } withWrapper={ true } />;
		},
		isEligible( attributes, innerBlocks, { block } ) {
			return block?.originalContent?.includes(
				'gutena-forms-content-wrapper'
			);
		},
		migrate( attributes, innerBlocks ) {
			return [ attributes, innerBlocks ];
		},
	},
];

export default deprecated;
