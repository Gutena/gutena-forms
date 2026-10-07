import { __ } from '@wordpress/i18n';
import { TextControl } from '@wordpress/components';

/**
 * Read-only Field ID control for standalone form field blocks.
 *
 * @param {Object} props
 * @param {string} props.nameAttr
 */
export default function FieldIdControl( { nameAttr } ) {
	return (
		<TextControl
			label={ __( 'Field ID', 'gutena-forms' ) + ' *' }
			value={ nameAttr ?? '' }
			readOnly
			help={ __(
				'Auto-assigned. Used as input name in form submission.',
				'gutena-forms'
			) }
		/>
	);
}
