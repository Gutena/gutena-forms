/**
 * Spotlight overlay with rounded cutout and pulse indicator.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { useEffect, useMemo, useState } from '@wordpress/element';

const SPOTLIGHT_RADIUS = 10;

/**
 * Track viewport dimensions for the SVG overlay mask.
 *
 * @returns {{ width: number, height: number }}
 */
function useViewportSize() {
	const [ size, setSize ] = useState( () => ( {
		width: window.innerWidth,
		height: window.innerHeight,
	} ) );

	useEffect( () => {
		const updateSize = () => {
			setSize( {
				width: window.innerWidth,
				height: window.innerHeight,
			} );
		};

		window.addEventListener( 'resize', updateSize );

		return () => {
			window.removeEventListener( 'resize', updateSize );
		};
	}, [] );

	return size;
}

/**
 * @param {Object} props
 * @param {Object|null} props.rect Spotlight rectangle (viewport coordinates).
 * @param {string} props.sectionColor Accent colour for border and pulse dot.
 * @param {boolean} props.isWaiting Whether the target is still resolving.
 */
const TourSpotlight = ( { rect, sectionColor, isWaiting } ) => {
	const maskId = useMemo(
		() => `gf-tour-mask-${ Math.random().toString( 36 ).slice( 2 ) }`,
		[]
	);

	const { width: viewportWidth, height: viewportHeight } = useViewportSize();

	return (
		<div
			className="gutena-forms-tour-spotlight"
			aria-hidden="true"
		>
			<svg
				className="gutena-forms-tour-spotlight__svg"
				width={ viewportWidth }
				height={ viewportHeight }
				xmlns="http://www.w3.org/2000/svg"
			>
				<defs>
					<mask id={ maskId }>
						<rect
							x="0"
							y="0"
							width={ viewportWidth }
							height={ viewportHeight }
							fill="white"
						/>
						{ rect && ! isWaiting && (
							<rect
								x={ rect.left }
								y={ rect.top }
								width={ rect.width }
								height={ rect.height }
								rx={ SPOTLIGHT_RADIUS }
								ry={ SPOTLIGHT_RADIUS }
								fill="black"
							/>
						) }
					</mask>
				</defs>
				<rect
					x="0"
					y="0"
					width={ viewportWidth }
					height={ viewportHeight }
					className="gutena-forms-tour-spotlight__dim"
					mask={ `url(#${ maskId })` }
				/>
			</svg>

			{ rect && ! isWaiting && (
				<>
					<div
						className="gutena-forms-tour-spotlight__ring"
						style={ {
							top: `${ rect.top }px`,
							left: `${ rect.left }px`,
							width: `${ rect.width }px`,
							height: `${ rect.height }px`,
							'--gf-tour-accent': sectionColor,
						} }
					/>
					<span
						className="gutena-forms-tour-spotlight__pulse"
						style={ {
							top: `${ rect.top - 4 }px`,
							left: `${ rect.left + rect.width - 4 }px`,
							'--gf-tour-accent': sectionColor,
						} }
					/>
				</>
			) }
		</div>
	);
};

export default TourSpotlight;
