import { __ } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';
import { Inserter, store as blockEditorStore } from '@wordpress/block-editor';
import { getLayoutColumnAllowedBlocks } from '../../shared/constants/form-allowed-blocks';

export default function ColumnDropPlaceholder( { clientId } ) {
	const { selectBlock } = useDispatch( blockEditorStore );

	return (
		<Inserter
			rootClientId={ clientId }
			isAppender
			allowedBlocks={ getLayoutColumnAllowedBlocks() }
			renderToggle={ ( { onToggle, disabled } ) => (
				<button
					type="button"
					className="gutena-layout-column__placeholder"
					disabled={ disabled }
					onClick={ ( event ) => {
						event.stopPropagation();
						selectBlock( clientId );
						onToggle( event );
					} }
				>
					{ __( 'Drop a field here', 'gutena-forms' ) }
				</button>
			) }
		/>
	);
}
