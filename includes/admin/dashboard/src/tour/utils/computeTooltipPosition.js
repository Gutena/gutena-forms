/**
 * Compute tooltip coordinates with viewport flip + clamp.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

const OPPOSITE_PLACEMENT = {
	top: 'bottom',
	bottom: 'top',
	left: 'right',
	right: 'left',
};

/**
 * @param {number} value
 * @param {number} min
 * @param {number} max
 * @returns {number}
 */
function clamp( value, min, max ) {
	return Math.min( Math.max( value, min ), max );
}

/**
 * @param {DOMRect} targetRect
 * @param {string} placement
 * @param {number} tooltipWidth
 * @param {number} tooltipHeight
 * @param {number} gap
 * @param {number} margin
 * @returns {{ top: number, left: number, placement: string }}
 */
function getCoordinatesForPlacement(
	targetRect,
	placement,
	tooltipWidth,
	tooltipHeight,
	gap,
	margin
) {
	const centerX = targetRect.left + targetRect.width / 2;
	const centerY = targetRect.top + targetRect.height / 2;

	switch ( placement ) {
		case 'top':
			return {
				top: targetRect.top - gap - tooltipHeight,
				left: centerX - tooltipWidth / 2,
				placement,
			};
		case 'bottom':
			return {
				top: targetRect.bottom + gap,
				left: centerX - tooltipWidth / 2,
				placement,
			};
		case 'left':
			return {
				top: centerY - tooltipHeight / 2,
				left: targetRect.left - gap - tooltipWidth,
				placement,
			};
		case 'right':
		default:
			return {
				top: centerY - tooltipHeight / 2,
				left: targetRect.right + gap,
				placement: placement === 'left' ? 'left' : 'right',
			};
	}
}

/**
 * @param {number} top
 * @param {number} left
 * @param {number} width
 * @param {number} height
 * @param {number} margin
 * @returns {boolean}
 */
function fitsViewport( top, left, width, height, margin ) {
	const viewportWidth = window.innerWidth;
	const viewportHeight = window.innerHeight;

	return (
		top >= margin &&
		left >= margin &&
		top + height <= viewportHeight - margin &&
		left + width <= viewportWidth - margin
	);
}

/**
 * @param {DOMRect} targetRect
 * @param {string} preferredPlacement
 * @param {number} tooltipWidth
 * @param {number} tooltipHeight
 * @param {Object} [options]
 * @returns {{ top: number, left: number, placement: string }}
 */
export function computeTooltipPosition(
	targetRect,
	preferredPlacement,
	tooltipWidth,
	tooltipHeight,
	options = {}
) {
	const gap = options.gap ?? 14;
	const margin = options.margin ?? 16;
	const placement = [ 'top', 'bottom', 'left', 'right' ].includes(
		preferredPlacement
	)
		? preferredPlacement
		: 'bottom';

	let coords = getCoordinatesForPlacement(
		targetRect,
		placement,
		tooltipWidth,
		tooltipHeight,
		gap,
		margin
	);

	if (
		! fitsViewport(
			coords.top,
			coords.left,
			tooltipWidth,
			tooltipHeight,
			margin
		)
	) {
		const flipped = OPPOSITE_PLACEMENT[ placement ];

		if ( flipped ) {
			const flippedCoords = getCoordinatesForPlacement(
				targetRect,
				flipped,
				tooltipWidth,
				tooltipHeight,
				gap,
				margin
			);

			if (
				fitsViewport(
					flippedCoords.top,
					flippedCoords.left,
					tooltipWidth,
					tooltipHeight,
					margin
				)
			) {
				coords = flippedCoords;
			}
		}
	}

	const viewportWidth = window.innerWidth;
	const viewportHeight = window.innerHeight;

	return {
		top: clamp( coords.top, margin, viewportHeight - tooltipHeight - margin ),
		left: clamp( coords.left, margin, viewportWidth - tooltipWidth - margin ),
		placement: coords.placement,
	};
}
