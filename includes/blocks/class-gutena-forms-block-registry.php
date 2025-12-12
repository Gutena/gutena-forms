<?php
/**
 * Class Gutena_Forms_Block_Registry
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Block_Registry' ) ) :
	/**
	 * Gutena Forms Block Registry Class
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Block_Registry {
		/**
		 * The single instance of the class.
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Block_Registry $instance The single instance of the class.
		 */
		private static $instance;

		/**
		 * Form block instance.
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Form_Block $form_block Form block instance.
		 */
		private $form_block;

		/**
		 * Field block instance.
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Field_Block $field_block Field block instance.
		 */
		private $field_block;

		/**
		 * Get the instance of the class.
		 *
		 * @since 1.6.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @since 1.6.0
		 */
		private function __construct() {
			$this->form_block  = Gutena_Forms_Form_Block::get_instance();
			$this->field_block = Gutena_Forms_Field_Block::get_instance();
		}

		/**
		 * Register blocks and scripts.
		 *
		 * @since 1.6.0
		 */
		public function register_blocks_and_scripts() {
			if ( ! function_exists( 'register_block_type' ) ) {
				return;
			}

			register_block_type(
				GUTENA_FORMS_DIR_PATH . '/build',
				array(
					'render_callback' => array( $this->form_block, 'render' ),
				)
			);

			register_block_type( GUTENA_FORMS_DIR_PATH . '/build/form-confirm-msg' );
			register_block_type( GUTENA_FORMS_DIR_PATH . '/build/form-error-msg' );

			register_block_type(
				GUTENA_FORMS_DIR_PATH . '/build/form-field',
				array(
					'render_callback' => array( $this->field_block, 'render' ),
				)
			);

			$this->register_field_groups();
			$this->localize_scripts();
		}

		/**
		 * Register form field groups.
		 *
		 * @since 1.5.0
		 */
		private function register_field_groups() {
			$fields = array(
				'text-field-group'     => array(
					'name'  => 'gutena/text-field-group',
					'type'  => 'text',
					'title' => 'Text Field',
					'dir'   => 'text-field-group',
				),
				'email-field-group'    => array(
					'name'  => 'gutena/email-field-group',
					'type'  => 'email',
					'title' => 'Email Field',
					'dir'   => 'email-field-group',
				),
				'textarea-field-group' => array(
					'name'  => 'gutena/textarea-field-group',
					'type'  => 'textarea',
					'title' => 'Textarea Field',
					'dir'   => 'textarea-field-group',
				),
				'range-field-group'    => array(
					'name'  => 'gutena/range-field-group',
					'type'  => 'range',
					'title' => 'Range Slider Field',
					'dir'   => 'range-field-group',
				),
				'radio-field-group'    => array(
					'name'  => 'gutena/radio-field-group',
					'type'  => 'radio',
					'title' => 'Radio Field',
					'dir'   => 'radio-field-group',
				),
				'checkbox-field-group' => array(
					'name'  => 'gutena/checkbox-field-group',
					'type'  => 'checkbox',
					'title' => 'Checkbox Field',
					'dir'   => 'checkbox-field-group',
				),
				'dropdown-field-group' => array(
					'name'  => 'gutena/dropdown-field-group',
					'type'  => 'select',
					'title' => 'Dropdown Field',
					'dir'   => 'dropdown-field-group',
				),
				'optin-field-group'    => array(
					'name'  => 'gutena/optin-field-group',
					'type'  => 'optin',
					'title' => 'Opt-in Field',
					'dir'   => 'optin-field-group',
				),
				'number-field-group'   => array(
					'name'  => 'gutena/number-field-group',
					'type'  => 'number',
					'title' => 'Number Field',
					'dir'   => 'number-field-group',
				),
			);

			$fields    = apply_filters( 'gutena_forms__register_fields', $fields );
			$group_dir = GUTENA_FORMS_DIR_PATH . '/build/form-fields/';

			usort(
				$fields,
				function ( $a, $b ) {
					return strcmp( $a['title'], $b['title'] );
				}
			);

			foreach ( $fields as $field => $field_args ) {
				if ( file_exists( $group_dir . $field_args['dir'] . '/block.json' ) ) {
					register_block_type( $group_dir . $field_args['dir'] );
				} elseif ( file_exists( $field_args['dir'] . '/block.json' ) ) {
					register_block_type( $field_args['dir'] );
				}
			}
		}

		/**
		 * Localize scripts.
		 *
		 * @since 1.6.0
		 */
		private function localize_scripts() {
			$grecaptcha 		   = get_option( 'gutena_forms_grecaptcha', array() );
			$gutena_forms_messages = get_option( 'gutena_forms_messages', array() );
			$cloudflare_turnstile  = get_option( 'gutena_forms_cloudflare_turnstile', array() );
			$gutena_forms_messages = empty( $gutena_forms_messages ) ? array(): $gutena_forms_messages;
			$gf_message 		   = array(
				'required_msg'        => __( 'Please fill in this field', 'gutena-forms' ),
				'required_msg_optin'  => __( 'Please check this checkbox', 'gutena-forms' ),
				'required_msg_select' => __( 'Please select an option', 'gutena-forms' ),
				'required_msg_check' => __( 'Please check an option', 'gutena-forms' ),
				'invalid_email_msg'   => __( 'Please enter a valid email address', 'gutena-forms' ),
				'min_value_msg'=>  __( 'Input value should be greater than', 'gutena-forms' ),
				'max_value_msg'=>  __( 'Input value should be less than', 'gutena-forms' ),
			);

			foreach ( $gf_message as $msg_key => $msg_value) {
				if ( ! empty( $gutena_forms_messages[ $msg_key ] ) ) {
					$gf_message[ $msg_key ] = $gutena_forms_messages[ $msg_key ];
				}
			}

			wp_localize_script(
				'gutena-forms-script',
				'gutenaFormsBlock',
				array_merge(
					array(
						'submit_action'         => 'gutena_forms_submit',
						'ajax_url'              => admin_url( 'admin-ajax.php' ),
						'nonce'                 => wp_create_nonce( 'gutena_Forms' ),
						'grecaptcha_type'	    => ( empty( $grecaptcha ) || empty( $grecaptcha['type'] ) ) ? '0' : $grecaptcha['type'],
						'grecaptcha_site_key'   => empty( $grecaptcha['site_key'] ) ? '': $grecaptcha['site_key'],
						'grecaptcha_secret_key' => ( function_exists( 'is_admin' ) && is_admin() && !empty( $grecaptcha['secret_key'] ) ) ? $grecaptcha['secret_key'] : '',
						'pricing_link'          => 'https://gutenaforms.com/pricing/',
						'cloudflare_turnstile'  => empty( $cloudflare_turnstile ) ? array() : $cloudflare_turnstile,
						'is_pro'                => Gutena_Forms_Helper::has_pro(),
					),
					$gf_message
				)
			);
		}

		/**
		 * Register blocks styles.
		 *
		 * @since 1.6.0
		 */
		public function register_blocks_styles() {
			if ( ! function_exists( 'register_block_style' ) ) {
				return;
			}

			register_block_style(
				'gutena/form-field',
				array(
					'name'         => 'round-range-slider',
					'label'        => __( 'Border Style', 'gutena-forms' ),
					'is_default'   => false,
					'inline_style' => '.wp-block-gutena-forms .is-style-round-range-slider .gutena-forms-field.range-field {
							-webkit-appearance: none;
							width: 100%;
							height: 8px;
							border: 1px solid var(--wp--gutena-forms--input-border-color, #D7DBE7);
							border-radius: 5px;
							background: var(--wp--gutena-forms--input-bg-color,"transparent");
							outline: none;
							-webkit-transition: .2s;
							transition: opacity .2s;
						 }
						 .wp-block-gutena-forms .is-style-round-range-slider .gutena-forms-field.range-field:hover{
							border: 1px solid var(--wp--gutena-forms--input-border-color, #D7DBE7);
							opacity: 1;
						 }
						 .wp-block-gutena-forms .is-style-round-range-slider .gutena-forms-field.range-field:focus {
							border: 1px solid var(--wp--gutena-forms--input-focus-border-color, var(--wp--preset--color--primary, #3F6DE4 ));
						 }
						 .wp-block-gutena-forms .is-style-round-range-slider .gutena-forms-field.range-field::-webkit-slider-thumb {
							-webkit-appearance: none;
							appearance: none;
							width: 20px;
							height: 20px;
							border: 2px solid var(--wp--gutena-forms--input-border-color, #D7DBE7);
							border-radius: 50%;
							background: var(--wp--gutena-forms--input-focus-border-color, var(--wp--preset--color--primary, #3F6DE4 ));
							cursor: pointer;
						  }
						  .wp-block-gutena-forms .is-style-round-range-slider .gutena-forms-field.range-field::-moz-range-thumb {
							width: 20px;
							height: 20px;
							border: 2px solid var(--wp--gutena-forms--input-border-color, #D7DBE7);
							border-radius: 50%;
							background: var(--wp--gutena-forms--input-focus-border-color, var(--wp--preset--color--primary, #3F6DE4 ));
							cursor: pointer;
						  }
						',
				)
			);
		}
	}
endif;
