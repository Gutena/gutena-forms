<?php
/**
 * Template registry and category definitions.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Templates_Registry' ) ) :
	/**
	 * Provides access to form template definitions.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_Templates_Registry {

		/**
		 * Singleton instance.
		 *
		 * @since 2.2.0
		 * @var Gutena_Forms_Templates_Registry|null
		 */
		private static $instance = null;

		/**
		 * Cached templates.
		 *
		 * @since 2.2.0
		 * @var array|null
		 */
		private $templates = null;

		/**
		 * Get singleton instance.
		 *
		 * @since 2.2.0
		 * @return Gutena_Forms_Templates_Registry
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Get template categories.
		 *
		 * @since 2.2.0
		 * @return array
		 */
		public function get_categories() {
			return array(
				array(
					'slug'  => 'all',
					'title' => __( 'All Form Templates', 'gutena-forms' ),
				),
				array(
					'slug'  => 'application-forms',
					'title' => __( 'Application Forms', 'gutena-forms' ),
				),
				array(
					'slug'  => 'booking-forms',
					'title' => __( 'Booking Forms', 'gutena-forms' ),
				),
				array(
					'slug'  => 'event-planning',
					'title' => __( 'Event Planning', 'gutena-forms' ),
				),
				array(
					'slug'  => 'lead-generation',
					'title' => __( 'Lead Generation', 'gutena-forms' ),
				),
				array(
					'slug'  => 'marketing',
					'title' => __( 'Marketing', 'gutena-forms' ),
				),
				array(
					'slug'  => 'supports-requests',
					'title' => __( 'Supports & Requests', 'gutena-forms' ),
				),
				array(
					'slug'  => 'surveys-feedback',
					'title' => __( 'Surveys & Feedback', 'gutena-forms' ),
				),
			);
		}

		/**
		 * Template counts keyed by category slug (includes "all").
		 *
		 * @since 2.2.0
		 * @return array<string, int>
		 */
		public function get_category_counts() {
			$counts = array(
				'all' => 0,
			);

			foreach ( $this->get_categories() as $category ) {
				if ( 'all' === $category['slug'] ) {
					continue;
				}
				$counts[ $category['slug'] ] = 0;
			}

			foreach ( $this->get_all() as $template ) {
				++$counts['all'];
				$slug = $template['category'];
				if ( isset( $counts[ $slug ] ) ) {
					++$counts[ $slug ];
				}
			}

			return $counts;
		}

		/**
		 * Sidebar navigation items for the template library (filters + external link).
		 *
		 * @since 2.2.0
		 * @return array
		 */
		public function get_sidebar_items() {
			$counts = $this->get_category_counts();
			$items  = array();

			foreach ( $this->get_categories() as $category ) {
				$slug = $category['slug'];
				$items[] = array(
					'slug'  => $slug,
					'title' => $category['title'],
					'type'  => 'filter',
					'count' => isset( $counts[ $slug ] ) ? (int) $counts[ $slug ] : 0,
				);
			}

			$items[] = array(
				'slug'            => 'request-template',
				'title'           => __( 'Request Template', 'gutena-forms' ),
				'type'            => 'link',
				'url'             => 'https://gutenaforms.com/roadmap/',
				'open_in_new_tab' => true,
			);

			return $items;
		}

		/**
		 * Load and normalize all templates.
		 *
		 * @since 2.2.0
		 * @return array
		 */
		public function get_all() {
			if ( null !== $this->templates ) {
				return $this->templates;
			}

			$this->templates = array();

			foreach ( Gutena_Forms_Templates_Loader::get_instances() as $template ) {
				$this->templates[] = $this->normalize_template( $template->to_array() );
			}

			return $this->templates;
		}

		/**
		 * Get a single template by ID.
		 *
		 * @since 2.2.0
		 * @param string $template_id Template ID.
		 * @return array|null
		 */
		public function get_by_id( $template_id ) {
			foreach ( $this->get_all() as $template ) {
				if ( $template['id'] === $template_id ) {
					return $template;
				}
			}

			return null;
		}

		/**
		 * Filter templates by category and search query.
		 *
		 * @since 2.2.0
		 * @param string $category Category slug or 'all'.
		 * @param string $search Search query.
		 * @return array
		 */
		public function filter( $category = 'all', $search = '' ) {
			$templates = $this->get_all();

			if ( ! empty( $category ) && 'all' !== $category ) {
				$templates = array_values(
					array_filter(
						$templates,
						function ( $template ) use ( $category ) {
							return $template['category'] === $category;
						}
					)
				);
			}

			if ( ! empty( $search ) ) {
				$search = strtolower( $search );
				$templates = array_values(
					array_filter(
						$templates,
						function ( $template ) use ( $search ) {
							$haystack = strtolower( $template['title'] . ' ' . $template['description'] );
							return false !== strpos( $haystack, $search );
						}
					)
				);
			}

			return $templates;
		}

		/**
		 * Get public template payload for API responses.
		 *
		 * @since 2.2.0
		 * @param array $template Raw template.
		 * @param bool  $include_blocks Whether to include inner blocks.
		 * @return array
		 */
		public function to_public( $template, $include_blocks = false ) {
			$is_free = ! empty( $template['is_free'] );

			$public = array(
				'id'             => $template['id'],
				'title'          => $template['title'],
				'description'    => $template['description'],
				'category'       => $template['category'],
				'category_label' => $this->get_category_label( $template['category'] ),
				'is_free'        => $is_free,
				'is_pro'         => ! $is_free,
				'preview'        => $template['preview'],
			);

			if ( $include_blocks ) {
				$public['form_attrs']  = $template['form_attrs'];
				$public['innerBlocks'] = $template['innerBlocks'];
			}

			return $public;
		}

		/**
		 * Get category label by slug.
		 *
		 * @since 2.2.0
		 * @param string $slug Category slug.
		 * @return string
		 */
		public function get_category_label( $slug ) {
			foreach ( $this->get_categories() as $category ) {
				if ( $category['slug'] === $slug ) {
					return $category['title'];
				}
			}

			return '';
		}

		/**
		 * Normalize template data and preview image URLs.
		 *
		 * @since 2.2.0
		 * @param array $template Template definition.
		 * @return array
		 */
		private function normalize_template( $template ) {
			$base_dir     = GUTENA_FORMS_DIR_PATH . 'assets/img/templates/';
			$default_file = 'default.png';
			$image_file   = $default_file;

			if ( ! empty( $template['preview']['image'] ) ) {
				$candidate = sanitize_file_name( wp_basename( $template['preview']['image'] ) );
				if ( $candidate && file_exists( $base_dir . $candidate ) ) {
					$image_file = $candidate;
				}
			}

			if ( ! file_exists( $base_dir . $image_file ) && file_exists( $base_dir . 'default.png' ) ) {
				$image_file = 'default.png';
			}

			$template['preview']['image'] = GUTENA_FORMS_PLUGIN_URL . 'assets/img/templates/' . $image_file;

			return $template;
		}
	}
endif;
