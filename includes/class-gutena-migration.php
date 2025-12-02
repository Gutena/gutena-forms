<?php
/**
 * Gutena Forms Migration Class
 *
 * @since 1.4.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Migration' ) ) :
	/**
	 * Gutena Forms Migration Class
	 *
	 * @since 1.4.0
	 */
	class Gutena_Forms_Migration {
		/**
		 * Singleton instance of the class.
		 *
		 * @since 1.4.0
		 * @var Gutena_Forms_Migration The single instance of the class.
		 */
		private static $instance;

		/**
		 * Migration option key
		 *
		 * @since 1.4.0
		 */
		const MIGRATION_OPTION = 'gutena_forms_migration_status';

		/**
		 * Forms to migrate option key
		 *
		 * @since 1.4.0
		 */
		const FORMS_TO_MIGRATE_OPTION = 'gutena_forms_to_migrategutena_forms_to_migrate';

		/**
		 * Get the singleton instance of the class.
		 *
		 * @since 1.4.0
		 * @return Gutena_Forms_Migration
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		private function __construct() {
			$migration_needed = $this->needs_migration();
			if ( $migration_needed ) {
				add_action( 'init', array( $this, 'register_cron_event' ) );
				add_action( 'gutena_forms_migration_cron_event', array( $this, 'perform_migration' ) );
			}
		}

		public function register_cron_event() {
			if ( ! wp_next_scheduled( 'gutena_forms_migration_cron_event' ) ) {
				wp_schedule_event( time(), 'hourly', 'gutena_forms_migration_cron_event' );
			}
		}

		public function perform_migration() {
			$forms_to_migrate = get_option( self::FORMS_TO_MIGRATE_OPTION, array() );
			if ( empty( $forms_to_migrate ) ) {
				return;
			}

			$batch_size  = 10;
			$forms_batch = array_splice( $forms_to_migrate, 0, $batch_size );
			$remaining  = array_slice( $forms_to_migrate, $batch_size );

			foreach ( $forms_batch as $batch ) {
				$this->migrate_single_form( $batch );
			}

			if ( ! empty( $remaining ) ) {
				update_option( self::FORMS_TO_MIGRATE_OPTION, $remaining );
			} else {
				update_option( self::FORMS_TO_MIGRATE_OPTION, [] );
				$timestamp = wp_next_scheduled( 'gutena_forms_migration_cron_event' );
				if ( $timestamp ) {
					wp_unschedule_event( $timestamp, 'gutena_forms_migration_cron_event' );
				}
			}
		}

		private function migrate_single_form( $form_data ) {
			$form_id        = $form_data['form_id'];
			$form_block     = $form_data['form_block'];
			$parent_post_id = $form_data['parent_post_id'];
			$form_name      = $form_data['form_name'];

			$existing_forms = get_posts(
				array(
					'post_type'      => 'gutena_forms',
					'posts_per_page' => 1,
					'meta_key'       => 'gutena_form_id',
					'meta_value'     => $form_id,
					'post_status'    => 'any',
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $existing_forms ) ) {
				// Form already exists, just update connected posts
				$cpt_post_id = $existing_forms[0];
			} else {
				// Create new CPT entry
				$cpt_post_id = wp_insert_post(
					array(
						'post_type'    => 'gutena_forms',
						'post_title'   => $form_name,
						'post_status'  => 'publish',
						'post_content' => serialize_block( $form_block ),
					),
					true
				);

				if ( is_wp_error( $cpt_post_id ) ) {
					return false;
				}

				// Save form ID as meta
				update_post_meta( $cpt_post_id, 'gutena_form_id', $form_id );
			}

			$connected_posts = get_post_meta( $cpt_post_id, '_gutena_connected_posts', true );
			if ( ! is_array( $connected_posts ) ) {
				$connected_posts = array();
			}

			if ( ! in_array( $parent_post_id, $connected_posts, true ) ) {
				$connected_posts[] = $parent_post_id;
				update_post_meta( $cpt_post_id, '_gutena_connected_posts', $connected_posts );
			}

			return true;
		}

		/**
		 * Check if migration is needed
		 *
		 * @since 1.4.0
		 * @return bool
		 */
		public function needs_migration() {
			$forms_to_migrate = get_option( self::FORMS_TO_MIGRATE_OPTION, false );
			if ( false !== $forms_to_migrate ) {
				return ! empty( $forms_to_migrate ) ? $forms_to_migrate : false;
			}

			$forms_to_migrate = $this->find_forms_to_migrate();

			// Cache the result
			update_option( self::FORMS_TO_MIGRATE_OPTION, $forms_to_migrate );

			return ! empty( $forms_to_migrate ) ? $forms_to_migrate : false;
		}

		/**
		 * Find forms that need migration
		 *
		 * @since 1.4.0
		 * @return array
		 */
		private function find_forms_to_migrate() {
			$forms_to_migrate = array();
			$posts_with_forms = get_posts(
				array(
					'post_type'      => array( 'post', 'page' ),
					'posts_per_page' => -1,
					'post_status'    => 'any',
				)
			);

			foreach ( $posts_with_forms as $post ) {
				if ( ! has_block( 'gutena/forms', $post ) ) {
					continue;
				}

				$blocks 	 = parse_blocks( $post->post_content );
				$form_blocks = $this->extract_form_blocks( $blocks );

				foreach ( $form_blocks as $form_block ) {
					if ( empty( $form_block['attrs']['formID'] ) ) {
						continue;
					}

					$form_id	   = sanitize_key( $form_block['attrs']['formID'] );
					$existing_form = get_posts(
						array(
							'post_type'      => 'gutena_forms',
							'posts_per_page' => 1,
							'meta_key'       => 'gutena_form_id',
							'meta_value'     => $form_id,
							'post_status'    => 'any',
							'fields'         => 'ids',
						)
					);

					if ( empty( $existing_form ) ) {
						$forms_to_migrate[] = array(
							'form_id'        => $form_id,
							'form_block'     => $form_block,
							'parent_post_id' => $post->ID,
							'form_name'      => isset( $form_block['attrs']['formName'] )
								? sanitize_text_field( $form_block['attrs']['formName'] )
								: 'Contact Form',
						);
					}
				}
			}

			$unique_forms = array();
			$seen_form_ids = array();
			foreach ( $forms_to_migrate as $form_data ) {
				if ( ! in_array( $form_data['form_id'], $seen_form_ids, true ) ) {
					$unique_forms[] = $form_data;
					$seen_form_ids[] = $form_data['form_id'];
				}
			}

			return $unique_forms;
		}

		/**
		 * Extract Gutena Forms blocks from parsed blocks
		 *
		 * @since 1.4.0
		 * @param array $blocks Parsed blocks array.
		 *
		 * @return array
		 */
		private function extract_form_blocks( $blocks ) {
			$form_blocks = array();

			foreach ( $blocks as $block ) {
				if ( isset( $block['blockName'] ) && 'gutena/forms' === $block['blockName'] ) {
					$form_blocks[] = $block;
				}

				// Recursively check inner blocks
				if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
					$inner_forms = $this->extract_form_blocks( $block['innerBlocks'] );
					$form_blocks = array_merge( $form_blocks, $inner_forms );
				}
			}

			return $form_blocks;
		}
	}

	Gutena_Forms_Migration::get_instance();
endif;
