/**
 * Focus trap and Escape handling for modal dialogs.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

import { useEffect } from '@wordpress/element';

const FOCUSABLE_SELECTOR = [
	'a[href]',
	'button:not([disabled])',
	'textarea:not([disabled])',
	'input:not([disabled])',
	'select:not([disabled])',
	'[tabindex]:not([tabindex="-1"])',
].join( ', ' );

/**
 * @param {boolean}      isOpen     Whether the dialog is open.
 * @param {Object}       containerRef Ref to the dialog container element.
 * @param {Function}     onClose    Close handler (Escape key).
 */
export function useDialogFocusTrap( isOpen, containerRef, onClose ) {
	useEffect( () => {
		if ( ! isOpen || ! containerRef.current ) {
			return undefined;
		}

		const previouslyFocused = document.activeElement;

		const focusableElements = () => {
			return Array.from( containerRef.current.querySelectorAll( FOCUSABLE_SELECTOR ) );
		};

		const timer = setTimeout( () => {
			const elements = focusableElements();
			if ( elements.length ) {
				elements[ 0 ].focus();
			}
		}, 10 );

		const handleKeyDown = ( event ) => {
			if ( 'Escape' === event.key ) {
				event.preventDefault();
				onClose();
				return;
			}

			if ( 'Tab' !== event.key ) {
				return;
			}

			const elements = focusableElements();

			if ( ! elements.length ) {
				event.preventDefault();
				return;
			}

			const first = elements[ 0 ];
			const last = elements[ elements.length - 1 ];

			if ( event.shiftKey && document.activeElement === first ) {
				event.preventDefault();
				last.focus();
			} else if ( ! event.shiftKey && document.activeElement === last ) {
				event.preventDefault();
				first.focus();
			}
		};

		document.addEventListener( 'keydown', handleKeyDown );

		return () => {
			clearTimeout( timer );
			document.removeEventListener( 'keydown', handleKeyDown );

			if ( previouslyFocused && previouslyFocused.focus ) {
				previouslyFocused.focus();
			}
		};
	}, [ isOpen, onClose, containerRef ] );
}
