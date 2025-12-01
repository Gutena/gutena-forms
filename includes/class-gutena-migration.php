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
		 */
		const MIGRATION_OPTION = 'gutena_forms_migration_status';

		/**
		 * Forms to migrate option key
		 */
		const FORMS_TO_MIGRATE_OPTION = 'gutena_forms_to_migrate';

		/**
		 * Private constructor to prevent direct instantiation.
		 *
		 * @since 1.4.0
		 */
		private function __construct() {
			add_action( 'admin_notices', array( $this, 'show_migration_notice' ) );
			add_action( 'wp_ajax_gutena_forms_start_migration', array( $this, 'ajax_start_migration' ) );
			add_action( 'wp_ajax_gutena_forms_dismiss_migration', array( $this, 'ajax_dismiss_migration' ) );
			add_action( 'wp_ajax_gutena_forms_check_migration_status', array( $this, 'ajax_check_migration_status' ) );
			add_action( 'gutena_forms_migrate_forms', array( $this, 'process_migration_batch' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_migration_scripts' ) );
		}

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

		/**
		 * Check if migration is needed
		 *
		 * @return bool|array False if not needed, array of forms to migrate if needed
		 */
		public function needs_migration() {
			// Check if migration already completed
			$migration_status = get_option( self::MIGRATION_OPTION, array() );
			if ( ! empty( $migration_status['completed'] ) ) {
				return false;
			}

			// Check if migration was dismissed
			if ( ! empty( $migration_status['dismissed'] ) ) {
				return false;
			}

			// Get cached forms to migrate
			$forms_to_migrate = get_option( self::FORMS_TO_MIGRATE_OPTION, false );
			if ( false !== $forms_to_migrate ) {
				return ! empty( $forms_to_migrate ) ? $forms_to_migrate : false;
			}

			// Find forms that need migration
			$forms_to_migrate = $this->find_forms_to_migrate();
			
			// Cache the result
			update_option( self::FORMS_TO_MIGRATE_OPTION, $forms_to_migrate );

			return ! empty( $forms_to_migrate ) ? $forms_to_migrate : false;
		}

		/**
		 * Find forms in old posts/pages that need migration
		 *
		 * @return array Array of forms with their parent post IDs
		 */
		private function find_forms_to_migrate() {
			$forms_to_migrate = array();

			// Get all posts/pages that contain gutena/forms block
			$posts_with_forms = get_posts( array(
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => -1,
				'post_status'    => 'any',
			) );

			foreach ( $posts_with_forms as $post ) {
				if ( ! has_block( 'gutena/forms', $post ) ) {
					continue;
				}

				$blocks = parse_blocks( $post->post_content );
				$form_blocks = $this->extract_form_blocks( $blocks );

				foreach ( $form_blocks as $form_block ) {
					if ( empty( $form_block['attrs']['formID'] ) ) {
						continue;
					}

					$form_id = sanitize_key( $form_block['attrs']['formID'] );

					// Check if this form already exists in CPT
					$existing_form = get_posts( array(
						'post_type'      => 'gutena_forms',
						'posts_per_page' => 1,
						'meta_key'       => 'gutena_form_id',
						'meta_value'     => $form_id,
						'post_status'    => 'any',
						'fields'         => 'ids',
					) );

					// If form doesn't exist in CPT, add to migration list
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

			// Remove duplicates based on form_id
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
		 * Extract form blocks from nested blocks
		 *
		 * @param array $blocks Parsed blocks
		 * @return array Array of form blocks
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

		/**
		 * Show admin notice for migration
		 */
		public function show_migration_notice() {
			// Only show in admin area
			if ( ! is_admin() ) {
				return;
			}

			// Check if user has permission
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$forms_to_migrate = $this->needs_migration();
			if ( false === $forms_to_migrate ) {
				return;
			}

			$forms_count = count( $forms_to_migrate );
			$migration_status = get_option( self::MIGRATION_OPTION, array() );
			$is_migrating = ! empty( $migration_status['in_progress'] );

			?>
			<div class="notice notice-info gutena-forms-migration-notice" style="position: relative;">
				<p>
					<strong><?php esc_html_e( 'Gutena Forms Migration', 'gutena-forms' ); ?></strong>
				</p>
				<p>
					<?php
					if ( $is_migrating ) {
						$migrated = isset( $migration_status['migrated_forms'] ) ? intval( $migration_status['migrated_forms'] ) : 0;
						$total = isset( $migration_status['total_forms'] ) ? intval( $migration_status['total_forms'] ) : $forms_count;
						printf(
							esc_html__( 'Migration is in progress. %d of %d form(s) migrated. Please wait while we continue migrating your forms to the new system.', 'gutena-forms' ),
							$migrated,
							$total
						);
					} else {
						printf(
							esc_html__( 'We found %d form(s) from your previous version that need to be migrated to the new form management system. Click the button below to start the migration.', 'gutena-forms' ),
							$forms_count
						);
					}
					?>
				</p>
				<?php if ( ! $is_migrating ) : ?>
					<p>
						<button type="button" class="button button-primary gutena-forms-start-migration" data-nonce="<?php echo esc_attr( wp_create_nonce( 'gutena_forms_migration' ) ); ?>">
							<?php esc_html_e( 'Start Migration', 'gutena-forms' ); ?>
						</button>
						<button type="button" class="button gutena-forms-dismiss-migration" data-nonce="<?php echo esc_attr( wp_create_nonce( 'gutena_forms_dismiss_migration' ) ); ?>">
							<?php esc_html_e( 'Dismiss', 'gutena-forms' ); ?>
						</button>
					</p>
				<?php else : ?>
					<div class="gutena-forms-migration-progress" style="margin: 10px 0;">
						<div style="background: #f0f0f0; border-radius: 4px; height: 20px; overflow: hidden;">
							<div class="gutena-forms-progress-bar" style="background: #2271b1; height: 100%; width: 0%; transition: width 0.3s;"></div>
						</div>
						<p class="gutena-forms-progress-text" style="margin: 5px 0 0 0; font-size: 12px;">
							<?php esc_html_e( 'Processing...', 'gutena-forms' ); ?>
						</p>
					</div>
				<?php endif; ?>
			</div>
			<?php
		}

		/**
		 * Enqueue migration scripts
		 */
		public function enqueue_migration_scripts( $hook ) {
			// Only load on admin pages
			if ( ! is_admin() ) {
				return;
			}

			// Check if migration notice should be shown
			$forms_to_migrate = $this->needs_migration();
			$migration_status = get_option( self::MIGRATION_OPTION, array() );
			$is_migrating = ! empty( $migration_status['in_progress'] );
			
			if ( false === $forms_to_migrate && ! $is_migrating ) {
				return;
			}

			// Get the forms list page URL for redirect after migration
			$forms_list_url = admin_url( 'edit.php?post_type=gutena_forms' );

			?>
			<script type="text/javascript">
			(function() {
				'use strict';
				
				// Wait for DOM to be ready
				function domReady(fn) {
					if (document.readyState === 'loading') {
						document.addEventListener('DOMContentLoaded', fn);
					} else {
						fn();
					}
				}

				// Polling function to check migration status
				function checkMigrationStatus(nonce, notice) {
					var xhr = new XMLHttpRequest();
					xhr.open('POST', ajaxurl, true);
					xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
					xhr.onload = function() {
						if (xhr.status === 200) {
							try {
								var response = JSON.parse(xhr.responseText);
								if (response.success && response.data) {
									var data = response.data;
									var progressBar = document.querySelector('.gutena-forms-progress-bar');
									var progressText = document.querySelector('.gutena-forms-progress-text');
									
									// Update progress bar
									if (progressBar && data.total > 0) {
										var percentage = Math.round((data.migrated / data.total) * 100);
										progressBar.style.width = percentage + '%';
									}
									
									// Update progress text
									if (progressText) {
										progressText.textContent = data.migrated + ' / ' + data.total + ' <?php esc_html_e( 'forms migrated', 'gutena-forms' ); ?>';
									}
									
									// Update notice text
									var noticeParagraphs = notice.querySelectorAll('p');
									if (noticeParagraphs.length > 1 && data.in_progress) {
										var message = '<?php esc_html_e( 'Migration is in progress. %d of %d form(s) migrated. Please wait...', 'gutena-forms' ); ?>';
										noticeParagraphs[1].innerHTML = message.replace('%d', data.migrated).replace('%d', data.total);
									}
									
									// Check if migration is completed
									if (data.completed || (!data.in_progress && data.migrated >= data.total && data.total > 0)) {
										// Migration completed
										if (noticeParagraphs.length > 0) {
											noticeParagraphs[0].innerHTML = '<strong><?php esc_html_e( 'Migration Completed!', 'gutena-forms' ); ?></strong>';
										}
										if (noticeParagraphs.length > 1) {
											noticeParagraphs[1].innerHTML = '<?php esc_html_e( 'All forms have been successfully migrated. Redirecting...', 'gutena-forms' ); ?>';
										}
										if (progressBar) {
											progressBar.style.width = '100%';
										}
										if (progressText) {
											progressText.textContent = data.total + ' / ' + data.total + ' <?php esc_html_e( 'forms migrated', 'gutena-forms' ); ?>';
										}
										
										// Redirect to forms list page after 2 seconds
										setTimeout(function() {
											window.location.href = '<?php echo esc_js( $forms_list_url ); ?>';
										}, 2000);
										return; // Stop polling
									}
									
									// Continue polling if migration is still in progress
									if (data.in_progress) {
										setTimeout(function() {
											checkMigrationStatus(nonce, notice);
										}, 2000); // Check every 2 seconds
									}
								}
							} catch (e) {
								console.error('Error parsing migration status:', e);
								// Retry after error
								setTimeout(function() {
									checkMigrationStatus(nonce, notice);
								}, 3000);
							}
						} else {
							// Retry on HTTP error
							setTimeout(function() {
								checkMigrationStatus(nonce, notice);
							}, 3000);
						}
					};
					xhr.onerror = function() {
						// Retry on network error
						setTimeout(function() {
							checkMigrationStatus(nonce, notice);
						}, 3000);
					};
					xhr.send('action=gutena_forms_check_migration_status&nonce=' + encodeURIComponent(nonce));
				}

				domReady(function() {
					var migrationNonce = '<?php echo esc_js( wp_create_nonce( 'gutena_forms_migration' ) ); ?>';
					var notice = document.querySelector('.gutena-forms-migration-notice');
					
					// Start migration
					var startButton = document.querySelector('.gutena-forms-start-migration');
					if (startButton) {
						startButton.addEventListener('click', function(e) {
							e.preventDefault();
							var button = this;
							var notice = button.closest('.gutena-forms-migration-notice');
							var nonce = button.getAttribute('data-nonce');

							button.disabled = true;
							button.textContent = '<?php esc_html_e( 'Starting...', 'gutena-forms' ); ?>';

							var xhr = new XMLHttpRequest();
							xhr.open('POST', ajaxurl, true);
							xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
							xhr.onload = function() {
								if (xhr.status === 200) {
									try {
										var response = JSON.parse(xhr.responseText);
										if (response.success) {
											// Update notice UI
											var noticeParagraphs = notice.querySelectorAll('p');
											if (noticeParagraphs.length > 0) {
												noticeParagraphs[0].innerHTML = '<strong><?php esc_html_e( 'Migration Started', 'gutena-forms' ); ?></strong>';
											}
											if (noticeParagraphs.length > 1) {
												noticeParagraphs[1].innerHTML = '<?php esc_html_e( 'Migration is now running in the background. Please wait...', 'gutena-forms' ); ?>';
											}
											
											// Remove buttons
											var buttonParent = button.closest('p');
											if (buttonParent) {
												buttonParent.remove();
											}
											
											// Show progress bar if not already visible
											var progressContainer = notice.querySelector('.gutena-forms-migration-progress');
											if (!progressContainer) {
												var progressHtml = '<div class="gutena-forms-migration-progress" style="margin: 10px 0;">' +
													'<div style="background: #f0f0f0; border-radius: 4px; height: 20px; overflow: hidden;">' +
													'<div class="gutena-forms-progress-bar" style="background: #2271b1; height: 100%; width: 0%; transition: width 0.3s;"></div>' +
													'</div>' +
													'<p class="gutena-forms-progress-text" style="margin: 5px 0 0 0; font-size: 12px;"><?php esc_html_e( 'Processing...', 'gutena-forms' ); ?></p>' +
													'</div>';
												notice.insertAdjacentHTML('beforeend', progressHtml);
											}
											
											// Start polling for status updates
											setTimeout(function() {
												checkMigrationStatus(nonce, notice);
											}, 1000);
										} else {
											alert(response.data && response.data.message ? response.data.message : '<?php esc_html_e( 'Migration failed to start.', 'gutena-forms' ); ?>');
											button.disabled = false;
											button.textContent = '<?php esc_html_e( 'Start Migration', 'gutena-forms' ); ?>';
										}
									} catch (e) {
										alert('<?php esc_html_e( 'An error occurred. Please try again.', 'gutena-forms' ); ?>');
										button.disabled = false;
										button.textContent = '<?php esc_html_e( 'Start Migration', 'gutena-forms' ); ?>';
									}
								} else {
									alert('<?php esc_html_e( 'An error occurred. Please try again.', 'gutena-forms' ); ?>');
									button.disabled = false;
									button.textContent = '<?php esc_html_e( 'Start Migration', 'gutena-forms' ); ?>';
								}
							};
							xhr.onerror = function() {
								alert('<?php esc_html_e( 'An error occurred. Please try again.', 'gutena-forms' ); ?>');
								button.disabled = false;
								button.textContent = '<?php esc_html_e( 'Start Migration', 'gutena-forms' ); ?>';
							};
							xhr.send('action=gutena_forms_start_migration&nonce=' + encodeURIComponent(nonce));
						});
					}

					// Dismiss migration
					var dismissButton = document.querySelector('.gutena-forms-dismiss-migration');
					if (dismissButton) {
						dismissButton.addEventListener('click', function(e) {
							e.preventDefault();
							var button = this;
							var notice = button.closest('.gutena-forms-migration-notice');
							var nonce = button.getAttribute('data-nonce');

							var xhr = new XMLHttpRequest();
							xhr.open('POST', ajaxurl, true);
							xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
							xhr.onload = function() {
								if (xhr.status === 200) {
									try {
										var response = JSON.parse(xhr.responseText);
										if (response.success && notice) {
											notice.style.opacity = '0';
											notice.style.transition = 'opacity 0.3s';
											setTimeout(function() {
												notice.style.display = 'none';
											}, 300);
										}
									} catch (e) {
										// Silently fail
									}
								}
							};
							xhr.send('action=gutena_forms_dismiss_migration&nonce=' + encodeURIComponent(nonce));
						});
					}

					// If migration is already in progress, start polling
					<?php if ( $is_migrating ) : ?>
					if (notice) {
						// Initial progress update
						var progressBar = document.querySelector('.gutena-forms-progress-bar');
						var progressText = document.querySelector('.gutena-forms-progress-text');
						var migrated = <?php echo isset( $migration_status['migrated_forms'] ) ? intval( $migration_status['migrated_forms'] ) : 0; ?>;
						var total = <?php echo isset( $migration_status['total_forms'] ) ? intval( $migration_status['total_forms'] ) : 1; ?>;
						var percentage = total > 0 ? Math.round((migrated / total) * 100) : 0;
						
						if (progressBar) {
							progressBar.style.width = percentage + '%';
						}
						if (progressText) {
							progressText.textContent = migrated + ' / ' + total + ' <?php esc_html_e( 'forms migrated', 'gutena-forms' ); ?>';
						}
						
						// Start polling
						setTimeout(function() {
							checkMigrationStatus(migrationNonce, notice);
						}, 1000);
					}
					<?php endif; ?>
				});
			})();
			</script>
			<?php
		}

		/**
		 * AJAX handler to start migration
		 */
		public function ajax_start_migration() {
			check_ajax_referer( 'gutena_forms_migration', 'nonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => __( 'Permission denied.', 'gutena-forms' ) ) );
			}

			$forms_to_migrate = $this->needs_migration();
			if ( false === $forms_to_migrate || empty( $forms_to_migrate ) ) {
				wp_send_json_error( array( 'message' => __( 'No forms to migrate.', 'gutena-forms' ) ) );
			}

			// Mark migration as in progress
			$migration_status = get_option( self::MIGRATION_OPTION, array() );
			$migration_status['in_progress'] = true;
			$migration_status['started_at'] = current_time( 'mysql' );
			$migration_status['total_forms'] = count( $forms_to_migrate );
			$migration_status['migrated_forms'] = 0;
			update_option( self::MIGRATION_OPTION, $migration_status );

			// Store forms to migrate
			update_option( self::FORMS_TO_MIGRATE_OPTION, $forms_to_migrate );

			// Schedule first batch
			$this->schedule_migration_batch();

			wp_send_json_success( array( 'message' => __( 'Migration started successfully.', 'gutena-forms' ) ) );
		}

		/**
		 * AJAX handler to dismiss migration notice
		 */
		public function ajax_dismiss_migration() {
			check_ajax_referer( 'gutena_forms_dismiss_migration', 'nonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error();
			}

			$migration_status = get_option( self::MIGRATION_OPTION, array() );
			$migration_status['dismissed'] = true;
			update_option( self::MIGRATION_OPTION, $migration_status );

			wp_send_json_success();
		}

		/**
		 * AJAX handler to check migration status
		 */
		public function ajax_check_migration_status() {
			check_ajax_referer( 'gutena_forms_migration', 'nonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => __( 'Permission denied.', 'gutena-forms' ) ) );
			}

			$migration_status = get_option( self::MIGRATION_OPTION, array() );
			$is_migrating = ! empty( $migration_status['in_progress'] );
			$is_completed = ! empty( $migration_status['completed'] );
			
			$response = array(
				'in_progress' => $is_migrating,
				'completed'   => $is_completed,
				'migrated'    => isset( $migration_status['migrated_forms'] ) ? intval( $migration_status['migrated_forms'] ) : 0,
				'total'       => isset( $migration_status['total_forms'] ) ? intval( $migration_status['total_forms'] ) : 0,
			);

			wp_send_json_success( $response );
		}

		/**
		 * Schedule migration batch
		 */
		private function schedule_migration_batch() {
			// Clear any existing scheduled event
			$timestamp = wp_next_scheduled( 'gutena_forms_migrate_forms' );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, 'gutena_forms_migrate_forms' );
			}

			// Schedule immediate execution (runs on next page load or cron)
			wp_schedule_single_event( time(), 'gutena_forms_migrate_forms' );
		}

		/**
		 * Process migration batch
		 */
		public function process_migration_batch() {
			$migration_status = get_option( self::MIGRATION_OPTION, array() );
			
			// Check if migration is in progress
			if ( empty( $migration_status['in_progress'] ) ) {
				return;
			}

			$forms_to_migrate = get_option( self::FORMS_TO_MIGRATE_OPTION, array() );
			if ( empty( $forms_to_migrate ) ) {
				// Migration complete
				$migration_status['in_progress'] = false;
				$migration_status['completed'] = true;
				$migration_status['completed_at'] = current_time( 'mysql' );
				update_option( self::MIGRATION_OPTION, $migration_status );
				delete_option( self::FORMS_TO_MIGRATE_OPTION );
				return;
			}

			// Process 5 forms per batch
			$batch_size = 5;
			$batch = array_slice( $forms_to_migrate, 0, $batch_size );
			$remaining = array_slice( $forms_to_migrate, $batch_size );

			foreach ( $batch as $form_data ) {
				$this->migrate_single_form( $form_data );
				$migration_status['migrated_forms']++;
			}

			// Update status
			update_option( self::MIGRATION_OPTION, $migration_status );

			if ( ! empty( $remaining ) ) {
				// Store remaining forms
				update_option( self::FORMS_TO_MIGRATE_OPTION, $remaining );
				
				// Schedule next batch
				wp_schedule_single_event( time() + 10, 'gutena_forms_migrate_forms' );
			} else {
				// Migration complete
				$migration_status['in_progress'] = false;
				$migration_status['completed'] = true;
				$migration_status['completed_at'] = current_time( 'mysql' );
				update_option( self::MIGRATION_OPTION, $migration_status );
				delete_option( self::FORMS_TO_MIGRATE_OPTION );
			}
		}

		/**
		 * Migrate a single form to CPT
		 *
		 * @param array $form_data Form data to migrate
		 * @return bool Success status
		 */
		private function migrate_single_form( $form_data ) {
			$form_id = $form_data['form_id'];
			$form_block = $form_data['form_block'];
			$parent_post_id = $form_data['parent_post_id'];
			$form_name = $form_data['form_name'];

			// Check if form already exists
			$existing_forms = get_posts( array(
				'post_type'      => 'gutena_forms',
				'posts_per_page' => 1,
				'meta_key'       => 'gutena_form_id',
				'meta_value'     => $form_id,
				'post_status'    => 'any',
				'fields'         => 'ids',
			) );

			if ( ! empty( $existing_forms ) ) {
				// Form already exists, just update connected posts
				$cpt_post_id = $existing_forms[0];
			} else {
				// Create new CPT entry
				$cpt_post_id = wp_insert_post( array(
					'post_type'    => 'gutena_forms',
					'post_title'   => $form_name,
					'post_status'  => 'publish',
					'post_content' => serialize_block( $form_block ),
				), true );

				if ( is_wp_error( $cpt_post_id ) ) {
					return false;
				}

				// Save form ID as meta
				update_post_meta( $cpt_post_id, 'gutena_form_id', $form_id );
			}

			// Update connected posts
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
	}

	Gutena_Forms_Migration::get_instance();
endif;
