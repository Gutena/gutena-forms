import { useSelect } from '@wordpress/data';
import {
	useBlockProps,
	useInnerBlocksProps,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import { getLayoutColumnAllowedBlocks } from '../../shared/constants/form-allowed-blocks';
import ColumnDropPlaceholder from './column-drop-placeholder';

export default function Edit( { clientId } ) {
	const hasInnerBlocks = useSelect(
		( select ) => {
			const block = select( blockEditorStore ).getBlock( clientId );
			return ( block?.innerBlocks?.length || 0 ) > 0;
		},
		[ clientId ]
	);

	const blockProps = useBlockProps( {
		className:
			'wp-block-gutena-layout-column' +
			( hasInnerBlocks ? ' has-inner-blocks' : ' is-empty-column' ),
	} );

	const innerBlocksProps = useInnerBlocksProps( blockProps, {
		allowedBlocks: getLayoutColumnAllowedBlocks(),
		renderAppender: false,
		templateLock: false,
	} );

	const { children, ...columnWrapperProps } = innerBlocksProps;

	return (
		<div { ...columnWrapperProps }>
			{ ! hasInnerBlocks && (
				<ColumnDropPlaceholder clientId={ clientId } />
			) }
			{ children }
		</div>
	);
}
