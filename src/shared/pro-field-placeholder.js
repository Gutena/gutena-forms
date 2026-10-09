import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';

/**
 * Editor placeholder for premium field blocks on the free plugin.
 *
 * @param {Object} props Block edit props.
 * @return {JSX.Element}
 */
export default function ProFieldPlaceholder( props ) {
	const { name } = props;
	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<Placeholder
				icon="lock"
				label={ __( 'Premium field', 'gutena-forms' ) }
				instructions={ __(
					'Upgrade to Gutena Forms Pro to edit and use this field.',
					'gutena-forms'
				) }
			>
				<p style={ { margin: 0, fontSize: '12px', color: '#757575' } }>
					{ name }
				</p>
			</Placeholder>
		</div>
	);
}
