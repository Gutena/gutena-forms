/**
 * Position the tour tooltip relative to the active target.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { useEffect, useState } from '@wordpress/element';
import { computeTooltipPosition } from '../utils/computeTooltipPosition';

/**
 * @param {Object} params
 * @param {DOMRect|null} targetRect
 * @param {string} preferredPlacement
 * @param {import('react').RefObject<HTMLElement>} tooltipRef
 * @param {boolean} isActive
 * @returns {{ top: number, left: number, placement: string }|null}
 */
export function useTooltipPosition( {
	targetRect,
	preferredPlacement,
	tooltipRef,
	isActive,
} ) {
	const [ position, setPosition ] = useState( null );

	useEffect( () => {
		if ( ! isActive || ! targetRect || ! tooltipRef.current ) {
			setPosition( null );
			return undefined;
		}

		const updatePosition = () => {
			const tooltipElement = tooltipRef.current;

			if ( ! tooltipElement ) {
				return;
			}

			const { offsetWidth, offsetHeight } = tooltipElement;

			setPosition(
				computeTooltipPosition(
					targetRect,
					preferredPlacement,
					offsetWidth,
					offsetHeight
				)
			);
		};

		updatePosition();

		const resizeObserver =
			typeof ResizeObserver !== 'undefined'
				? new ResizeObserver( updatePosition )
				: null;

		if ( resizeObserver && tooltipRef.current ) {
			resizeObserver.observe( tooltipRef.current );
		}

		window.addEventListener( 'resize', updatePosition );
		window.addEventListener( 'scroll', updatePosition, true );

		return () => {
			if ( resizeObserver ) {
				resizeObserver.disconnect();
			}

			window.removeEventListener( 'resize', updatePosition );
			window.removeEventListener( 'scroll', updatePosition, true );
		};
	}, [ isActive, preferredPlacement, targetRect, tooltipRef ] );

	return position;
}
