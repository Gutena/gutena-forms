/**
 * Track a target element's viewport rectangle.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { useEffect, useState } from '@wordpress/element';

export const SPOTLIGHT_PADDING = 8;

/**
 * @param {HTMLElement|null} target
 * @param {boolean} isActive
 * @returns {DOMRect|null}
 */
export function useTargetRect( target, isActive ) {
	const [ rect, setRect ] = useState( null );

	useEffect( () => {
		if ( ! isActive || ! target ) {
			setRect( null );
			return undefined;
		}

		const updateRect = () => {
			const nextRect = target.getBoundingClientRect();

			setRect( {
				top: nextRect.top - SPOTLIGHT_PADDING,
				left: nextRect.left - SPOTLIGHT_PADDING,
				width: nextRect.width + SPOTLIGHT_PADDING * 2,
				height: nextRect.height + SPOTLIGHT_PADDING * 2,
				right: nextRect.right + SPOTLIGHT_PADDING,
				bottom: nextRect.bottom + SPOTLIGHT_PADDING,
			} );
		};

		updateRect();

		try {
			target.scrollIntoView( {
				block: 'nearest',
				inline: 'nearest',
				behavior: 'smooth',
			} );
		} catch ( error ) {
			// Ignore scroll errors in unsupported contexts.
		}

		const resizeObserver =
			typeof ResizeObserver !== 'undefined'
				? new ResizeObserver( updateRect )
				: null;

		if ( resizeObserver ) {
			resizeObserver.observe( target );
		}

		window.addEventListener( 'resize', updateRect );
		window.addEventListener( 'scroll', updateRect, true );

		return () => {
			if ( resizeObserver ) {
				resizeObserver.disconnect();
			}

			window.removeEventListener( 'resize', updateRect );
			window.removeEventListener( 'scroll', updateRect, true );
		};
	}, [ isActive, target ] );

	return rect;
}
