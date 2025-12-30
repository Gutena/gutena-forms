import { useBlockProps } from '@wordpress/block-editor';

const Save = ( { attributes } ) => {
	const blockProps = useBlockProps.save();

	return (
		<div { ...blockProps }>
			Something went wrong. Please update Gutena Forms to the latest version.
		</div>
	);
};

export default Save;
