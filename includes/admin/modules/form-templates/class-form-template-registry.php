<?php
/**
 * Registry for Gutena Forms template library classes.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Form_Template_Registry' ) ) :
	/**
	 * Discovers template classes and resolves the template catalog.
	 *
	 * @since 2.2.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Form_Template_Registry {
		/**
		 * Singleton instance.
		 *
		 * @since 2.2.0
		 * @var Gutena_Forms_Form_Template_Registry|null
		 */
		private static $instance = null;

		/**
		 * Resolved template catalog cache.
		 *
		 * @since 2.2.0
		 * @var array|null
		 */
		private $templates_cache = null;

		/**
		 * Get singleton instance.
		 *
		 * @since 2.2.0
		 * @return Gutena_Forms_Form_Template_Registry
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @since 2.2.0
		 */
		private function __construct() {}

		/**
		 * Get registered template class names.
		 *
		 * @since 2.2.0
		 * @return string[] Fully-qualified class names.
		 */
		public function get_template_classes() {
			$template_classes = $this->get_default_template_classes();

			/**
			 * Filter the template class collection before the catalog is resolved.
			 *
			 * Pro and third-party extensions can append their own template classes.
			 *
			 * @since 2.2.0
			 * @param string[] $template_classes Template class names.
			 */
			$template_classes = apply_filters( 'gutena_forms__template_library', $template_classes );

			if ( ! is_array( $template_classes ) ) {
				return $this->get_default_template_classes();
			}

			return array_values( array_unique( array_filter( $template_classes ) ) );
		}

		/**
		 * Get all resolved templates keyed by template ID.
		 *
		 * @since 2.2.0
		 * @return array<string, array>
		 */
		public function get_all() {
			if ( null !== $this->templates_cache ) {
				return $this->templates_cache;
			}

			$templates = array();

			foreach ( $this->get_template_classes() as $template_class ) {
				$template = $this->resolve_template_class( $template_class );

				if ( null === $template ) {
					continue;
				}

				$template_id = $template['id'];

				if ( isset( $templates[ $template_id ] ) ) {
					continue;
				}

				$templates[ $template_id ] = $template;
			}

			$this->templates_cache = $templates;

			return $this->templates_cache;
		}

		/**
		 * Get a single template by ID.
		 *
		 * @since 2.2.0
		 * @param string $template_id Template identifier.
		 * @return array|null
		 */
		public function get_by_id( $template_id ) {
			$template_id = sanitize_key( $template_id );

			if ( '' === $template_id ) {
				return null;
			}

			$templates = $this->get_all();

			return isset( $templates[ $template_id ] ) ? $templates[ $template_id ] : null;
		}

		/**
		 * Get all supported category definitions with translated labels.
		 *
		 * @since 2.2.0
		 * @return array<string, string> Category slug => translated label.
		 */
		public function get_categories() {
			return self::get_category_labels();
		}

		/**
		 * Get template counts grouped by category slug.
		 *
		 * Counts are calculated dynamically from registered templates.
		 *
		 * @since 2.2.0
		 * @return array<string, int> Category slug => template count.
		 */
		public function get_category_counts() {
			$counts = array();

			foreach ( array_keys( self::get_category_labels() ) as $category_id ) {
				$counts[ $category_id ] = 0;
			}

			foreach ( $this->get_all() as $template ) {
				if ( empty( $template['category'] ) ) {
					continue;
				}

				$category_id = sanitize_key( $template['category'] );

				if ( ! isset( $counts[ $category_id ] ) ) {
					$counts[ $category_id ] = 0;
				}

				++$counts[ $category_id ];
			}

			return $counts;
		}

		/**
		 * Default Free template classes.
		 *
		 * @since 2.2.0
		 * @return string[]
		 */
		private function get_default_template_classes() {
			return array(
				Gutena_Forms_Template_Volunteer_Application::class,
				Gutena_Forms_Template_Service_Booking_Request::class,
				Gutena_Forms_Template_Event_RSVP::class,
				Gutena_Forms_Template_Online_Event_Registration::class,
				Gutena_Forms_Template_Lead_Capture::class,
				Gutena_Forms_Template_Request_A_Quote::class,
				Gutena_Forms_Template_Newsletter_Signup::class,
				Gutena_Forms_Template_Affiliate_Signup::class,
				Gutena_Forms_Template_Demo_Request::class,
				Gutena_Forms_Template_Maintenance_Request::class,
				Gutena_Forms_Template_Beta_Access_Request::class,
				Gutena_Forms_Template_Customer_Satisfaction_Survey::class,
				Gutena_Forms_Template_Product_Feedback::class,
			);
		}

		/**
		 * Resolve a template class into a validated template array.
		 *
		 * @since 2.2.0
		 * @param string $template_class Template class name.
		 * @return array|null
		 */
		private function resolve_template_class( $template_class ) {
			if ( ! is_string( $template_class ) || ! class_exists( $template_class ) ) {
				return null;
			}

			if ( ! is_subclass_of( $template_class, 'Gutena_Forms_Abstract_Form_Template' ) ) {
				return null;
			}

			if ( ! is_callable( array( $template_class, 'get_template' ) ) ) {
				return null;
			}

			$template = call_user_func( array( $template_class, 'get_template' ) );

			if ( ! is_array( $template ) ) {
				return null;
			}

			return $this->validate_template( $template );
		}

		/**
		 * Validate and normalize a template definition.
		 *
		 * @since 2.2.0
		 * @param array $template Raw template definition.
		 * @return array|null
		 */
		private function validate_template( $template ) {
			$required_keys = array(
				'id',
				'title',
				'description',
				'category',
				'is_pro',
				'fields',
				'submit_label',
				'success_message',
			);

			foreach ( $required_keys as $required_key ) {
				if ( ! array_key_exists( $required_key, $template ) ) {
					return null;
				}
			}

			if ( ! is_string( $template['id'] ) || '' === sanitize_key( $template['id'] ) ) {
				return null;
			}

			if ( ! is_array( $template['fields'] ) ) {
				return null;
			}

			$template['id']       = sanitize_key( $template['id'] );
			$template['category'] = sanitize_key( $template['category'] );
			$template['is_pro']   = (bool) $template['is_pro'];

			if ( ! isset( $template['preview_image'] ) ) {
				$template['preview_image'] = '';
			}

			foreach ( $template['fields'] as $field ) {
				if ( empty( $field['block'] ) || empty( $field['attributes'] ) || ! is_array( $field['attributes'] ) ) {
					return null;
				}
			}

			return $template;
		}

		/**
		 * Supported category labels keyed by machine-readable slug.
		 *
		 * @since 2.2.0
		 * @return array<string, string>
		 */
		public static function get_category_labels() {
			return array(
				'application-forms'  => __( 'Application Forms', 'gutena-forms' ),
				'booking-forms'      => __( 'Booking Forms', 'gutena-forms' ),
				'event-planning'     => __( 'Event Planning', 'gutena-forms' ),
				'lead-generation'    => __( 'Lead Generation', 'gutena-forms' ),
				'marketing'          => __( 'Marketing', 'gutena-forms' ),
				'payment-forms'      => __( 'Payment Forms', 'gutena-forms' ),
				'quizzes'            => __( 'Quizzes', 'gutena-forms' ),
				'registration-forms' => __( 'Registration Forms', 'gutena-forms' ),
				'support-requests'   => __( 'Support & Requests', 'gutena-forms' ),
				'surveys-feedback'   => __( 'Surveys & Feedback', 'gutena-forms' ),
			);
		}
	}
endif;
