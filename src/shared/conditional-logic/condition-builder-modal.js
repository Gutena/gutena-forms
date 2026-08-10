/**
 * ConditionBuilderModal — the V2 modal UI (Figma) used to author a field's
 * conditional logic groups.
 *
 * Layout:
 *  - Header: "Configure Conditions"
 *  - Body: groups separated by OR dividers; each group is a stack of
 *    ConditionRow rows joined by AND badges. "Add Condition" appends a row to
 *    a group; "OR Group" appends a new group.
 *  - Footer: Cancel / Set Rules.
 *
 * The modal works on a local draft copy of `groups` and commits it via
 * `onSave` only when "Set Rules" is pressed. Cancel discards the draft.
 */

import { __ } from '@wordpress/i18n';
import { useState, useEffect, useCallback } from '@wordpress/element';
import { Button, Modal } from '@wordpress/components';
import ConditionRow from './condition-row';
import {
	getOperatorOptions,
} from './operators';

function emptyCondition( fieldType ) {
	const ops = getOperatorOptions( fieldType || 'text' );
	return {
		field: '',
		operator: ops.length ? ops[ 0 ].value : '',
		value: '',
	};
}

function emptyGroup( fieldType ) {
	return [ emptyCondition( fieldType ) ];
}

export default function ConditionBuilderModal( {
	isOpen,
	groups,
	siblings,
	onClose,
	onSave,
} ) {
	const [ draft, setDraft ] = useState( () => cloneGroups( groups ) );

	useEffect( () => {
		if ( isOpen ) {
			setDraft( cloneGroups( groups ) );
		}
	}, [ isOpen, groups ] );

	const updateCondition = useCallback( ( groupIndex, rowIndex, patch ) => {
		setDraft( ( prev ) => {
			const next = prev.map( ( g ) => g.slice() );
			next[ groupIndex ][ rowIndex ] = { ...next[ groupIndex ][ rowIndex ], ...patch };
			return next;
		} );
	}, [] );

	const addCondition = useCallback( ( groupIndex ) => {
		setDraft( ( prev ) => {
			const next = prev.map( ( g ) => g.slice() );
			next[ groupIndex ].push( emptyCondition() );
			return next;
		} );
	}, [] );

	const removeCondition = useCallback( ( groupIndex, rowIndex ) => {
		setDraft( ( prev ) => {
			const next = prev.map( ( g ) => g.slice() );
			next[ groupIndex ].splice( rowIndex, 1 );
			// Drop empty groups.
			return next.filter( ( g ) => g.length > 0 );
		} );
	}, [] );

	const addGroup = useCallback( () => {
		setDraft( ( prev ) => [ ...prev, emptyGroup() ] );
	}, [] );

	if ( ! isOpen ) {
		return null;
	}

	return (
		<Modal
			className="gf-condition-builder"
			title={ __( 'Configure Conditional Logic', 'gutena-forms' ) }
			onRequestClose={ onClose }
			shouldCloseOnClickOutside={ false }
			shouldCloseOnEsc={ true }
			isWide
		>
			<div className="gf-condition-builder__body">
				{ draft.length === 0 && (
					<p className="gf-condition-builder__empty">
						{ __( 'No conditions yet. Add a group to start.', 'gutena-forms' ) }
					</p>
				) }

				{ draft.map( ( group, groupIndex ) => (
					<div className="gf-condition-group" key={ groupIndex }>
						{ groupIndex > 0 && (
							<div className="gf-condition-group__or">
								<span className="gf-condition-group__or-line" />
								<span className="gf-condition-group__or-text">{ __( 'OR', 'gutena-forms' ) }</span>
								<span className="gf-condition-group__or-line" />
							</div>
						) }
						{ group.map( ( condition, rowIndex ) => (
							<ConditionRow
								key={ rowIndex }
								condition={ condition }
								siblings={ siblings }
								onChange={ ( patch ) => updateCondition( groupIndex, rowIndex, patch ) }
								onRemove={ () => removeCondition( groupIndex, rowIndex ) }
								onAdd={ () => addCondition( groupIndex ) }
							/>
						) ) }
					</div>
				) ) }

				<Button
					variant="primary"
					text={ __( 'OR Group', 'gutena-forms' ) }
					onClick={ addGroup }
					className="gf-condition-builder__add-group"
				/>
			</div>

			<div className="gf-condition-builder__footer">
				<Button variant="secondary" text={ __( 'Cancel', 'gutena-forms' ) } onClick={ onClose } />
				<Button
					variant="primary"
					text={ __( 'Set Rules', 'gutena-forms' ) }
					onClick={ () => {
						onSave( sanitizeDraft( draft ) );
						onClose();
					} }
				/>
			</div>
		</Modal>
	);
}

function cloneGroups( groups ) {
	if ( ! Array.isArray( groups ) ) {
		return [];
	}
	return groups.map( ( g ) => ( Array.isArray( g ) ? g.map( ( c ) => ( { ...c } ) ) : [] ) );
}

function sanitizeDraft( draft ) {
	// Drop fully empty conditions and empty groups before persisting.
	return draft
		.map( ( g ) => g.filter( ( c ) => c && c.field ) )
		.filter( ( g ) => g.length > 0 );
}
