<?php
/**
 * Cross-marketing SMTP (Post SMTP) admin screen.
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Smtp' ) ) :

	/**
	 * Gutena Forms SMTP marketing page.
	 */
	class Gutena_Forms_Smtp {
		/**
		 * Singleton instance.
		 *
		 * @var Gutena_Forms_Smtp|null
		 */
		private static $instance = null;

		/**
		 * Menu slug.
		 *
		 * @var string
		 */
		const PAGE_SLUG = 'gutena-forms-smtp';

		/**
		 * Get instance.
		 *
		 * @return Gutena_Forms_Smtp
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		private function __construct() {
			add_action( 'admin_menu', array( $this, 'register_submenu' ), 20 );
			add_action( 'admin_init', array( $this, 'maybe_redirect_to_postman' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
			add_action( 'wp_ajax_gutena_forms_post_smtp_request', array( $this, 'request_post_smtp_ajax' ) );
		}

		/**
		 * Whether Post SMTP plugin files are installed (active or not).
		 *
		 * @return bool
		 */
		public static function is_post_smtp_installed() {
			return file_exists( WP_PLUGIN_DIR . '/post-smtp/postman-smtp.php' );
		}

		/**
		 * Whether Post SMTP plugin is active.
		 *
		 * @return bool
		 */
		public static function is_post_smtp_active() {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			return is_plugin_active( 'post-smtp/postman-smtp.php' )
				|| defined( 'POST_SMTP_VER' )
				|| defined( 'POST_SMTP_BASE' );
		}

		/**
		 * Register SMTP submenu before Upgrade.
		 * Active Post SMTP → link to Post SMTP admin; otherwise marketing screen.
		 */
		public function register_submenu() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			global $submenu;
			if ( ! isset( $submenu['gutena-forms'] ) ) {
				return;
			}

			if ( self::is_post_smtp_active() ) {
				$menu_slug = 'admin.php?page=postman';
				add_submenu_page(
					'gutena-forms',
					__( 'SMTP', 'gutena-forms' ),
					__( 'SMTP', 'gutena-forms' ),
					'manage_options',
					$menu_slug
				);
			} else {
				$menu_slug = self::PAGE_SLUG;
				add_submenu_page(
					'gutena-forms',
					__( 'SMTP', 'gutena-forms' ),
					__( 'SMTP', 'gutena-forms' ),
					'manage_options',
					$menu_slug,
					array( $this, 'render_page' )
				);
			}

			$this->move_submenu_before_upgrade( $menu_slug );
		}

		/**
		 * If marketing slug is opened while Post SMTP is active, go to Post SMTP.
		 */
		public function maybe_redirect_to_postman() {
			if ( empty( $_GET['page'] ) || self::PAGE_SLUG !== sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return;
			}

			if ( ! self::is_post_smtp_active() ) {
				return;
			}

			wp_safe_redirect( admin_url( 'admin.php?page=postman' ) );
			exit;
		}

		/**
		 * Place SMTP item immediately before Upgrade.
		 *
		 * @param string $smtp_slug Registered submenu slug for SMTP.
		 */
		private function move_submenu_before_upgrade( $smtp_slug ) {
			global $submenu;

			if ( empty( $submenu['gutena-forms'] ) || ! is_array( $submenu['gutena-forms'] ) ) {
				return;
			}

			$smtp_item   = null;
			$upgrade_key = null;
			$rebuilt     = array();

			foreach ( $submenu['gutena-forms'] as $key => $item ) {
				if ( isset( $item[2] ) && $smtp_slug === $item[2] ) {
					$smtp_item = $item;
					continue;
				}
				if ( isset( $item[0] ) && 'Upgrade' === $item[0] ) {
					$upgrade_key = $key;
				}
			}

			if ( null === $smtp_item ) {
				return;
			}

			foreach ( $submenu['gutena-forms'] as $key => $item ) {
				if ( isset( $item[2] ) && $smtp_slug === $item[2] ) {
					continue;
				}
				if ( null !== $upgrade_key && (int) $key === (int) $upgrade_key ) {
					$rebuilt[] = $smtp_item;
				}
				$rebuilt[] = $item;
			}

			// Upgrade missing (e.g. Pro) — append SMTP at end.
			if ( null === $upgrade_key ) {
				$rebuilt[] = $smtp_item;
			}

			$submenu['gutena-forms'] = $rebuilt;
		}

		/**
		 * Enqueue page styles.
		 *
		 * @param string $hook Current admin page hook.
		 */
		public function enqueue_assets( $hook ) {
			if ( 'gutena-forms_page_' . self::PAGE_SLUG !== $hook ) {
				return;
			}

			$version = defined( 'GUTENA_FORMS_VERSION' ) ? GUTENA_FORMS_VERSION : '1.0.0';

			// Satoshi only on this SMTP marketing screen (not global admin).
			wp_enqueue_style(
				'gutena-forms-smtp-fonts',
				'https://fonts.cdnfonts.com/css/satoshi',
				array(),
				null
			);

			wp_enqueue_style(
				'gutena-forms-smtp',
				GUTENA_FORMS_PLUGIN_URL . 'vendor/smtp/smtp.css',
				array( 'gutena-forms-smtp-fonts' ),
				$version
			);

			wp_enqueue_script( 'updates' );

			wp_enqueue_script(
				'gutena-forms-smtp',
				GUTENA_FORMS_PLUGIN_URL . 'vendor/smtp/smtp.js',
				array( 'updates' ),
				$version,
				true
			);

			wp_localize_script(
				'gutena-forms-smtp',
				'gutenaFormsSmtp',
				array(
					'ajaxURL'     => admin_url( 'admin-ajax.php' ),
					'ajaxNonce'   => wp_create_nonce( 'gutena_forms_post_smtp_request' ),
					'postSMTPURL' => admin_url( 'admin.php?page=postman' ),
					'i18n'        => array(
						'installing'  => __( 'Installing...', 'gutena-forms' ),
						'activating'  => __( 'Activating...', 'gutena-forms' ),
						'activated'   => __( 'Activated!', 'gutena-forms' ),
						'error'       => __( 'Error!', 'gutena-forms' ),
					),
				)
			);
		}

		/**
		 * Track install/activate with Post SMTP connect API.
		 */
		public function request_post_smtp_ajax() {
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'gutena_forms_post_smtp_request' ) ) {
				wp_send_json_error( array( 'message' => 'Security check failed' ) );
			}

			if ( ! current_user_can( 'install_plugins' ) && ! current_user_can( 'activate_plugins' ) ) {
				wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
			}

			if ( ! isset( $_POST['status'] ) ) {
				wp_send_json_error( array( 'message' => 'No status provided' ) );
			}

			$site_url    = get_bloginfo( 'url' );
			$status      = sanitize_text_field( wp_unslash( $_POST['status'] ) );
			$plugin_slug = 'gutena-forms';
			$secret_key  = 'WP_*#KXs2)34KM@_-*^%?>"}0!@~\@4C2*0A^%(%MVBS';

			$response = wp_remote_post(
				"https://connect.postmansmtp.com/wp-json/update/v1/update?site_url={$site_url}&status={$status}&plugin_slug={$plugin_slug}",
				array(
					'method'  => 'POST',
					'headers' => array(
						'Content-Type' => 'application/json',
						'Secret-Key'   => $secret_key,
					),
				)
			);

			if ( is_wp_error( $response ) ) {
				wp_send_json_error(
					array(
						'message' => 'Failed to send request: ' . $response->get_error_message(),
					)
				);
			}

			wp_send_json_success(
				array(
					'message' => __( 'Request sent successfully', 'gutena-forms' ),
				)
			);
		}

		/**
		 * Render marketing page.
		 */
		public function render_page() {
			if ( self::is_post_smtp_active() ) {
				wp_safe_redirect( admin_url( 'admin.php?page=gutena-forms' ) );
				exit;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$img         = GUTENA_FORMS_PLUGIN_URL . 'assets/img/smtp/';
			$video_title = __( 'Post SMTP - Email Plugin for WordPress Sites | Reliable Email Delivery Made Easy', 'gutena-forms' );
			$is_installed = self::is_post_smtp_installed();
			$btn_action   = $is_installed ? 'activate-plugin_post-smtp' : 'install-plugin_post-smtp';
			$btn_label    = $is_installed
				? __( 'Activate Post SMTP Now!', 'gutena-forms' )
				: __( 'Install and Activate Post SMTP Now!', 'gutena-forms' );
			?>
			<div class="wrap gutena-forms-smtp-wrap">
				<div class="gutena-forms-smtp">
					<div class="gutena-forms-smtp__hero">
						<div class="gutena-forms-smtp__logos">
							<img
								class="gutena-forms-smtp__logos-img"
								src="<?php echo esc_url( $img . 'smtp-gutena-logo.png' ); ?>"
								alt="<?php esc_attr_e( 'Gutena Forms + Post SMTP', 'gutena-forms' ); ?>"
							/>
						</div>

						<div class="gutena-forms-smtp__intro">
							<h1 class="gutena-forms-smtp__title">
								<?php esc_html_e( 'Never Miss a Form Submission Alert Again', 'gutena-forms' ); ?>
							</h1>
							<p class="gutena-forms-smtp__subtitle">
								<?php esc_html_e( 'Post SMTP helps your form notifications reach your inbox reliably.', 'gutena-forms' ); ?>
							</p>
						</div>

						<button type="button" class="gutena-forms-smtp__btn" data-action="<?php echo esc_attr( $btn_action ); ?>">
							<?php echo esc_html( $btn_label ); ?>
						</button>
					</div>

					<div
						class="gutena-forms-smtp__video"
						data-youtube-id="3DprhaEzCag"
					>
						<button
							type="button"
							class="gutena-forms-smtp__video-trigger"
							data-video-title="<?php echo esc_attr( $video_title ); ?>"
							aria-label="<?php esc_attr_e( 'Play video', 'gutena-forms' ); ?>"
						>
							<img
								src="<?php echo esc_url( $img . 'smtp-video-thumbnail.png' ); ?>"
								alt="<?php esc_attr_e( 'Setup PostSMTP for Better Email Deliverability', 'gutena-forms' ); ?>"
							/>
						</button>
					</div>

					<div class="gutena-forms-smtp__features">
						<h2 class="gutena-forms-smtp__features-title">
							<?php esc_html_e( 'Improve WordPress Email Deliverability with Post SMTP', 'gutena-forms' ); ?>
						</h2>

						<div class="gutena-forms-smtp__grid">
							<div class="gutena-forms-smtp__feature">
								<img class="gutena-forms-smtp__feature-icon" src="<?php echo esc_url( $img . 'email-box-icon.png' ); ?>" alt="" width="40" height="40" />
								<div class="gutena-forms-smtp__feature-copy">
									<h3><?php esc_html_e( '20+ Email Providers Support', 'gutena-forms' ); ?></h3>
									<p><?php esc_html_e( 'Connect Gmail, Microsoft 365, Brevo, Amazon SES, Mailgun, SendGrid, Zoho Mail, and much more.', 'gutena-forms' ); ?></p>
								</div>
							</div>
							<div class="gutena-forms-smtp__feature">
								<img class="gutena-forms-smtp__feature-icon" src="<?php echo esc_url( $img . 'fail-alert-icon.png' ); ?>" alt="" width="40" height="40" />
								<div class="gutena-forms-smtp__feature-copy">
									<h3><?php esc_html_e( 'Instant Failure Alerts', 'gutena-forms' ); ?></h3>
									<p><?php esc_html_e( 'Receive immediate alerts (via email, SMS, Microsoft Teams, Webhook) whenever a form notification or any transactional email fails to send.', 'gutena-forms' ); ?></p>
								</div>
							</div>
							<div class="gutena-forms-smtp__feature">
								<img class="gutena-forms-smtp__feature-icon" src="<?php echo esc_url( $img . 'email-logs-icon.png' ); ?>" alt="" width="40" height="40" />
								<div class="gutena-forms-smtp__feature-copy">
									<h3><?php esc_html_e( 'Detailed Email Logs', 'gutena-forms' ); ?></h3>
									<p><?php esc_html_e( 'See every form notification your website sends and quickly troubleshoot email delivery issues.', 'gutena-forms' ); ?></p>
								</div>
							</div>
							<div class="gutena-forms-smtp__feature">
								<img class="gutena-forms-smtp__feature-icon" src="<?php echo esc_url( $img . 'backup-smtp.png' ); ?>" alt="" width="40" height="40" />
								<div class="gutena-forms-smtp__feature-copy">
									<h3><?php esc_html_e( 'Backup SMTP', 'gutena-forms' ); ?></h3>
									<p><?php esc_html_e( 'Automatically send through a secondary mailer if your primary email service is unavailable.', 'gutena-forms' ); ?></p>
								</div>
							</div>
						</div>
					</div>

					<div class="gutena-forms-smtp__footer">
						<p class="gutena-forms-smtp__footer-text">
							<?php esc_html_e( "Don't let valuable leads disappear because of email delivery problems.", 'gutena-forms' ); ?>
						</p>
						<button type="button" class="gutena-forms-smtp__btn" data-action="<?php echo esc_attr( $btn_action ); ?>">
							<?php echo esc_html( $btn_label ); ?>
						</button>
					</div>
				</div>
			</div>
			<?php
		}
	}

	Gutena_Forms_Smtp::get_instance();

endif;
