<?php
/**
 * Class Gutena_Forms_Form_Schema
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Form_Schema' ) ) :
	/**
	 * Gutena Forms Form Schema Class
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Form_Schema {
		/**
		 * The singleton instance of the class.
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Form_Schema $instance Singleton instance of the class.
		 */
		private static $instance;

		/**
		 * Get the singleton instance of the class.
		 *
		 * @since 1.6.0
		 * @return Gutena_Forms_Form_Schema
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Save forms schema.
		 *
		 * @since 1.6.0
		 * @param int     $post_id id of the post.
		 * @param WP_Post $post post object.
		 * @param bool    $update whether it's an update.
		 */
		public function save_forms_schema( $post_id, $post, $update ) {
			if ( empty( $post_id ) || empty( $post ) || ! function_exists( 'parse_blocks' ) || ! function_exists( 'wp_is_post_revision' ) || wp_is_post_revision( $post_id ) || ! function_exists( 'get_post_status' ) || 'trash' === get_post_status( $post_id ) || ! has_block( 'gutena/forms', $post ) ) {
				return;
			}

			if ( $update && 'wp_block' == $post->post_type ) {

				$wp_pattern_sync_status = get_post_meta( $post->ID, 'wp_pattern_sync_status', true );
				if ( ! empty( $wp_pattern_sync_status ) && 'unsynced' == $wp_pattern_sync_status ) {
					// unhook this function so it doesn't loop infinitely
					remove_action( 'save_post', array( $this, 'save_forms_schema' ), 10 );
					//correct and save unsynced pattern
					$this->correct_gutena_forms_pattern( $post );
					// re-hook this function.
					add_action( 'save_post', array( $this, 'save_forms_schema' ), 10, 3 );
					return;
				}
			}

			$form_schema = $this->get_form_schema( parse_blocks( $post->post_content ) );
			if ( empty( $form_schema ) || ! is_array( $form_schema ) ) {
				return;
			}

			$gutena_form_ids = get_option( 'gutena_form_ids', array() );

			if ( ! empty( $form_schema['form_schema'] ) && is_array( $form_schema['form_schema'] ) ) {
				$gutena_forms_blocks = explode( '<!-- wp:gutena/forms', $post->post_content );
				foreach ( $form_schema['form_schema'] as  $formSchema ) {
					if ( ! empty( $formSchema['form_attrs']['formID'] ) ) {
						//get block markup
						foreach ($gutena_forms_blocks as $gf_block) {
							if ( false !== stripos( $gf_block, $formSchema['form_attrs']['formID'] ) ) {
								$gf_block = explode( 'wp:gutena/forms -->', $gf_block );
								$formSchema['block_markup'] = '<!-- wp:gutena/forms' . $gf_block[0] . 'wp:gutena/forms -->';
								break;
							}
						}
						//filter for formSchema
						$formSchema_filtered = apply_filters( 'gutena_forms_save_form_schema', $formSchema, $formSchema['form_attrs']['formID'], $gutena_form_ids );
						//Save form schema
						update_option(
							sanitize_key( $formSchema['form_attrs']['formID'] ),
							Gutena_Forms_Helper::sanitize_array( $formSchema_filtered, true )
						);

						//Save Google reCAPTCHA details
						if ( ! empty( $formSchema['form_attrs']['recaptcha'] ) && ! empty( $formSchema['form_attrs']['recaptcha']['site_key'] ) && ! empty( $formSchema['form_attrs']['recaptcha']['secret_key'] ) ) {
							update_option(
								'gutena_forms_grecaptcha',
								Gutena_Forms_Helper::sanitize_array( $formSchema['form_attrs']['recaptcha'] )
							);
						}

						// cloudflare turnstile
						if ( ! empty( $formSchema['form_attrs']['cloudflareTurnstile'] ) && ! empty( $formSchema['form_attrs']['cloudflareTurnstile']['site_key'] ) && ! empty( $formSchema['form_attrs']['cloudflareTurnstile']['secret_key'] ) ) {
							update_option(
								'gutena_forms_cloudflare_turnstile',
								Gutena_Forms_Helper::sanitize_array( $formSchema['form_attrs']['cloudflareTurnstile'] )
							);
						}

						//Save common form messages
						if ( ! empty( $formSchema['form_attrs']['messages'] ) && is_array( $formSchema['form_attrs']['messages'] ) ) {
							update_option(
								'gutena_forms_messages',
								$formSchema['form_attrs']['messages']
							);
						}
					}
				}
			}

			if ( ! empty( $form_schema['form_ids'] ) ) {

				if ( ! empty( $gutena_form_ids ) && is_array( $gutena_form_ids ) ) {
					$gutena_form_ids = array_merge( $gutena_form_ids, $form_schema['form_ids'] );
				} else {
					$gutena_form_ids = $form_schema['form_ids'];
				}
				//unique ids only
				$gutena_form_ids = array_unique( $gutena_form_ids );

				update_option(
					'gutena_form_ids',
					Gutena_Forms_Helper::sanitize_array( $gutena_form_ids )
				);
			}
		}

		/**
		 * Get form schema from blocks.
		 *
		 * @since 1.6.0
		 * @param array  $blocks array of blocks.
		 * @param string $formID form id.
		 *
		 * @return array
		 */
		private function get_form_schema( $blocks, $formID = 0 ) {
			if ( empty( $blocks ) || ! is_array( $blocks ) ) {
				return [];
			}

			$form_schema = array();
			$form_ids    = array();
			$innerblocks = array();

			foreach ( $blocks as $block ) {

				if ( ! empty( $block['blockName'] ) && 'gutena/forms' === $block['blockName'] && ! empty( $block['attrs']['formID'] ) ) {
					$formID                               = $block['attrs']['formID'];
					$form_ids[]                           = $formID;
					$form_schema[ $formID ]['form_attrs'] = $block['attrs'];
				}

				if ( ! empty( $block['blockName'] ) && 'gutena/form-field' === $block['blockName'] && ! empty( $block['attrs']['nameAttr'] ) ) {
					$form_schema[ $formID ]['form_fields'][ $block['attrs']['nameAttr'] ] = $block['attrs'];
				}

				if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
					$innerblocks = $this->get_form_schema( $block['innerBlocks'], $formID );
					$form_schema = array_merge_recursive( $form_schema, $innerblocks['form_schema'] );
					$form_ids    = array_merge( $form_ids, $innerblocks['form_ids'] );
				}
			}

			return array(
				'form_ids'    => $form_ids,
				'form_schema' => $form_schema,
			);
		}

		/**
		 * Save forms pattern.
		 *
		 * @since 1.6.0
		 * @param int    $meta_id meta id.
		 * @param int    $post_id post id.
		 * @param string $meta_key meta key.
		 * @param mixed  $meta_value meta value.
		 */
		public function save_forms_pattern( $meta_id, $post_id, $meta_key, $meta_value ) {
			if ( empty( $post_id ) || empty( $meta_key ) || empty( $meta_value ) || 'wp_pattern_sync_status' != $meta_key || 'unsynced' != $meta_value ) {
				return;
			}

			$post = get_post( $post_id );
			$this->correct_gutena_forms_pattern( $post );
		}

		/**
		 * Correct gutena forms pattern
		 *
		 * @since 1.6.0
		 * @param WP_Post $post post object.
		 */
		private function correct_gutena_forms_pattern( $post ) {
			static $func_call = 0;
			//patterns are store under 'wp_block' post type
			//return if post is empty or not a pattern post_type
			if ( $func_call > 0 || empty( $post ) || empty( $post->ID ) ||  'wp_block' != $post->post_type || empty( $post->post_content ) || false === stripos( $post->post_content,"{\"formID\"" )  ) {
				return;
			}

			//get form id
			$first_extract = "\",\"formName\":";
			if ( false === stripos( $post->post_content, $first_extract ) ) {
				$first_extract = "\",\"formClasses\":";
			}

			$post_content = explode( $first_extract, $post->post_content );
			$post_content = explode( "{\"formID\":\"gutena_forms_ID_", $post_content[0] );
			$post_content = end( $post_content );
			$formID = wp_unslash( $post_content );
			$formID = "gutena_forms_ID_". $formID;
			//remove form id
			$post_content = str_ireplace( $formID, "" , $post->post_content );
			//count function call
			$func_call++;
			//Update pattern
			wp_update_post( array(
				'ID'           => $post->ID,
				'post_content' => $post_content,
			) );

		}
	}
endif;
