<?php
/**
 * REST API endpoints for AI form creation.
 *
 * Registers the `forms/create-from-ai` route under the
 * `gutena-forms/v1` namespace via the gutena_forms__rest_routs filter.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_AI_Endpoints' ) ) :
	/**
	 * Handles AI form creation REST routes.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_AI_Endpoints {

		/**
		 * Singleton instance.
		 *
		 * @since 2.2.0
		 * @var Gutena_Forms_AI_Endpoints|null
		 */
		private static $instance = null;

		/**
		 * Get singleton instance.
		 *
		 * @since 2.2.0
		 * @return Gutena_Forms_AI_Endpoints
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
		private function __construct() {
			add_filter( 'gutena_forms__rest_routs', array( $this, 'rest_routes' ), 10, 2 );
		}

		/**
		 * Register REST routes.
		 *
		 * @since 2.2.0
		 * @param array          $routes Existing routes.
		 * @param WP_REST_Server $server REST server.
		 * @return array
		 */
		public function rest_routes( $routes, $server ) {
			$routes[] = array(
				'route'    => 'forms/create-from-ai',
				'methods'  => $server::CREATABLE,
				'callback' => array( $this, 'create_from_ai' ),
				'auth'     => true,
			);

			return $routes;
		}

		/**
		 * Create a form from an AI-generated prompt.
		 *
		 * @since 2.2.0
		 * @param WP_REST_Request $request REST request. Expects 'prompt' (string, max 350 chars).
		 * @return WP_REST_Response|\WP_Error
		 */
		public function create_from_ai( $request ) {
			// 1. Sanitize and validate the prompt.
			$prompt = sanitize_textarea_field( wp_unslash( $request->get_param( 'prompt' ) ) );

			if ( '' === trim( $prompt ) ) {
				return new \WP_Error(
					'gutena_forms_ai_empty_prompt',
					__( 'Please describe the form you want to create.', 'gutena-forms' ),
					array( 'status' => 400 )
				);
			}

			if ( mb_strlen( $prompt ) > 350 ) {
				return new \WP_Error(
					'gutena_forms_ai_prompt_too_long',
					__( 'The prompt must be 350 characters or fewer.', 'gutena-forms' ),
					array( 'status' => 400 )
				);
			}

			// 2. Ensure AI is enabled.
			if ( ! class_exists( 'Gutena_Forms_AI_Settings' ) || ! Gutena_Forms_AI_Settings::is_enabled() ) {
				return new \WP_Error(
					'gutena_forms_ai_disabled',
					__( 'AI form creation is not enabled.', 'gutena-forms' ),
					array( 'status' => 403 )
				);
			}

			// 3. Ask the middle server for block markup (credit consumed on 200).
			$markup = Gutena_Forms_AI_Model::create_from_ai( $prompt, get_current_user_id() );

			if ( is_wp_error( $markup ) ) {
				return $markup;
			}

			// 4. Validate the returned block markup against the allowlist.
			$blocks = Gutena_Forms_AI_Block_Validator::validate( $markup );

			if ( is_wp_error( $blocks ) ) {
				return $blocks;
			}

			// 5. Degrade Pro-only field blocks when Pro is not active.
			$blocks = Gutena_Forms_AI_Block_Validator::degrade_pro_fields( $blocks );

			// 6. Generate a formID and inject it into the root gutena/forms block.
			$form_id = Gutena_Forms_Templates_Block_Builder::generate_form_id();
			$blocks  = Gutena_Forms_AI_Block_Validator::inject_form_id( $blocks, $form_id );

			// 7. Serialize the block tree back to markup.
			$serialized = '';
			foreach ( $blocks as $block ) {
				$serialized .= serialize_block( $block );
			}

			if ( '' === trim( $serialized ) ) {
				return new \WP_Error(
					'gutena_forms_ai_serialize_failed',
					__( 'The generated form could not be processed. Please try again.', 'gutena-forms' )
				);
			}

			// 8. Create the gutena_forms post as a draft.
			$title   = sanitize_text_field( (string) $request->get_param( 'title' ) );
			$title   = '' === $title ? __( 'AI Generated Form', 'gutena-forms' ) : $title;

			$post_id = wp_insert_post(
				array(
					'post_type'    => 'gutena_forms',
					'post_title'   => $title,
					'post_status'  => 'draft',
					'post_content' => wp_slash( $serialized ),
				),
				true
			);

			if ( is_wp_error( $post_id ) ) {
				return $post_id;
			}

			// 9. Store the form ID in post meta (matches template creation).
			update_post_meta( $post_id, 'gutena_form_id', $form_id );

			// The save_post hook fires inside wp_insert_post and persists the
			// form schema to the gutena_forms_schema_<formID> option and the
			// gutenaforms DB table automatically via save_gutena_forms_schema().

			return rest_ensure_response(
				array(
					'status'  => 'success',
					'message' => __( 'Form created successfully.', 'gutena-forms' ),
					'data'    => array(
						'post_id'  => $post_id,
						'form_id'  => $form_id,
						'edit_url' => get_edit_post_link( $post_id, 'raw' ),
					),
				)
			);
		}
	}

	Gutena_Forms_AI_Endpoints::get_instance();
endif;
