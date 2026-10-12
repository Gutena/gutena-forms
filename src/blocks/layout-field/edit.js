import { __ } from '@wordpress/i18n';
import { useEffect, useRef } from '@wordpress/element';
import {
	InspectorControls,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { LAYOUT_COLUMN_BLOCK } from '../../shared/constants/form-allowed-blocks';
import {
	getDisplayRatioForPreset,
	getLayoutPreset,
	getSidebarRatioLabelForPreset,
	DEFAULT_LAYOUT_PRESET,
} from '../../shared/constants/layout-presets';
import { syncLayoutColumnsToPreset } from '../../shared/layout/sync-layout-columns';
import PresetPicker from './preset-picker';

const LAYOUT_COLUMN_TEMPLATE = [
	[ LAYOUT_COLUMN_BLOCK ],
	[ LAYOUT_COLUMN_BLOCK ],
];

export default function Edit( { attributes, setAttributes, clientId } ) {
	const { layoutLabel, preset, displayRatio } = attributes;
	const presetRef = useRef( preset );
	const isSyncingRef = useRef( false );

	const layoutConfig = getLayoutPreset( preset );

	const blockProps = useBlockProps( {
		className: `wp-block-gutena-layout-field is-preset-${ preset }`,
		style: {
			'--gutena-layout-columns': layoutConfig.gridTemplateColumns,
		},
	} );

	const innerBlocksProps = useInnerBlocksProps(
		{
			className: 'gutena-layout-field__columns',
			style: {
				'--gutena-layout-columns': layoutConfig.gridTemplateColumns,
			},
		},
		{
			allowedBlocks: [ LAYOUT_COLUMN_BLOCK ],
			template: LAYOUT_COLUMN_TEMPLATE,
			templateLock: 'all',
		}
	);

	const { children: columnChildren, ...columnsWrapperProps } =
		innerBlocksProps;

	useEffect( () => {
		syncLayoutColumnsToPreset(
			clientId,
			preset || DEFAULT_LAYOUT_PRESET
		);
		// eslint-disable-next-line react-hooks/exhaustive-deps -- run once on mount.
	}, [] );

	useEffect( () => {
		if ( presetRef.current === preset ) {
			return;
		}
		presetRef.current = preset;
		if ( isSyncingRef.current ) {
			return;
		}
		isSyncingRef.current = true;
		syncLayoutColumnsToPreset( clientId, preset );
		isSyncingRef.current = false;
	}, [ preset, clientId ] );

	const handlePresetSelect = ( nextPreset ) => {
		if ( nextPreset === preset ) {
			return;
		}
		setAttributes( {
			preset: nextPreset,
			displayRatio: getDisplayRatioForPreset( nextPreset ),
		} );
	};

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Layout settings', 'gutena-forms' ) }
					initialOpen={ true }
				>
					<TextControl
						label={ __( 'Label', 'gutena-forms' ) }
						value={ layoutLabel }
						onChange={ ( value ) =>
							setAttributes( { layoutLabel: value } )
						}
					/>
					<PresetPicker
						preset={ preset || DEFAULT_LAYOUT_PRESET }
						onSelect={ handlePresetSelect }
					/>
					<TextControl
						label={ __( 'Display Ratio', 'gutena-forms' ) }
						value={
							getSidebarRatioLabelForPreset(
								preset || DEFAULT_LAYOUT_PRESET
							) || displayRatio
						}
						readOnly
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<div { ...columnsWrapperProps }>{ columnChildren }</div>
				<p className="gutena-layout-field__helper">
					{ __(
						'Drag any field (or a new one from the inserter) into a column.',
						'gutena-forms'
					) }
				</p>
			</div>
		</>
	);
}
