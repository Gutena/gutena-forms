<?php
/**
 * Plugin Name:       Gutena Forms - Contact Forms Block
 * Description:       Gutena Forms is the easiest way to create forms inside the WordPress block editor. Our plugin does not use jQuery and is lightweight, so you can rest assured that it won’t slow down your website. Instead, it allows you to quickly and easily create custom forms right inside the block editor.
 * Requires at least: 6.5
 * Requires PHP:      5.6
 * Version:           1.2.7
 * Author:            ExpressTech
 * Author URI:        https://expresstech.io
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gutena-forms
 *
 * @package           gutena-forms
 */


defined( 'ABSPATH' ) || exit;

/**
 * Abort if the class is already exists.
 */
if ( ! class_exists( 'Gutena_Forms' ) ) {

	/**
	 * Plugin dir path
	 */
	if ( ! defined( 'GUTENA_FORMS_FILE' ) ) {
		define( 'GUTENA_FORMS_FILE',  __FILE__ );
	}

	/**
	 * Plugin dir path
	 */
	if ( ! defined( 'GUTENA_FORMS_DIR_PATH' ) ) {
		define( 'GUTENA_FORMS_DIR_PATH',  plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Plugin url
	 */
	if ( ! defined( 'GUTENA_FORMS_PLUGIN_URL' ) ) {
		define( 'GUTENA_FORMS_PLUGIN_URL', esc_url( trailingslashit( plugins_url( '', __FILE__ ) ) ) );
	}

	/**
	 * Plugin version.
	 */
	if ( ! defined( 'GUTENA_FORMS_VERSION' ) ) {
		define( 'GUTENA_FORMS_VERSION', '1.2.6' );
	}

	if ( ! function_exists( 'is_gutena_forms_pro' ) ) {
		function is_gutena_forms_pro( $valid = true ) {
			return  true === $valid ? ( defined( 'GUTENA_FORMS_PRO_VALID' ) && GUTENA_FORMS_PRO_VALID ) : defined( 'GUTENA_FORMS_PRO_VERSION' );
		}
	}

	class Gutena_Forms {

		// The instance of this class
		private static $instance = null;

		// Returns the instance of this class.
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function __construct() {
			add_action( 'init', array( $this, 'register_blocks_and_scripts' ) );
			add_action( 'init', array( $this, 'register_blocks_styles' ) );
			add_filter( 'block_categories_all', array( $this, 'register_category' ), 10, 2 );
			add_action( 'save_post', array( $this, 'save_gutena_forms_schema' ), 10, 3 );
			// save pattern
			add_action( 'added_post_meta', array( $this, 'save_gutena_forms_pattern' ), 10, 4 );
			
			add_action( 'wp_ajax_gutena_forms_submit', array( $this, 'submit_form' ) );
			add_action( 'wp_ajax_nopriv_gutena_forms_submit', array( $this, 'submit_form' ) );
			//Dashboard
			$this->load_dashboard();
		}

		//load form dashboard
		private function load_dashboard() {
			if ( ! class_exists( 'Gutena_Forms_Admin' ) && file_exists( GUTENA_FORMS_DIR_PATH . 'includes/admin/class-admin.php' ) ) {
				require_once GUTENA_FORMS_DIR_PATH . 'includes/admin/class-admin.php';
			}
		}

		// Register blocks and scripts
		public function register_blocks_and_scripts() {
			if ( ! function_exists( 'register_block_type' ) ) {
				// Block editor is not available.
				return;
			}
			// Guten Forms Block
			register_block_type(
				__DIR__ . '/build',
				array(
					'render_callback' => array( $this, 'render_form' ),
				)
			);

			// Field group Block
			register_block_type( __DIR__ . '/build/field-group' );

			// Form Confirmation Message Block
			register_block_type( __DIR__ . '/build/form-confirm-msg' );

			// Form Error Message Block
			register_block_type( __DIR__ . '/build/form-error-msg' );

			// Form Field Block
			register_block_type(
				__DIR__ . '/build/form-field',
				array(
					'render_callback' => array( $this, 'render_form_field' ),
				)
			);

			//google recaptcha
			$grecaptcha = get_option( 'gutena_forms_grecaptcha', array() );
			
			//Form messages
			$gutena_forms_messages = get_option( 'gutena_forms_messages', array() );
			$gutena_forms_messages = empty( $gutena_forms_messages ) ? array(): $gutena_forms_messages;
			$gf_message = array(
				'required_msg'        => __( 'Please fill in this field', 'gutena-forms' ),
				'required_msg_optin'  => __( 'Please check this checkbox', 'gutena-forms' ),
				'required_msg_select' => __( 'Please select an option', 'gutena-forms' ),
				'required_msg_check' => __( 'Please check an option', 'gutena-forms' ),
				'invalid_email_msg'   => __( 'Please enter a valid email address', 'gutena-forms' ),
				'min_value_msg'=>  __( 'Input value should be greater than', 'gutena-forms' ),
				'max_value_msg'=>  __( 'Input value should be less than', 'gutena-forms' ),
			);
			//get saved messages by admin
			foreach ( $gf_message as $msg_key => $msg_value) {
				if ( ! empty( $gutena_forms_messages[ $msg_key ] ) ) {
					$gf_message[ $msg_key ] = $gutena_forms_messages[ $msg_key ];
				}
			}

			//Provide data for form submission script
			wp_localize_script(
				'gutena-forms-script',
				'gutenaFormsBlock',
				array_merge( array(
					'submit_action'       => 'gutena_forms_submit',
					'ajax_url'            => admin_url( 'admin-ajax.php' ),
					'nonce'               => wp_create_nonce( 'gutena_Forms' ),
					'grecaptcha_type'	  => ( empty( $grecaptcha ) || empty( $grecaptcha['type'] ) ) ? '0' : $grecaptcha['type'],
					'grecaptcha_site_key' => empty( $grecaptcha['site_key'] ) ? '': $grecaptcha['site_key'],
					'grecaptcha_secret_key' => ( function_exists( 'is_admin' ) && is_admin() && !empty( $grecaptcha['secret_key'] ) ) ? $grecaptcha['secret_key'] : '',
					'pricing_link' => esc_url( admin_url( 'admin.php?page=gutena-forms&pagetype=introduction#gutena-forms-pricing' ) )
				), $gf_message )
			);
		}

		public function register_blocks_styles() {
			if ( function_exists( 'register_block_style' ) ) {

				//Range Slider single
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

		// Register Gutena category if not exists
		public function register_category( $block_categories, $editor_context ) {
			$fields = wp_list_pluck( $block_categories, 'slug' );

			if ( ! empty( $editor_context->post ) && ! in_array( 'gutena', $fields, true ) ) {
				array_push(
					$block_categories,
					array(
						'slug'  => 'gutena',
						'title' => __( 'Gutena', 'gutena-forms' ),
					)
				);
			}

			return $block_categories;
		}

		/**
		 * Prepare attributes for form input field.
		 *
		 * @param array $attributes The block attributes.
		 * @param array $check_attr attribute to check for existance e.g. array('nameAttr'=>'name').
		 *
		 * @return string Rendered HTML attributes.
		 */
		public function get_field_attribute( $attributes , $check_attr = array() ) {
			
			//field_attr to render inside field
			$field_attr = '';

			//check if values are empty
			if ( empty( $attributes ) || empty( $check_attr ) ) {
				return $field_attr;
			}

			foreach ( $check_attr as $check => $input_attr ) {
				//continue loop if empty except zero value
				if ( ! isset( $attributes[$check] ) || ( empty( $attributes[$check] ) && '0' != $attributes[$check]  ) || empty( $input_attr ) ) {
					continue;
				}

				//if input attr is also an array then check recursively
				if ( is_array( $input_attr ) ) {
					$field_attr .= $this->get_field_attribute( $attributes[$check], $input_attr );
					continue;
				}

				$field_attr .= ' ' . sanitize_key( $input_attr ) . '="' . esc_attr( $attributes[$check] ) .'"';
			}

			return $field_attr;
		}

		/**
		 * Escape attributes after checking if isset.
		 *
		 * @param array $attributes The block attributes.
		 * @param array $key key to check for existance e.g. isset( $attributes[$key] ).
		 *
		 * @return string escaped or empty string.
		 */
		private function check_esc_attr( $attributes, $key = '' ) {
			
			if ( empty( $key ) ) {
				return ( isset( $attributes ) && ! is_array( $attributes ) ) ? esc_attr( $attributes ): '';
			}

			return isset( $attributes[ $key ] ) ? esc_attr( $attributes[ $key ] ): '';
		}

		// render_callback : form field
		public function render_form_field( $attributes, $content, $block ) {
			// No changes if fieldType is empty
			if ( empty( $attributes ) || empty( $attributes['fieldType'] ) || empty( $attributes['nameAttr'] ) ) {
				return $content;
			}

			// Get Block Supports like styles or classNames
			$wrapper_attributes = get_block_wrapper_attributes(
				array(
					'class' => 'gutena-forms-' . esc_attr( $attributes['fieldType'] ) . '-field field-name-' . esc_attr( $attributes['nameAttr'] ) .' '. ( empty( $attributes['optionsInline'] ) ? '':'gf-inline-content'  ),
				)
			);
			// Output Html
			$output = '';

			$inputAttr = '';

			//Check for required attribute
			$inputAttr .= empty( $attributes['isRequired'] ) ? '' : ' required';


			// Text type Input
			if ( in_array( $attributes['fieldType'], array( 'text', 'email', 'number' ) ) ) {
				$output = '<input 
				' . $this->get_field_attribute( $attributes, array(
					'nameAttr' 		=> 'name',
					'fieldType' 	=> 'type',
					'fieldClasses' 	=> 'class',
					'placeholder' 	=> 'placeholder',
					'maxlength' 	=> 'maxlength',
					'defaultValue' 	=> 'value',
					'minMaxStep' 	=> array(
						'min'=>'min',
						'max'=>'max',
						'step'=>'step',
					),
				) ) . 
				' ' . esc_attr( $inputAttr ) . ' 
				/>';
			}

			//Input Range slider
			if ( 'range' === $attributes['fieldType'] ) {
				$output = '<div class="gf-range-container">
				<input 
				' . $this->get_field_attribute( $attributes, array(
					'nameAttr' 		=> 'name',
					'fieldType' 	=> 'type',
					'fieldClasses' 	=> 'class',
					'minMaxStep' 	=> array(
						'min'=>'min',
						'max'=>'max',
						'step'=>'step',
					),
				) ) . 
				' ' . esc_attr( $inputAttr ) . ' 
				/><p class="gf-range-values"> ';

				//Range min value
				if ( ! empty( $attributes['minMaxStep'] ) && isset( $attributes['minMaxStep']['min'] ) ) {
					$output .= '
					<span class="gf-prefix-value-wrapper">
						<span class="gf-prefix">' . $this->check_esc_attr( $attributes, 'preFix' ) . '</span>
						<span class="gf-value">' . $this->check_esc_attr( $attributes['minMaxStep'], 'min' ) . '</span>
						<span class="gf-suffix">' . $this->check_esc_attr( $attributes, 'sufFix' ) . '</span>
					</span>';
				}

				//range input value
				$output .= '<span class="gf-prefix-value-wrapper">
					<span class="gf-prefix">' . $this->check_esc_attr( $attributes, 'preFix' ) . '</span>
					<span class="gf-value range-input-value"></span>
					<span class="gf-suffix">' . $this->check_esc_attr( $attributes, 'sufFix' ) . '</span>
				</span>';

				//Range max value
				if ( ! empty( $attributes['minMaxStep'] ) && ! empty( $attributes['minMaxStep']['max'] ) ) {	
					$output .= '<span class="gf-prefix-value-wrapper">
						<span class="gf-prefix">' . $this->check_esc_attr( $attributes, 'preFix' ) . '</span>
						<span class="gf-value">' . $this->check_esc_attr( $attributes['minMaxStep'], 'max' ) . '</span>
						<span class="gf-suffix">' . $this->check_esc_attr( $attributes, 'sufFix' ) . '</span>
					</span>
					';
				}

				$output .='</p></div>';
			}

			// Textarea type Input
			if ( 'textarea' === $attributes['fieldType'] ) {
				$output = '<textarea 
				' . $this->get_field_attribute( $attributes, array(
					'nameAttr' 		=> 'name',
					'textAreaRows' 	=> 'rows',
					'fieldClasses' 	=> 'class',
					'placeholder'	=> 'placeholder',
					'maxlength' 	=> 'maxlength',
				) ) . 
				' ' . esc_attr( $inputAttr ) . ' 
				>'. esc_html( empty( $attributes['defaultValue'] ) ? '' : $attributes['defaultValue'] ).'</textarea>';
			}

			// Select type Input
			if ( 'select' === $attributes['fieldType'] ) {
				$output = '<select  
				' . $this->get_field_attribute( $attributes, array(
					'nameAttr' 		=> 'name',
					'fieldClasses' 	=> 'class',
				) ) . 
				' ' . esc_attr( $inputAttr ) . ' 
				 >';
				if ( ! empty( $attributes['selectOptions'] ) && is_array( $attributes['selectOptions'] ) ) {
					foreach ( $attributes['selectOptions'] as $option ) {
						$output .= '<option value="' . esc_attr( $option ) . '" >' . esc_attr( $option ) . '</option>';
					}
				}
				$output .= '</select>';
			}

			// radio type Input
			if ( in_array( $attributes['fieldType'], array( 'radio', 'checkbox', 'optin' ) ) ) {
				$output = '<div  
				' . $this->get_field_attribute( $attributes, array(
					'fieldClasses' 	=> 'class',
				) ) . 
				' 
				>';
				if ( 'optin' == $attributes['fieldType'] ) {
					$output .= '<label class="' . esc_attr( $attributes['fieldType'] ) . '-container">
						<input type="checkbox" name="' . esc_attr( $attributes['nameAttr']  ) . 
						'" value="1" >
						<span class="checkmark"></span>
					  </label>';
				} else if ( ! empty( $attributes['selectOptions'] ) && is_array( $attributes['selectOptions'] ) ) {
					foreach ( $attributes['selectOptions'] as $option ) {
						$output .= '<label class="' . esc_attr( $attributes['fieldType'] ) . '-container">' . esc_attr( $option ) . '
						<input type="' . esc_attr( $attributes['fieldType'] ) . '" name="' . esc_attr( 'radio' === $attributes['fieldType'] ? $attributes['nameAttr'] : $attributes['nameAttr'].'[]'  ) . 
						'" value="' . esc_attr( $option ) . '" >
						<span class="checkmark"></span>
					  </label>';
					}
				}
				$output .= '</div>';
			}

			//filter output field
			$output = apply_filters( 'gutena_forms_render_field', $output, $attributes, $inputAttr, $block );

			//render field styles
			if ( ! empty( $attributes['fieldStyle'] ) && ! empty( $block->context['gutena-forms/formID'] ) && function_exists( 'wp_add_inline_style' ) ) {
				wp_add_inline_style( 
					'gutena-forms-style', 
					'.wp-block-gutena-forms.' . esc_attr( $block->context['gutena-forms/formID'] ) . ' .gutena-forms-' . esc_attr( $attributes['fieldType'] ) . '-field {
					' . esc_attr( $attributes['fieldStyle'] ) . '
					}'
				);
			}

			// output
			return sprintf(
				'<div %1$s>%2$s</div>',
				$wrapper_attributes,
				$output
			);
		}

		//Replace last occurance of a string
		public function str_last_replace( $search, $replace, $str ) {
			//finds the position of the last occurrence of a string
			$pos = strripos($str, $search);
		
			if ( $pos !== false ) {
				$str = substr_replace($str, $replace, $pos, strlen($search));
			}
		
			return $str;
		}

		// render_callback : form
		public function render_form( $attributes, $content, $block ) {
			
			// No changes if attributes is empty
			if ( empty( $attributes ) || empty( $attributes['adminEmails'] ) ) {
				return $content;
			}

			$html = '';
			if ( ! empty( $attributes['redirectUrl'] ) ) {
				$html = '<input type="hidden" name="redirect_url" value="' . esc_attr( esc_url( $attributes['redirectUrl'] ) ) . '" />';
			}

			//google recaptcha 
			$recaptcha_html = '';
			if ( ! empty( $attributes['recaptcha'] ) && ! empty( $attributes['recaptcha']['enable'] ) && ! empty( $attributes['recaptcha']['site_key'] ) && ! empty( $attributes['recaptcha']['type'] ) ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_grecaptcha_scripts' ));
				
				//input box for v2 type only
				if ( 'v2' === $attributes['recaptcha']['type'] ){
					$recaptcha_html = '<div class="g-recaptcha" data-sitekey="' . esc_attr( $attributes['recaptcha']['site_key'] ) . '"></div><br>';
				} 

				//input field to check if recaptcha or not
				$html .= '<input type="hidden" name="recaptcha_enable" value="' . esc_attr( $attributes['recaptcha']['enable'] ) . '" />';
			}

			// Add required html
			if ( ! empty( $html ) ) {
				$content = preg_replace(
					'/' . preg_quote( '>', '/' ) . '/',
					'>'.$html,
					$content,
					1
				);
			}

			//Submit Button HTML markup : change link to button tag
			$content = $this->str_last_replace(
				'<a', 
				$recaptcha_html.'<button',
				$content
			); 

			$content = $this->str_last_replace(
				'</a>', 
				'</button>',
				$content
			); 
			//filter content
			$content = apply_filters( 'gutena_forms_render_form', $content, $attributes );
			// Enqueue block styles
			$this->enqueue_block_styles( $attributes['formStyle'] );
			return $content;
		}

		// Enqueue Block local styles in head
		public function enqueue_block_styles( $style, $priority = 10 ) {

			if ( empty( $style ) || ! function_exists( 'wp_strip_all_tags' ) ) {
				return;
			}

			$action_hook_name = 'wp_footer';
			if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
				$action_hook_name = 'wp_head';
			}
			add_action(
				$action_hook_name,
				static function () use ( $style ) {
					echo '<style>' . wp_strip_all_tags( $style ) . "</style>\n";
				},
				$priority
			);
		}

		//Enqueue google recaptcha : Run once
		public function enqueue_grecaptcha_scripts() {
			static $recaptcha_start = 0;
			if ( 0 === $recaptcha_start  ) {
				$grecaptcha = get_option( 'gutena_forms_grecaptcha', false );
				if ( ! empty( $grecaptcha ) && ! empty( $grecaptcha['site_key'] ) && ! empty( $grecaptcha['type'] ) ) {

					wp_enqueue_script( 
						'google-recaptcha', 
						esc_url( 'https://www.google.com/recaptcha/api.js'.( ( 'v2' === $grecaptcha['type'] ) ? '' : '?render='. esc_attr( $grecaptcha['site_key'] )  ) ), 
						array(), 
						GUTENA_FORMS_VERSION, 
						false 
					);
				}

				++$recaptcha_start;
			}
		}

		// save form schema
		public function save_gutena_forms_schema( $post_id, $post, $update ) {
			//post should not be a rivision or trash
			if ( empty( $post_id ) || empty( $post ) || ! function_exists( 'parse_blocks' ) || ! function_exists( 'wp_is_post_revision' ) || wp_is_post_revision( $post_id ) || ! function_exists( 'get_post_status' ) || 'trash' === get_post_status( $post_id ) || ! has_block( 'gutena/forms', $post ) ) {
				return;
			}

			//block pattern
			if ( $update && 'wp_block' == $post->post_type ) {
				
				$wp_pattern_sync_status = get_post_meta( $post->ID, 'wp_pattern_sync_status', true );
				if ( ! empty( $wp_pattern_sync_status ) && 'unsynced' == $wp_pattern_sync_status ) {
					// unhook this function so it doesn't loop infinitely
					remove_action( 'save_post', array( $this, 'save_gutena_forms_schema' ), 10 );
					//correct and save unsynced pattern
					$this->correct_gutena_forms_pattern( $post );
					// re-hook this function.
					add_action( 'save_post', array( $this, 'save_gutena_forms_schema' ), 10, 3 );
					return;
				}
			}

			 // developer.wordpress.org/reference/functions/parse_blocks/
			$form_schema = $this->get_form_schema( parse_blocks( $post->post_content ) );
			if ( empty( $form_schema ) || ! is_array( $form_schema ) ) {
				return;
			}

			$gutena_form_ids = get_option( 'gutena_form_ids', array() );
			
			// Save gutena form schema in wp option
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
							$this->sanitize_array( $formSchema_filtered, true )
						);

						//Save Google reCAPTCHA details
						if ( ! empty( $formSchema['form_attrs']['recaptcha'] ) && ! empty( $formSchema['form_attrs']['recaptcha']['site_key'] ) && ! empty( $formSchema['form_attrs']['recaptcha']['secret_key'] ) ) {
							update_option(
								'gutena_forms_grecaptcha',
								$this->sanitize_array( $formSchema['form_attrs']['recaptcha'] )
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

			// Save gutena form ids in array gutena_form_ids
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
					$this->sanitize_array( $gutena_form_ids )
				);
			}

		}

		/**
		 * Correct unsynced form pattern
		 * remove form id from unsynched pattern so that it can be reuse
		 * 
		 * @param  integer $meta_id    ID of the meta data field
		 * @param  integer $post_id    Post ID
		 * @param  string $meta_key    Name of meta field
		 * @param  string $meta_value  Value of meta field
		 */
		public function save_gutena_forms_pattern( $meta_id, $post_id, $meta_key, $meta_value ) {
			//return if post meta is not for unsynced pattern
			if ( empty( $post_id ) || empty( $meta_key ) || empty( $meta_value ) || 'wp_pattern_sync_status' != $meta_key || 'unsynced' != $meta_value ) {
				return;
			}

			$post = get_post( $post_id );
			$this->correct_gutena_forms_pattern( $post );

		}

		/**
		 * Correct and save unsynced pattern
		 * 
		 * @param object $post 
		 * @param boolean $check_meta should check meta key or not
		 * 
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


		// Get Form schema from block parsing
		private function get_form_schema( $blocks, $formID = 0 ) {
			if ( empty( $blocks ) || ! is_array( $blocks ) ) {
				return;
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

		// sanitize_array
		public function sanitize_array( $array, $textarea_sanitize = false ) {
			if ( ! empty( $array ) && is_array( $array ) ) {
				foreach ( (array) $array as $key => $value ) {
					if ( is_array( $value ) ) {
						$array[ $key ] = $this->sanitize_array( $value );
					} else if ( 'block_markup' === $key && function_exists( 'wp_kses' ) ) {
						
						$array[ $key ] = wp_kses(
							$value,
							array_merge(
								wp_kses_allowed_html( 'post' ),
								array(
									'form' => array(
										'method'=> 1,
										'class'	=> 1,
										'style'	=> 1,
									),
									'input' => array(
										'type'=> 1,
										'name'	=> 1,
										'class'	=> 1,
										'value'	=> 1,
									),
								)
							)
						);
						//$array[ $key ] = wp_kses_post( $value );
					} else {
						$array[ $key ] = true === $textarea_sanitize ? sanitize_textarea_field( $value )  : sanitize_text_field( $value );
					}
				}
			}
			return $array;
		}

		// Submit Gutena Forms
		public function submit_form() {
			check_ajax_referer( 'gutena_Forms', 'nonce' );

			if ( empty( $_POST['formid'] ) ) {
				wp_send_json(
					array(
						'status'  => 'error',
						'message' => __( 'Missing form identity', 'gutena-forms' ),
					)
				);
			}

			$formID     = sanitize_key( wp_unslash( $_POST['formid'] ) );
			$formSchema = get_option( $formID );

			if ( empty( $formSchema ) || empty( $formSchema['form_attrs'] ) || empty( $formSchema['form_fields'] ) ) {
				wp_send_json(
					array(
						'status'  => 'error',
						'message' => __( 'Missing form details', 'gutena-forms' ),
					)
				);
			}

			//Check for google recaptcha
			if ( ! empty( $formSchema['form_attrs']['recaptcha'] ) && ! empty( $formSchema['form_attrs']['recaptcha']['enable'] ) && ! $this->recaptcha_verify() ) {
				wp_send_json(
					array(
						'status'  => 'error',
						'message' => __( 'Invalid reCAPTCHA', 'gutena-forms' ),
						'recaptcha_error'	  => isset( $_POST['recaptcha_error'] ) ? sanitize_text_field( $_POST['recaptcha_error'] ) : ''
					)
				);
			}

			$blog_title  = get_bloginfo( 'name' );
			$from_name =  empty( $formSchema['form_attrs']['emailFromName'] ) ? $blog_title : $formSchema['form_attrs']['emailFromName'];
			$from_name = sanitize_text_field( $from_name );

			$admin_email = sanitize_email( get_option( 'admin_email' ) );

			// Email To
			$to = empty( $formSchema['form_attrs']['adminEmails'] ) ? $admin_email : $formSchema['form_attrs']['adminEmails'];

			if ( ! is_array( $to ) ) {
				$to = explode( ',', $to );
			}

			foreach ( $to as $key => $toEmail ) {
				$to[ $key ] = sanitize_email( wp_unslash( $toEmail ) );
			}

			$reply_to = empty( $formSchema['form_attrs']['replyToEmail'] ) ? '' : $formSchema['form_attrs']['replyToEmail'];

			$reply_to = ( empty( $reply_to ) || empty( $_POST[ $reply_to ] ) ) ? '' : sanitize_email( wp_unslash( $_POST[ $reply_to ] ) );

			//First name field
			$reply_to_name = empty( $formSchema['form_attrs']['replyToName'] ) ? '' : $formSchema['form_attrs']['replyToName'];

			//Last name field
			$reply_to_lname = empty( $formSchema['form_attrs']['replyToLastName'] ) ? '' : $formSchema['form_attrs']['replyToLastName'];
			

			$reply_to_name = ( empty( $reply_to_name ) || empty( $_POST[ $reply_to_name ] ) ) ? sanitize_key( $reply_to ) : sanitize_text_field( wp_unslash( $_POST[ $reply_to_name ] ) );

			$reply_to_lname = ( empty( $reply_to_lname ) || empty( $_POST[ $reply_to_lname ] ) ) ? '' : sanitize_text_field( wp_unslash( $_POST[ $reply_to_lname ] ) );

			//Form submit Data for filter
			$form_submit_data = array(
				'formName' => empty( $formSchema['form_attrs']['formName'] ) ? '': $formSchema['form_attrs']['formName'],
				'formID' => $formSchema['form_attrs']['formID'],
				'emailFromName' => $from_name,
				'replyToEmail' => $reply_to,
				'replyToFname' => $reply_to_name,
				'replyToLname' => $reply_to_lname
			);

			$reply_to_name = $reply_to_name .' '.$reply_to_lname;


			// Email Subject
			$subject = sanitize_text_field( empty( $formSchema['form_attrs']['adminEmailSubject'] ) ? __( 'Form received', 'gutena-forms' ) . '- ' . $blog_title : $formSchema['form_attrs']['adminEmailSubject'] );

			$fieldSchema = $formSchema['form_fields'];
			$body        = '';

			foreach ( $_POST as $name_attr => $field_value ) {
				$name_attr   = sanitize_key( wp_unslash( $name_attr ) );

				if ( empty( $fieldSchema[ $name_attr ] ) || ( ! empty( $fieldSchema[ $name_attr ][ 'fieldType' ] ) && 'optin' == $fieldSchema[ $name_attr ][ 'fieldType' ] ) ) {
					continue;
				}

				$field_value = apply_filters( 'gutena_forms_field_value_for_email', $field_value, $fieldSchema[ $name_attr ], $formID );

				if ( is_array( $field_value ) ) {
					$field_value =	$this->sanitize_array( wp_unslash( $field_value ), true );
					$field_value = implode(", ", $field_value );
				} else {
					$field_value = sanitize_textarea_field( wp_unslash( $field_value ) );
				}

				//Add prefix in value if set
				if ( ! empty( $fieldSchema[ $name_attr ][ 'preFix' ] ) ) {
					$field_value = sanitize_text_field( $fieldSchema[ $name_attr ][ 'preFix' ] ).' '.$field_value;
				}

				//Add suffix in value if set
				if ( ! empty( $fieldSchema[ $name_attr ][ 'sufFix' ] ) ) {
					$field_value =  $field_value . ' ' . sanitize_text_field( $fieldSchema[ $name_attr ][ 'sufFix' ] );
				}

				$field_name = sanitize_text_field( empty( $fieldSchema[ $name_attr ]['fieldName'] ) ? str_ireplace( '_', ' ', $name_attr ) : $fieldSchema[ $name_attr ]['fieldName'] );

				//Form submit Data for filter
				$form_submit_data['submit_data'][ $field_name ] = $field_value;
				$form_submit_data['raw_data'][ $name_attr ] = array(
					'label' => $field_name,
					'value'	=> $field_value,
					'fieldType' =>  empty( $fieldSchema[ $name_attr ][ 'fieldType' ] ) ? 'text': $fieldSchema[ $name_attr ][ 'fieldType' ],
					'raw_value' => apply_filters( 
						'gutena_forms_field_raw_value', 
						wp_unslash( $_POST[ $name_attr ] ), 
						array(
							'field_name' => $field_name,
							'field_value' => $field_value,
							'fieldSchema' => $fieldSchema[ $name_attr ],
							'formID' => $formID,
						) 
					)
				);

				$field_email_html = '<p><strong>' . esc_html( $field_name ) . '</strong> <br />' . esc_html( $field_value ) . ' </p>';

				$field_email_html = apply_filters( 
					'gutena_forms_field_email_html', 
					$field_email_html, 
					array(
						'field_name' => $field_name,
						'field_value' => $field_value,
						'fieldSchema' => $fieldSchema[ $name_attr ],
						'formID' => $formID,
					) 
				);

				$body .= $field_email_html;

			}
			//submitted form raw data
			do_action( 'gutena_forms_submitted_data', $form_submit_data['raw_data'], $formID, $fieldSchema );
			do_action( 'gutena_forms_submission', $form_submit_data, $formSchema );

			// If admin don't want to get Email notification 
			if ( isset( $formSchema['form_attrs']['emailNotifyAdmin'] ) && ( '' === $formSchema['form_attrs']['emailNotifyAdmin'] || false === $formSchema['form_attrs']['emailNotifyAdmin'] || '0' == $formSchema['form_attrs']['emailNotifyAdmin'] ) ) {
				wp_send_json(
					array(
						'status'  => 'Success',
						'message' => __( 'success', 'gutena-forms' ),
						'detail'  => __( 'admin email notification off', 'gutena-forms' ),
					)
				);
			}

			//Email headers
			$headers = array(
				'Content-Type: text/html; charset=UTF-8',
				'From: ' . esc_html( $from_name ) . ' <' . $admin_email . '>',
			);
			//Add reply to header
			if ( ! empty( $reply_to ) ) {
				array_push(
					$headers,
					'Reply-To: ' . esc_html( $reply_to_name ) . ' <' . $reply_to . '>'
				);
			}

			//Apply filter for admin email notification
			$body    = apply_filters( 'gutena_forms_submit_admin_notification', $body, $form_submit_data );

			if ( ! is_gutena_forms_pro( false ) ) {
				/**
				 * https://stackoverflow.com/questions/17602400/html-email-in-gmail-css-style-attribute-removed
				 */
				$body .= '<div style="background-color: #fffbeb; width: fit-content; margin-top: 50px; padding: 14px 15px 12px 15px; border-radius: 10px;" > <span style="font-size: 13px; line-height: 1; display: flex;" > <span style="margin-right: 5px;" > </span> <span style="margin-right: 3px;" ><strong>' . __( 'Exciting News!', 'gutena-forms' ) . ' </strong></span> '. __( 'Now, you can view and manage all your form submissions right from the Gutena Forms Dashboard.', 'gutena-forms' ) . '<strong><a href="'.esc_url( admin_url( 'admin.php?page=gutena-forms' ) ).'" style="color: #E35D3F; margin-left: 1rem;" target="_blank" > ' . __( 'See all Entries', 'gutena-forms' ) . ' </a></strong></span></div>';
			}
			
			$body    = wpautop( $body, true );
			$body 	 = $this->email_html_body( $body, $subject );
			$subject = esc_html( $subject );
			$res     = wp_mail( $to, $subject, $body, $headers );

			if ( $res ) {
				wp_send_json(
					array(
						'status'  => 'Success',
						'message' => __( 'success', 'gutena-forms' ),
					)
				);
			} else {
				wp_send_json(
					array(
						'status'  => 'error',
						'message' => __( 'Sorry! your form was submitted, but the email could not be sent. The site admin may need to review the email settings.', 'gutena-forms' ),
						'details' => __( 'Failed to send email', 'gutena-forms' ),
					)
				);
			}

		}

		private function email_html_body( $body, $subject ) {
			$lang = function_exists( 'get_language_attributes' ) ? get_language_attributes('html') : 'lang="en"';
			return '
			<!DOCTYPE html>
			<html '. $lang .'>
				<head>
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<title>'.$subject.'</title>
				</head>
				<body style="margin:0;padding:0;background:#ffffff;">
				'.$body.'
				</body>
			</html>
			';
		}

		//verify Input reCAPTCHA
		private function recaptcha_verify(){
			//check if reCAPTCHA not embedded in the form
			if ( empty( $_POST['recaptcha_enable'] ) && empty( $_POST['g-recaptcha-response'] ) ) {
				return true; 
			}
			//default recaptcha failed is considered as spam
			$_POST['recaptcha_error'] = 'spam';

			if ( empty( $_POST['g-recaptcha-response'] ) ) {
				$_POST['recaptcha_error'] = 'Recaptcha input missing';
				return false;	
			} else {  
				//get reCAPTCHA settings
				$recaptcha_settings= get_option( 'gutena_forms_grecaptcha', false );

				if ( empty( $recaptcha_settings ) ) {
					return false;
				}
				//verify reCAPTCHA 
				$response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
					'body'        => array(
						'secret' => $recaptcha_settings['secret_key'],
						'response' => sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) )
					)
				));

				if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
					$_POST['recaptcha_error'] = 'No response from api';
					return false;//fail to verify
				}

				$api_response = json_decode( wp_remote_retrieve_body( $response ), true );

				if ( ! empty($api_response) && $api_response['success'] ) {

					$threshold_score = apply_filters( 'gutena_forms_recaptcha_threshold_score', ( empty( $recaptcha_settings['thresholdScore'] ) || $recaptcha_settings['thresholdScore'] < 0.5 ) ? 0.5 : $recaptcha_settings['thresholdScore'] );

					// check the hostname of the site where the reCAPTCHA was solved
					if ( ! empty( $api_response['hostname'] ) && function_exists( 'get_site_url' ) ) {
						$site_url = explode( "?", get_site_url() );
						if ( 5 < strlen( $site_url[0] ) && false === stripos( $site_url[0], $api_response['hostname'] ) ) {
							$_POST['recaptcha_error'] = 'different hostname';
							return false;//fail to verify hostname
						}
					}

					if ( 'v2' === $recaptcha_settings['type'] ) {
						return true;//for v2
					} else if ( isset( $api_response['score'] ) && $api_response['score'] > $threshold_score ) {
						return	apply_filters( 'gutena_forms_recaptcha_verify', true, $response );
					} else {
						return false;//spam
					}
				}else{
					return false;
				}
			}
		}

	}

	Gutena_Forms::get_instance();

}