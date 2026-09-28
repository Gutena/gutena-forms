/**
 * Product tour React context.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { createContext, useContext } from '@wordpress/element';

export const TourContext = createContext( null );

/**
 * @returns {Object|null}
 */
export function useTour() {
	const context = useContext( TourContext );

	if ( ! context ) {
		throw new Error( 'useTour must be used within a TourProvider.' );
	}

	return context;
}
