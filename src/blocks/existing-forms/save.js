import { useBlockProps } from '@wordpress/block-editor';

const Save = () => {
	const blockProps = useBlockProps.save();

	return (
		<div { ...blockProps }>
			This form has been deleted.
		</div>
	);
};

export default Save;
