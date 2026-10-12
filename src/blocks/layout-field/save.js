import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';

export default function save( { attributes } ) {
	const { preset, displayRatio } = attributes;

	const blockProps = useBlockProps.save( {
		className: `wp-block-gutena-layout-field is-preset-${ preset }`,
		'data-preset': preset,
		'data-display-ratio': displayRatio,
	} );

	return (
		<div { ...blockProps }>
			<InnerBlocks.Content />
		</div>
	);
}
