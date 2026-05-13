/**
 * Front-end: custom dropdown UI synced to native <select> for gutena/dropdown-field.
 * Dispatches input/change on the native select for existing Gutena form validation.
 */

const ATTR_ROOT = 'data-gf-dropdown-custom';
const ATTR_INIT = 'data-gf-dropdown-initialized';

function dispatchSyncedEvents( nativeSelect ) {
	nativeSelect.dispatchEvent( new Event( 'input', { bubbles: true } ) );
	nativeSelect.dispatchEvent( new Event( 'change', { bubbles: true } ) );
}

/**
 * @param {HTMLElement} wrap
 */
function initDropdownField( wrap ) {
	if ( wrap.getAttribute( ATTR_INIT ) === '1' ) {
		return;
	}
	wrap.setAttribute( ATTR_INIT, '1' );

	const nativeSelect = wrap.querySelector(
		'select.gf-dropdown-custom__native'
	);
	const trigger = wrap.querySelector( '.gf-dropdown-custom__trigger' );
	const valueEl = wrap.querySelector( '.gf-dropdown-custom__value' );
	const popover = wrap.querySelector( '.gf-dropdown-custom__popover' );
	const listbox = wrap.querySelector( '[role="listbox"]' );

	if ( ! nativeSelect || ! trigger || ! valueEl || ! popover || ! listbox ) {
		return;
	}

	let open = false;
	/** @type {number} index into native options / optionEls */
	let activeIndex = 0;
	/** @type {HTMLElement[]} */
	let optionEls = [];

	const getEnabledOptionIndices = () => {
		const indices = [];
		nativeSelect.querySelectorAll( 'option' ).forEach( ( opt, i ) => {
			if ( ! opt.disabled ) {
				indices.push( i );
			}
		} );
		return indices;
	};

	const setActiveIndex = ( optionIndex ) => {
		if ( ! optionEls.length ) {
			return;
		}
		const enabled = getEnabledOptionIndices();
		if ( ! enabled.length ) {
			return;
		}
		let pos = enabled.indexOf( optionIndex );
		if ( pos === -1 ) {
			pos = 0;
		}
		activeIndex = enabled[ pos ];
		optionEls.forEach( ( el, i ) => {
			el.classList.toggle( 'is-active', i === activeIndex );
		} );
		const activeEl = optionEls[ activeIndex ];
		if ( activeEl ) {
			listbox.setAttribute( 'aria-activedescendant', activeEl.id );
		}
	};

	const rebuildOptions = () => {
		listbox.innerHTML = '';
		optionEls = [];
		const opts = nativeSelect.querySelectorAll( 'option' );
		opts.forEach( ( opt, index ) => {
			const li = document.createElement( 'li' );
			li.setAttribute( 'role', 'option' );
			li.id = `${ nativeSelect.id }-opt-${ index }`;
			li.dataset.value = opt.value;
			li.textContent = opt.textContent;
			if ( opt.disabled ) {
				li.setAttribute( 'aria-disabled', 'true' );
			}
			li.className = 'gf-dropdown-custom__option';
			listbox.appendChild( li );
			optionEls.push( li );
		} );
	};

	const syncUIFromNative = () => {
		const selected = nativeSelect.options[ nativeSelect.selectedIndex ];
		if ( selected ) {
			valueEl.textContent = selected.textContent;
		} else {
			valueEl.textContent = '';
		}
		activeIndex =
			nativeSelect.selectedIndex >= 0 ? nativeSelect.selectedIndex : 0;
		optionEls.forEach( ( el, i ) => {
			const opt = nativeSelect.options[ i ];
			el.setAttribute(
				'aria-selected',
				opt && opt.selected ? 'true' : 'false'
			);
			el.classList.toggle( 'is-active', i === activeIndex );
		} );
		const activeEl = optionEls[ activeIndex ];
		if ( activeEl ) {
			listbox.setAttribute( 'aria-activedescendant', activeEl.id );
		}
	};

	const close = () => {
		if ( ! open ) {
			return;
		}
		open = false;
		popover.hidden = true;
		trigger.setAttribute( 'aria-expanded', 'false' );
		wrap.classList.remove( 'is-open' );
		document.removeEventListener( 'click', onDocClick, true );
	};

	function onDocClick( e ) {
		if ( ! wrap.contains( e.target ) ) {
			close();
		}
	}

	const openList = () => {
		if ( open ) {
			return;
		}
		open = true;
		popover.hidden = false;
		trigger.setAttribute( 'aria-expanded', 'true' );
		wrap.classList.add( 'is-open' );
		const start =
			nativeSelect.selectedIndex >= 0 ? nativeSelect.selectedIndex : 0;
		setActiveIndex( start );
		document.addEventListener( 'click', onDocClick, true );
	};

	const selectIndex = ( index ) => {
		const opt = nativeSelect.options[ index ];
		if ( ! opt || opt.disabled ) {
			return;
		}
		nativeSelect.selectedIndex = index;
		syncUIFromNative();
		dispatchSyncedEvents( nativeSelect );
		close();
		trigger.focus();
	};

	rebuildOptions();
	syncUIFromNative();

	nativeSelect.addEventListener( 'change', () => {
		rebuildOptions();
		syncUIFromNative();
	} );

	const form = wrap.closest( 'form' );
	if ( form ) {
		form.addEventListener( 'reset', () => {
			window.setTimeout( () => {
				rebuildOptions();
				syncUIFromNative();
			}, 0 );
		} );
	}

	trigger.addEventListener( 'click', ( e ) => {
		e.preventDefault();
		if ( open ) {
			close();
		} else {
			openList();
		}
	} );

	listbox.addEventListener( 'click', ( e ) => {
		const li = e.target.closest( '[role="option"]' );
		if ( ! li || ! listbox.contains( li ) ) {
			return;
		}
		const idx = optionEls.indexOf( li );
		if ( idx >= 0 ) {
			selectIndex( idx );
		}
	} );

	trigger.addEventListener( 'keydown', ( e ) => {
		const enabled = getEnabledOptionIndices();
		if ( ! enabled.length ) {
			return;
		}

		if ( e.key === 'ArrowDown' ) {
			e.preventDefault();
			if ( ! open ) {
				openList();
			} else {
				const pos = enabled.indexOf( activeIndex );
				const next = enabled[ Math.min( enabled.length - 1, pos + 1 ) ];
				setActiveIndex( next );
				optionEls[ activeIndex ]?.scrollIntoView( {
					block: 'nearest',
				} );
			}
		} else if ( e.key === 'ArrowUp' ) {
			e.preventDefault();
			if ( ! open ) {
				openList();
			} else {
				const pos = enabled.indexOf( activeIndex );
				const next = enabled[ Math.max( 0, pos - 1 ) ];
				setActiveIndex( next );
				optionEls[ activeIndex ]?.scrollIntoView( {
					block: 'nearest',
				} );
			}
		} else if ( e.key === 'Enter' || e.key === ' ' ) {
			e.preventDefault();
			if ( open ) {
				selectIndex( activeIndex );
			} else {
				openList();
			}
		} else if ( e.key === 'Escape' ) {
			if ( open ) {
				e.preventDefault();
				close();
			}
		} else if ( e.key === 'Home' && open ) {
			e.preventDefault();
			setActiveIndex( enabled[ 0 ] );
			optionEls[ activeIndex ]?.scrollIntoView( { block: 'nearest' } );
		} else if ( e.key === 'End' && open ) {
			e.preventDefault();
			setActiveIndex( enabled[ enabled.length - 1 ] );
			optionEls[ activeIndex ]?.scrollIntoView( { block: 'nearest' } );
		}
	} );

	listbox.addEventListener( 'keydown', ( e ) => {
		if ( e.key === 'Escape' ) {
			e.preventDefault();
			close();
			trigger.focus();
		} else if ( e.key === 'Enter' ) {
			e.preventDefault();
			selectIndex( activeIndex );
		}
	} );
}

function initAll() {
	document.querySelectorAll( `[${ ATTR_ROOT }]` ).forEach( ( el ) => {
		initDropdownField( el );
	} );
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', initAll );
} else {
	initAll();
}
