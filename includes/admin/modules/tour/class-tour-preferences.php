<?php
/**
 * Per-user product tour preferences (user meta).
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Tour_Preferences' ) ) :
	/**
	 * Read/write tour preference payloads stored in user meta.
	 *
	 * @since 1.9.0
	 */
	class Gutena_Forms_Tour_Preferences {
		/**
		 * User meta key.
		 *
		 * @var string
		 */
		const META_KEY = 'gutena_forms_tour_preferences';

		/**
		 * Tour preference schema version.
		 *
		 * @var string
		 */
		const VERSION = '1.0.0';

		/**
		 * Total number of tour steps (indexes 0–15).
		 *
		 * @var int
		 */
		const STEP_COUNT = 16;

		/**
		 * Get default preferences for a user who has never started the tour.
		 *
		 * @since 1.9.0
		 * @return array
		 */
		public static function get_defaults() {
			$steps = array();

			for ( $i = 0; $i < self::STEP_COUNT; $i++ ) {
				$steps[ (string) $i ] = false;
			}

			return array(
				'tour_status'        => 'not_started',
				'current_step'       => 0,
				'steps'              => $steps,
				'do_not_show_again'  => false,
				'version'            => self::VERSION,
			);
		}

		/**
		 * Get merged preferences for the current (or supplied) user.
		 *
		 * @since 1.9.0
		 * @param int $user_id User ID. Defaults to current user.
		 * @return array
		 */
		public static function get( $user_id = 0 ) {
			$user_id = $user_id ? absint( $user_id ) : get_current_user_id();

			if ( ! $user_id ) {
				return self::get_defaults();
			}

			$stored = get_user_meta( $user_id, self::META_KEY, true );

			if ( ! is_array( $stored ) || empty( $stored ) ) {
				return self::get_defaults();
			}

			return self::sanitize_preferences( $stored );
		}

		/**
		 * Merge and persist preferences for a user.
		 *
		 * @since 1.9.0
		 * @param array $patch   Partial preference payload.
		 * @param int   $user_id User ID. Defaults to current user.
		 * @return array Saved preferences.
		 */
		public static function save( array $patch, $user_id = 0 ) {
			$user_id = $user_id ? absint( $user_id ) : get_current_user_id();

			if ( ! $user_id ) {
				return self::get_defaults();
			}

			$current = self::get( $user_id );
			$merged  = self::sanitize_preferences( array_merge( $current, $patch ) );

			update_user_meta( $user_id, self::META_KEY, $merged );

			return $merged;
		}

		/**
		 * Sanitize and normalize a preference payload.
		 *
		 * @since 1.9.0
		 * @param array $preferences Raw preferences.
		 * @return array
		 */
		public static function sanitize_preferences( array $preferences ) {
			$defaults = self::get_defaults();
			$allowed_statuses = array( 'not_started', 'active', 'skipped', 'completed' );

			$tour_status = isset( $preferences['tour_status'] )
				? sanitize_key( $preferences['tour_status'] )
				: $defaults['tour_status'];

			if ( ! in_array( $tour_status, $allowed_statuses, true ) ) {
				$tour_status = $defaults['tour_status'];
			}

			$current_step = isset( $preferences['current_step'] )
				? absint( $preferences['current_step'] )
				: $defaults['current_step'];

			if ( $current_step > ( self::STEP_COUNT - 1 ) ) {
				$current_step = self::STEP_COUNT - 1;
			}

			$steps = $defaults['steps'];
			if ( isset( $preferences['steps'] ) && is_array( $preferences['steps'] ) ) {
				foreach ( $steps as $index => $seen ) {
					if ( array_key_exists( $index, $preferences['steps'] ) ) {
						$steps[ $index ] = (bool) $preferences['steps'][ $index ];
					} elseif ( array_key_exists( (int) $index, $preferences['steps'] ) ) {
						$steps[ $index ] = (bool) $preferences['steps'][ (int) $index ];
					}
				}
			}

			return array(
				'tour_status'       => $tour_status,
				'current_step'      => $current_step,
				'steps'             => $steps,
				'do_not_show_again' => ! empty( $preferences['do_not_show_again'] ),
				'version'           => self::VERSION,
			);
		}
	}
endif;
