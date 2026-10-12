import { __ } from '@wordpress/i18n';
import { LAYOUT_PRESET_KEYS, LAYOUT_PRESETS } from '../../shared/constants/layout-presets';

export default function PresetPicker( { preset, onSelect } ) {
	return (
		<div className="gutena-layout-preset-picker">
			<p className="gutena-layout-preset-picker__label">
				{ __( 'Select a Layout', 'gutena-forms' ) }
			</p>
			<div className="gutena-layout-preset-picker__grid">
				{ LAYOUT_PRESET_KEYS.map( ( key ) => {
					const config = LAYOUT_PRESETS[ key ];
					const isSelected = preset === key;
					return (
						<button
							type="button"
							key={ key }
							className={
								'gutena-layout-preset-picker__option' +
								( isSelected
									? ' is-selected'
									: '' )
							}
							onClick={ () => onSelect( key ) }
							aria-pressed={ isSelected }
							aria-label={ config.label }
							title={ config.label }
						>
							<span
								className="gutena-layout-preset-picker__bars"
								data-preset={ key }
								aria-hidden="true"
							>
								{ Array.from( {
									length: config.columnCount,
								} ).map( ( _, index ) => (
									<span
										key={ index }
										className="gutena-layout-preset-picker__bar"
									/>
								) ) }
							</span>
						</button>
					);
				} ) }
			</div>
		</div>
	);
}
