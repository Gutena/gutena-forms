/**
 * ConditionalLogicPanel — the InspectorControls panel rendered inside each
 * eligible field's `edit.js`. Wires the enable toggle, the show/hide action
 * select, the "Configure Conditions" button, the read-only ConditionSummary,
 * and the ConditionBuilderModal.
 *
 * Behavior (user story sections B, D, E, F):
 *  - Toggle ON with empty groups: seed one empty group with one empty condition.
 *  - Toggle OFF: keep groups for restore.
 *  - Last rule removed (via modal save): auto-disable.
 *  - Changing the action updates `conditionalLogic.action`.
 */

import { __ } from '@wordpress/i18n';
import { useState, useMemo } from '@wordpress/element';
import { PanelBody, Button, ToggleControl, SelectControl } from '@wordpress/components';
import ConditionBuilderModal from './condition-builder-modal';
import ConditionSummary from './condition-summary';
import { getSiblingFields } from './use-sibling-fields';
import './style.scss';

const DEFAULT_CONDITIONAL_LOGIC = {
	enabled: false,
	action: 'show',
	groups: [],
};

function emptyCondition() {
	return { field: '', operator: 'is', value: '' };
}

export default function ConditionalLogicPanel( { clientId, conditionalLogic, setAttributes } ) {
	const [ modalOpen, setModalOpen ] = useState( false );

	const value = useMemo(
		() => ( { ...DEFAULT_CONDITIONAL_LOGIC, ...( conditionalLogic || {} ) } ),
		[ conditionalLogic ]
	);

	const siblings = useMemo( () => getSiblingFields( clientId ), [ clientId ] );

	const update = ( patch ) => {
		setAttributes( {
			conditionalLogic: { ...value, ...patch },
		} );
	};

	const handleToggle = ( enabled ) => {
		if ( enabled ) {
			// Seed one empty group with one empty condition when enabling with no groups.
			const seeded = {
				...value,
				enabled: true,
				groups: Array.isArray( value.groups ) && value.groups.length
					? value.groups
					: [ [ emptyCondition() ] ],
			};
			setAttributes( { conditionalLogic: seeded } );
		} else {
			// Keep groups for restore; only flip the flag.
			update( { enabled: false } );
		}
	};

	const handleSaveGroups = ( groups ) => {
		// Auto-disable when the last rule is removed (user story section B).
		if ( ! groups || ! groups.length ) {
			update( { enabled: false, groups: [] } );
			return;
		}
		update( { groups } );
	};

	return (
		<PanelBody
			title={ __( 'Conditional Logic', 'gutena-forms' ) }
			initialOpen={ false }
			className="gf-conditional-logic-panel"
		>
			<ToggleControl
				label={ __( 'Enable conditional logic', 'gutena-forms' ) }
				checked={ !! value.enabled }
				onChange={ handleToggle }
			/>

			{ value.enabled && (
				<>
					<SelectControl
						label={ __( 'Action', 'gutena-forms' ) }
						value={ value.action || 'show' }
						options={ [
							{ value: 'show', label: __( 'Show when conditions match', 'gutena-forms' ) },
							{ value: 'hide', label: __( 'Hide when conditions match', 'gutena-forms' ) },
						] }
						onChange={ ( action ) => update( { action } ) }
						__nextHasNoMarginBottom
					/>

					<ConditionSummary
						action={ value.action }
						groups={ value.groups }
						siblings={ siblings }
					/>

					<Button
						variant="secondary"
						text={ __( 'Configure Conditions', 'gutena-forms' ) }
						onClick={ () => setModalOpen( true ) }
						className="gf-conditional-logic-panel__configure"
					/>
				</>
			) }

			<ConditionBuilderModal
				isOpen={ modalOpen }
				groups={ value.groups }
				siblings={ siblings }
				onClose={ () => setModalOpen( false ) }
				onSave={ handleSaveGroups }
			/>
		</PanelBody>
	);
}
