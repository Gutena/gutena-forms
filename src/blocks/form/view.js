/**
 * Conditional logic runtime evaluator (viewScript for gutena/forms).
 *
 * Runs on the frontend and in the editor preview iframe. Parses the
 * `data-gf-conditions` JSON emitted by `class-form-block.php` and toggles
 * the `gf-conditionally-hidden` class on each target field's
 * `.wp-block-gutena-field-group` wrapper, disabling all inputs inside
 * hidden fields so they are excluded from validation and submission.
 *
 * Operator keys mirror OPERATORS in src/shared/conditional-logic/operators.js.
 */

( function () {
	const ATTR_CONDITIONS = 'data-gf-conditions';
	const ATTR_INITIALIZED = 'data-gf-conditions-initialized';
	const HIDDEN_CLASS = 'gf-conditionally-hidden';
	const MAX_PASSES = 10;

	/**
	 * Operator test functions. Each receives the referenced field's read
	 * descriptor { values, isEmpty, checked } and the condition's value
	 * string. Returns boolean.
	 */
	const OPERATOR_TESTS = {
		is: ( r, v ) => firstValue( r ) === v,
		is_not: ( r, v ) => firstValue( r ) !== v,
		contains: ( r, v ) => String( firstValue( r ) ).indexOf( v ) !== -1,
		does_not_contain: ( r, v ) => String( firstValue( r ) ).indexOf( v ) === -1,
		starts_with: ( r, v ) => String( firstValue( r ) ).indexOf( v ) === 0,
		does_not_start_with: ( r, v ) => String( firstValue( r ) ).indexOf( v ) !== 0,
		ends_with: ( r, v ) => {
			const s = String( firstValue( r ) );
			return s.length >= v.length && s.indexOf( v ) === s.length - v.length;
		},
		does_not_end_with: ( r, v ) => {
			const s = String( firstValue( r ) );
			return ! ( s.length >= v.length && s.indexOf( v ) === s.length - v.length );
		},
		is_empty: ( r ) => r.isEmpty,
		is_not_empty: ( r ) => ! r.isEmpty,

		is_equal_to: ( r, v ) => num( firstValue( r ) ) === num( v ),
		is_not_equal_to: ( r, v ) => num( firstValue( r ) ) !== num( v ),
		greater_than: ( r, v ) => num( firstValue( r ) ) > num( v ),
		less_than: ( r, v ) => num( firstValue( r ) ) < num( v ),
		greater_than_or_equal_to: ( r, v ) => num( firstValue( r ) ) >= num( v ),
		less_than_or_equal_to: ( r, v ) => num( firstValue( r ) ) <= num( v ),
		is_before: ( r, v ) => compareDate( firstValue( r ), v ) < 0,
		is_after: ( r, v ) => compareDate( firstValue( r ), v ) > 0,

		is_any_of: ( r, v ) => intersects( r.values, splitValues( v ) ),
		is_not_any_of: ( r, v ) => ! intersects( r.values, splitValues( v ) ),
		is_every_of: ( r, v ) => containsAll( r.values, splitValues( v ) ),

		is_checked: ( r ) => r.checked === true,
		is_not_checked: ( r ) => r.checked !== true,
	};

	function firstValue( r ) {
		return r && r.values && r.values.length ? r.values[ 0 ] : '';
	}
	function num( v ) {
		const n = parseFloat( v );
		return isNaN( n ) ? 0 : n;
	}
	function splitValues( v ) {
		return String( v == null ? '' : v )
			.split( ',' )
			.map( ( s ) => s.trim() )
			.filter( Boolean );
	}
	function intersects( a, b ) {
		if ( ! a || ! b ) {
			return false;
		}
		return a.some( ( x ) => b.indexOf( x ) !== -1 );
	}
	function containsAll( a, b ) {
		if ( ! a || ! b || ! b.length ) {
			return false;
		}
		return b.every( ( x ) => a.indexOf( x ) !== -1 );
	}
	function compareDate( a, b ) {
		const ta = Date.parse( a );
		const tb = Date.parse( b );
		if ( isNaN( ta ) || isNaN( tb ) ) {
			return 0;
		}
		return ta < tb ? -1 : ta > tb ? 1 : 0;
	}

	function cssEscape( s ) {
		if ( window.CSS && 'function' === typeof window.CSS.escape ) {
			return window.CSS.escape( s );
		}
		return String( s ).replace( /[^a-zA-Z0-9_-]/g, function ( ch ) {
			return '\\' + ch;
		} );
	}

	/**
	 * Read the current value descriptor for a referenced field inside a form.
	 *
	 * @param {HTMLElement} form     Form element.
	 * @param {string}      nameAttr Field name attribute.
	 * @return {{values:string[], isEmpty:boolean, checked:boolean|null}}
	 */
	function readField( form, nameAttr ) {
		const els = form.querySelectorAll( '[name="' + cssEscape( nameAttr ) + '"]' );
		if ( ! els.length ) {
			return { values: [], isEmpty: true, checked: null };
		}

		const first = els[ 0 ];
		const tag = first.tagName.toLowerCase();
		const type = first.getAttribute( 'type' ) || '';

		if ( 'file' === type ) {
			let hasFiles = false;
			els.forEach( ( el ) => {
				if ( el.files && el.files.length ) {
					hasFiles = true;
				}
			} );
			return { values: [], isEmpty: ! hasFiles, checked: null };
		}

		if ( 'checkbox' === type || 'radio' === type ) {
			const checked = [];
			let singleChecked = null;
			els.forEach( ( el ) => {
				if ( el.checked ) {
					checked.push( el.value );
				}
			} );
			if ( els.length === 1 ) {
				singleChecked = els[ 0 ].checked;
			}
			return {
				values: checked,
				isEmpty: checked.length === 0,
				checked: singleChecked,
			};
		}

		if ( 'select' === tag ) {
			const values = [];
			els.forEach( ( el ) => {
				if ( el.selectedOptions && el.selectedOptions.length ) {
					for ( let i = 0; i < el.selectedOptions.length; i++ ) {
						values.push( el.selectedOptions[ i ].value );
					}
				} else if ( el.value ) {
					values.push( el.value );
				}
			} );
			return { values, isEmpty: values.length === 0, checked: null };
		}

		const values = [];
		els.forEach( ( el ) => {
			if ( el.value ) {
				values.push( el.value );
			}
		} );
		return { values, isEmpty: values.length === 0, checked: null };
	}

	function evaluateCondition( form, condition ) {
		if ( ! condition || ! condition.field || ! condition.operator ) {
			return false;
		}
		const read = readField( form, condition.field );
		const test = OPERATOR_TESTS[ condition.operator ];
		if ( ! test ) {
			return false;
		}
		try {
			return !! test( read, condition.value );
		} catch ( e ) {
			return false;
		}
	}

	function evaluateGroups( form, entry ) {
		const groups = entry && entry.groups;
		if ( ! groups || ! groups.length ) {
			return false;
		}
		for ( let g = 0; g < groups.length; g++ ) {
			const group = groups[ g ];
			if ( ! group || ! group.length ) {
				continue;
			}
			let allMatch = true;
			for ( let c = 0; c < group.length; c++ ) {
				if ( ! evaluateCondition( form, group[ c ] ) ) {
					allMatch = false;
					break;
				}
			}
			if ( allMatch ) {
				return true;
			}
		}
		return false;
	}

	function findWrapper( form, nameAttr ) {
		const el = form.querySelector( '[name="' + cssEscape( nameAttr ) + '"]' );
		if ( ! el ) {
			return null;
		}
		return el.closest( '.wp-block-gutena-field-group' );
	}

	/**
	 * Apply visibility to a wrapper: toggle hidden class + disable inputs.
	 * Preserves the original disabled state of inputs that were already
	 * disabled (e.g. by other field scripts) so it can be restored on show.
	 *
	 * @param {HTMLElement} wrapper
	 * @param {boolean}     visible
	 */
	function applyVisibility( wrapper, visible ) {
		if ( ! wrapper ) {
			return;
		}
		if ( visible ) {
			wrapper.classList.remove( HIDDEN_CLASS );
		} else {
			wrapper.classList.add( HIDDEN_CLASS );
		}
		const inputs = wrapper.querySelectorAll( 'input, select, textarea, button' );
		inputs.forEach( ( input ) => {
			if ( visible ) {
				if ( 'true' === input.getAttribute( 'data-gf-was-disabled' ) ) {
					input.removeAttribute( 'data-gf-was-disabled' );
				} else {
					input.disabled = false;
				}
			} else {
				if ( input.disabled ) {
					input.setAttribute( 'data-gf-was-disabled', 'true' );
				} else {
					input.disabled = true;
				}
			}
		} );
	}

	/**
	 * Compute visibility for a target field given its conditions entry.
	 *
	 * @param {HTMLElement} form
	 * @param {Object}      entry { action, groups }
	 * @return {boolean} Whether the field should be visible.
	 */
	function computeVisibility( form, entry ) {
		const matched = evaluateGroups( form, entry );
		// action=show -> visible when matched; action=hide -> visible when not matched.
		return 'hide' === entry.action ? ! matched : matched;
	}

	/**
	 * Evaluate every conditional field once and apply visibility.
	 * Returns true when any field's visibility changed this pass.
	 *
	 * @param {HTMLElement} form
	 * @param {Object}      conditions Map of nameAttr => entry.
	 * @return {boolean}
	 */
	function evaluatePass( form, conditions ) {
		let changed = false;
		Object.keys( conditions ).forEach( ( nameAttr ) => {
			const wrapper = findWrapper( form, nameAttr );
			if ( ! wrapper ) {
				return;
			}
			const visible = computeVisibility( form, conditions[ nameAttr ] );
			const wasHidden = wrapper.classList.contains( HIDDEN_CLASS );
			if ( visible === ! wasHidden ) {
				return; // no change
			}
			applyVisibility( wrapper, visible );
			changed = true;
		} );
		return changed;
	}

	/**
	 * Iterate evaluation until stable (fixed-point) or MAX_PASSES reached.
	 * Chained fields (A depends on B, B depends on C) settle across passes.
	 *
	 * @param {HTMLElement} form
	 * @param {Object}      conditions
	 */
	function evaluate( form, conditions ) {
		let pass = 0;
		while ( pass < MAX_PASSES ) {
			if ( ! evaluatePass( form, conditions ) ) {
				break;
			}
			pass++;
		}
	}

	function parseConditions( form ) {
		const raw = form.getAttribute( ATTR_CONDITIONS );
		if ( ! raw ) {
			return null;
		}
		try {
			const parsed = JSON.parse( raw );
			return parsed && 'object' === typeof parsed ? parsed : null;
		} catch ( e ) {
			return null;
		}
	}

	/**
	 * Initialise conditional logic for a single form element.
	 *
	 * @param {HTMLElement} form
	 */
	function initForm( form ) {
		if ( form.getAttribute( ATTR_INITIALIZED ) === '1' ) {
			return;
		}
		form.setAttribute( ATTR_INITIALIZED, '1' );

		const conditions = parseConditions( form );
		if ( ! conditions ) {
			return;
		}

		// Initial evaluation.
		evaluate( form, conditions );

		// Re-evaluate on any input/change event bubbling to the form.
		const onInput = () => evaluate( form, conditions );
		form.addEventListener( 'input', onInput );
		form.addEventListener( 'change', onInput );

		// Editor preview: the iframe content is re-rendered when block
		// attributes change. A MutationObserver re-runs evaluation so the
		// preview reflects the new default values without a full reload.
		if ( 'undefined' !== typeof MutationObserver ) {
			const observer = new MutationObserver( () => evaluate( form, conditions ) );
			observer.observe( form, { childList: true, subtree: true, attributes: true, attributeFilter: [ 'value', 'checked', 'selected' ] } );
		}
	}

	function initAll( root ) {
		const scope = root || document;
		const forms = scope.querySelectorAll( '[' + ATTR_CONDITIONS + ']' );
		forms.forEach( initForm );
	}

	function ready( fn ) {
		if ( 'loading' !== document.readyState ) {
			fn();
			return;
		}
		document.addEventListener( 'DOMContentLoaded', fn );
	}

	ready( function () {
		initAll( document );

		// Watch for forms inserted dynamically (e.g. editor preview re-mount).
		if ( 'undefined' !== typeof MutationObserver ) {
			const bodyObserver = new MutationObserver( ( mutations ) => {
				mutations.forEach( ( m ) => {
					m.addedNodes.forEach( ( node ) => {
						if ( 1 !== node.nodeType ) {
							return;
						}
						if ( node.hasAttribute && node.hasAttribute( ATTR_CONDITIONS ) ) {
							initForm( node );
						}
						if ( node.querySelectorAll ) {
							initAll( node );
						}
					} );
				} );
			} );
			bodyObserver.observe( document.body, { childList: true, subtree: true } );
		}
	} );
} )();
