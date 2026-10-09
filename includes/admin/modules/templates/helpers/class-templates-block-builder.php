<?php
/**
 * Block builder helpers for form templates.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Templates_Block_Builder' ) ) :
	/**
	 * Converts inner block templates into serialized block arrays.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_Templates_Block_Builder {

		/**
		 * Generate a unique form ID matching the block editor format.
		 *
		 * @since 2.2.0
		 * @return string
		 */
		public static function generate_form_id() {
			$random = substr( preg_replace( '/\W/', '', wp_generate_password( 12, false ) ), 0, 12 );
			$now    = current_time( 'timestamp' );

			return 'gutena_forms_ID_' . $random . '_' . gmdate( 'j', $now ) . gmdate( 'n', $now ) . gmdate( 'Y', $now ) . gmdate( 'G', $now ) . gmdate( 'i', $now ) . gmdate( 's', $now );
		}

		/**
		 * Build a WordPress block array from an inner-blocks template tuple.
		 *
		 * @since 2.2.0
		 * @param array $template Block template tuple.
		 * @return array
		 */
		public static function build_block( $template ) {
			$name   = $template[0];
			$attrs  = ( isset( $template[1] ) && is_array( $template[1] ) ) ? $template[1] : array();
			$rest   = array_slice( $template, 2 );
			$inner  = array();

			foreach ( $rest as $child ) {
				if ( is_array( $child ) && ! empty( $child[0] ) && is_string( $child[0] ) ) {
					$inner[] = self::build_block( $child );
				}
			}

			$inner_content = array();
			if ( ! empty( $inner ) ) {
				$inner_content = array_fill( 0, count( $inner ) + 1, null );
			}

			return array(
				'blockName'    => $name,
				'attrs'        => $attrs,
				'innerBlocks'  => $inner,
				'innerHTML'    => '',
				'innerContent' => $inner_content,
			);
		}

		/**
		 * Build the root gutena/forms block.
		 *
		 * @since 2.2.0
		 * @param array  $form_attrs Form block attributes.
		 * @param array  $inner_templates Inner block templates.
		 * @param string $form_id Optional form ID.
		 * @return array
		 */
		public static function build_form_block( $form_attrs, $inner_templates, $form_id = '' ) {
			if ( empty( $form_id ) ) {
				$form_id = self::generate_form_id();
			}

			$inner_blocks = array();
			foreach ( $inner_templates as $template ) {
				$inner_blocks[] = self::build_block( $template );
			}

			$attrs = array_merge(
				array( 'formID' => $form_id ),
				$form_attrs
			);

			$inner_content = ! empty( $inner_blocks ) ? array_fill( 0, count( $inner_blocks ) + 1, null ) : array();

			return array(
				'blockName'    => 'gutena/forms',
				'attrs'        => $attrs,
				'innerBlocks'  => $inner_blocks,
				'innerHTML'    => '',
				'innerContent' => $inner_content,
			);
		}

		/**
		 * Standard contact-style fields used by several templates.
		 *
		 * @since 2.2.0
		 * @return array
		 */
		public static function contact_fields() {
			return array(
				self::text_field( 'f_0', __( 'First name', 'gutena-forms' ), __( 'Enter your first name', 'gutena-forms' ) ),
				self::text_field( 'f_1', __( 'Last name', 'gutena-forms' ), __( 'Enter your last name', 'gutena-forms' ) ),
				self::email_field( 'f_2' ),
				self::text_field( 'f_3', __( 'Subject', 'gutena-forms' ), __( 'Subject', 'gutena-forms' ) ),
				self::textarea_field( 'f_4', __( 'Message', 'gutena-forms' ), __( 'Type here', 'gutena-forms' ) ),
			);
		}

		/**
		 * Minimal blank form fields.
		 *
		 * @since 2.2.0
		 * @return array
		 */
		public static function blank_fields() {
			return array(
				self::text_field( 'f_0', __( 'Name', 'gutena-forms' ), __( 'Enter your name', 'gutena-forms' ) ),
				self::email_field( 'f_1' ),
				self::textarea_field( 'f_2', __( 'Message', 'gutena-forms' ), __( 'Type here', 'gutena-forms' ) ),
			);
		}

		/**
		 * Append submit button and message blocks to field templates.
		 *
		 * @since 2.2.0
		 * @param array  $fields       Field block templates.
		 * @param string $submit_label Submit button label.
		 * @return array
		 */
		public static function with_form_footer( $fields, $submit_label = '' ) {
			if ( empty( $submit_label ) ) {
				$submit_label = __( 'Submit', 'gutena-forms' );
			}

			return array_merge(
				$fields,
				array(
					self::submit_buttons( $submit_label ),
					self::confirm_message_block(),
					self::error_message_block(),
				)
			);
		}

		/**
		 * Text field template.
		 *
		 * @since 2.2.0
		 * @param string $name_attr Field name attribute.
		 * @param string $label Field label.
		 * @param string $placeholder Field placeholder.
		 * @param bool   $required Whether field is required.
		 * @return array
		 */
		public static function text_field( $name_attr, $label, $placeholder = '', $required = true ) {
			return array(
				'gutena/text-field',
				array(
					'nameAttr'    => $name_attr,
					'isRequired'  => $required,
					'fieldName'   => $label,
					'fieldType'   => 'text',
					'placeholder' => $placeholder,
				),
			);
		}

		/**
		 * Email field template.
		 *
		 * @since 2.2.0
		 * @param string $name_attr   Field name attribute.
		 * @param string $label       Field label.
		 * @param string $placeholder Field placeholder.
		 * @param bool   $required    Whether field is required.
		 * @return array
		 */
		public static function email_field( $name_attr, $label = '', $placeholder = '', $required = true ) {
			if ( empty( $label ) ) {
				$label = __( 'Email', 'gutena-forms' );
			}
			if ( empty( $placeholder ) ) {
				$placeholder = __( 'Enter email', 'gutena-forms' );
			}

			return array(
				'gutena/email-field',
				array(
					'nameAttr'    => $name_attr,
					'isRequired'  => $required,
					'fieldName'   => $label,
					'fieldType'   => 'email',
					'placeholder' => $placeholder,
				),
			);
		}

		/**
		 * Textarea field template.
		 *
		 * @since 2.2.0
		 * @param string $name_attr   Field name attribute.
		 * @param string $label       Field label.
		 * @param string $placeholder Field placeholder.
		 * @param int    $rows        Textarea rows.
		 * @param bool   $required    Whether field is required.
		 * @return array
		 */
		public static function textarea_field( $name_attr, $label, $placeholder = '', $rows = 5, $required = true ) {
			if ( empty( $placeholder ) ) {
				$placeholder = __( 'Enter description', 'gutena-forms' );
			}

			return array(
				'gutena/textarea-field',
				array(
					'nameAttr'     => $name_attr,
					'isRequired'   => $required,
					'fieldName'    => $label,
					'fieldType'    => 'textarea',
					'textAreaRows' => $rows,
					'placeholder'  => $placeholder,
				),
			);
		}

		/**
		 * Phone field for templates: uses the pro phone block when Pro is active.
		 *
		 * @since 2.2.0
		 * @param string $name_attr   Field name attribute.
		 * @param string $label       Field label.
		 * @param string $placeholder Field placeholder.
		 * @param bool   $required    Whether field is required.
		 * @return array
		 */
		public static function phone_field_for_template( $name_attr, $label = '', $placeholder = '', $required = false ) {
			if ( function_exists( 'is_gutena_forms_pro' ) && is_gutena_forms_pro() ) {
				return self::phone_field( $name_attr, $label, $placeholder, $required );
			}

			if ( empty( $label ) ) {
				$label = __( 'Phone', 'gutena-forms' );
			}

			if ( empty( $placeholder ) ) {
				$placeholder = __( 'Enter phone number', 'gutena-forms' );
			}

			return self::text_field( $name_attr, $label, $placeholder, $required );
		}

		/**
		 * Date field for templates: uses the pro date block when Pro is active.
		 *
		 * @since 2.2.0
		 * @param string $name_attr   Field name attribute.
		 * @param string $label       Field label.
		 * @param string $placeholder Field placeholder.
		 * @param bool   $required    Whether field is required.
		 * @return array
		 */
		public static function date_field_for_template( $name_attr, $label, $placeholder = '', $required = false ) {
			if ( function_exists( 'is_gutena_forms_pro' ) && is_gutena_forms_pro() ) {
				return self::date_field( $name_attr, $label, $placeholder, $required );
			}

			if ( empty( $placeholder ) ) {
				$placeholder = 'dd/mm/yyyy';
			}

			return self::text_field( $name_attr, $label, $placeholder, $required );
		}

		/**
		 * Phone field template.
		 *
		 * @since 2.2.0
		 * @param string $name_attr   Field name attribute.
		 * @param string $label       Field label.
		 * @param string $placeholder Field placeholder.
		 * @param bool   $required    Whether field is required.
		 * @return array
		 */
		public static function phone_field( $name_attr, $label = '', $placeholder = '', $required = false ) {
			if ( empty( $label ) ) {
				$label = __( 'Phone', 'gutena-forms' );
			}

			return array(
				'gutena/phone-field',
				array(
					'nameAttr'    => $name_attr,
					'isRequired'  => $required,
					'fieldName'   => $label,
					'fieldType'   => 'phone',
					'placeholder' => $placeholder,
					'settings'    => array(
						'defaultCountry'        => 'US',
						'showSelectedCountries' => false,
					),
				),
			);
		}

		/**
		 * Dropdown field template.
		 *
		 * @since 2.2.0
		 * @param string $name_attr Field name attribute.
		 * @param string $label     Field label.
		 * @param array  $options   Select options.
		 * @param bool   $required  Whether field is required.
		 * @return array
		 */
		public static function dropdown_field( $name_attr, $label, $options = array(), $required = true ) {
			return array(
				'gutena/dropdown-field',
				array(
					'nameAttr'      => $name_attr,
					'isRequired'    => $required,
					'fieldName'     => $label,
					'fieldType'     => 'select',
					'selectOptions' => $options,
				),
			);
		}

		/**
		 * Radio field template.
		 *
		 * @since 2.2.0
		 * @param string $name_attr       Field name attribute.
		 * @param string $label           Field label.
		 * @param array  $options         Radio options.
		 * @param bool   $required        Whether field is required.
		 * @param bool   $options_inline  Whether options display inline.
		 * @param int    $options_columns Number of option columns.
		 * @return array
		 */
		public static function radio_field( $name_attr, $label, $options = array(), $required = false, $options_inline = true, $options_columns = 1 ) {
			return array(
				'gutena/radio-field',
				array(
					'nameAttr'       => $name_attr,
					'isRequired'     => $required,
					'fieldName'      => $label,
					'fieldType'      => 'radio',
					'selectOptions'  => $options,
					'optionsInline'  => $options_inline,
					'optionsColumns' => $options_columns,
				),
			);
		}

		/**
		 * Checkbox field template.
		 *
		 * @since 2.2.0
		 * @param string $name_attr       Field name attribute.
		 * @param string $label           Field label.
		 * @param array  $options         Checkbox options.
		 * @param bool   $required        Whether field is required.
		 * @param bool   $options_inline  Whether options display inline.
		 * @param int    $options_columns Number of option columns.
		 * @return array
		 */
		public static function checkbox_field( $name_attr, $label, $options = array(), $required = false, $options_inline = true, $options_columns = 1 ) {
			return array(
				'gutena/checkbox-field',
				array(
					'nameAttr'       => $name_attr,
					'isRequired'     => $required,
					'fieldName'      => $label,
					'fieldType'      => 'checkbox',
					'selectOptions'  => $options,
					'optionsInline'  => $options_inline,
					'optionsColumns' => $options_columns,
				),
			);
		}

		/**
		 * Number field template.
		 *
		 * @since 2.2.0
		 * @param string $name_attr   Field name attribute.
		 * @param string $label       Field label.
		 * @param string $placeholder Field placeholder.
		 * @param bool   $required    Whether field is required.
		 * @return array
		 */
		public static function number_field( $name_attr, $label, $placeholder = '', $required = false ) {
			return array(
				'gutena/number-field',
				array(
					'nameAttr'    => $name_attr,
					'isRequired'  => $required,
					'fieldName'   => $label,
					'fieldType'   => 'number',
					'placeholder' => $placeholder,
				),
			);
		}

		/**
		 * Date field template.
		 *
		 * @since 2.2.0
		 * @param string $name_attr   Field name attribute.
		 * @param string $label       Field label.
		 * @param string $placeholder Field placeholder.
		 * @param bool   $required    Whether field is required.
		 * @return array
		 */
		public static function date_field( $name_attr, $label, $placeholder = '', $required = false ) {
			if ( empty( $placeholder ) ) {
				$placeholder = 'dd/mm/yyyy';
			}

			return array(
				'gutena/date-field',
				array(
					'nameAttr'    => $name_attr,
					'isRequired'  => $required,
					'fieldName'   => $label,
					'fieldType'   => 'date',
					'placeholder' => $placeholder,
				),
			);
		}

		/**
		 * File upload field template.
		 *
		 * @since 2.2.0
		 * @param string $name_attr   Field name attribute.
		 * @param string $label       Field label.
		 * @param string $description Accepted file types description.
		 * @param bool   $required    Whether field is required.
		 * @return array
		 */
		public static function file_upload_field( $name_attr, $label, $description = '', $required = false ) {
			return array(
				'gutena/file-upload-field',
				array(
					'nameAttr'    => $name_attr,
					'isRequired'  => $required,
					'fieldName'   => $label,
					'fieldType'   => 'file',
					'description' => $description,
					'settings'    => array(
						'actionBtnName' => __( 'Upload', 'gutena-forms' ),
					),
				),
			);
		}

		/**
		 * Rating field template.
		 *
		 * @since 2.2.0
		 * @param string $name_attr Field name attribute.
		 * @param string $label     Field label.
		 * @param bool   $required  Whether field is required.
		 * @return array
		 */
		public static function rating_field( $name_attr, $label, $required = false ) {
			return array(
				'gutena/rating-field',
				array(
					'nameAttr'       => $name_attr,
					'isRequired'     => $required,
					'fieldName'      => $label,
					'fieldType'      => 'rating',
					'optionsColumns' => 5,
					'settings'       => array(
						'gap' => 10,
					),
				),
			);
		}

		/**
		 * Opt-in / consent checkbox field template.
		 *
		 * @since 2.2.0
		 * @param string $name_attr Field name attribute.
		 * @param string $label     Field label.
		 * @param bool   $required  Whether field is required.
		 * @return array
		 */
		public static function optin_field( $name_attr, $label, $required = true ) {
			return array(
				'gutena/optin-field',
				array(
					'nameAttr'   => $name_attr,
					'isRequired' => $required,
					'fieldName'  => $label,
					'fieldType'  => 'optin',
				),
			);
		}

		/**
		 * URL field template.
		 *
		 * @since 2.2.0
		 * @param string $name_attr   Field name attribute.
		 * @param string $label       Field label.
		 * @param string $placeholder Field placeholder.
		 * @param bool   $required    Whether field is required.
		 * @return array
		 */
		public static function url_field( $name_attr, $label, $placeholder = '', $required = false ) {
			return array(
				'gutena/url-field',
				array(
					'nameAttr'    => $name_attr,
					'isRequired'  => $required,
					'fieldName'   => $label,
					'fieldType'   => 'text',
					'placeholder' => $placeholder,
				),
			);
		}

		/**
		 * Submit buttons block template.
		 *
		 * @since 2.2.0
		 * @param string $label Submit button label.
		 * @return array
		 */
		public static function submit_buttons( $label = '' ) {
			if ( empty( $label ) ) {
				$label = __( 'Submit', 'gutena-forms' );
			}

			return array(
				'core/buttons',
				array( 'className' => 'gutena-forms-submit-buttons' ),
				array(
					'core/button',
					array(
						'text'        => $label,
						'className'   => 'gutena-forms-submit-button',
						'placeholder' => $label,
					),
				),
			);
		}

		/**
		 * Success message block template.
		 *
		 * @since 2.2.0
		 * @return array
		 */
		public static function confirm_message_block() {
			$success_icon = GUTENA_FORMS_PLUGIN_URL . 'src/blocks/form/variations/assets/success-tick.svg';

			return array(
				'gutena/form-confirm-msg',
				array(),
				array(
					'core/group',
					array(
						'style'  => array(
							'spacing' => array(
								'blockGap' => '8px',
								'padding'  => array(
									'bottom' => '12px',
									'right'  => '12px',
									'left'   => '12px',
									'top'    => '12px',
								),
							),
							'color'  => array( 'background' => '#d8eacc' ),
							'border' => array( 'radius' => '5px' ),
						),
						'layout' => array(
							'type'              => 'flex',
							'flexWrap'          => 'nowrap',
							'verticalAlignment' => 'center',
							'justifyContent'    => 'left',
						),
					),
					array(
						'core/image',
						array(
							'sizeSlug'        => 'large',
							'linkDestination' => 'none',
							'className'       => 'form-message-icon',
							'url'             => $success_icon,
							'alt'             => __( 'Success', 'gutena-forms' ),
						),
					),
					array(
						'core/paragraph',
						array(
							'style'     => array(
								'typography' => array(
									'lineHeight' => '1.2',
									'fontStyle'  => 'normal',
									'fontWeight' => '500',
									'fontSize'   => '12px',
								),
							),
							'textColor' => 'black',
							'fontSize'  => 'tiny',
							'content'   => __( 'Your form submitted successfully!', 'gutena-forms' ),
						),
					),
				),
			);
		}

		/**
		 * Error message block template.
		 *
		 * @since 2.2.0
		 * @return array
		 */
		public static function error_message_block() {
			$error_icon = GUTENA_FORMS_PLUGIN_URL . 'src/blocks/form/variations/assets/error.svg';

			return array(
				'gutena/form-error-msg',
				array(),
				array(
					'core/group',
					array(
						'style'  => array(
							'spacing' => array(
								'blockGap' => '8px',
								'padding'  => array(
									'bottom' => '12px',
									'right'  => '12px',
									'left'   => '12px',
									'top'    => '12px',
								),
							),
							'color'  => array( 'background' => '#ffd3d3' ),
							'border' => array( 'radius' => '5px' ),
						),
						'layout' => array(
							'type'              => 'flex',
							'flexWrap'          => 'nowrap',
							'verticalAlignment' => 'center',
							'justifyContent'    => 'left',
						),
					),
					array(
						'core/image',
						array(
							'sizeSlug'        => 'large',
							'linkDestination' => 'none',
							'className'       => 'form-message-icon',
							'url'             => $error_icon,
							'alt'             => __( 'Error', 'gutena-forms' ),
						),
					),
					array(
						'core/paragraph',
						array(
							'style'     => array(
								'typography' => array(
									'lineHeight' => '1.2',
									'fontStyle'  => 'normal',
									'fontWeight' => '500',
									'fontSize'   => '12px',
								),
							),
							'textColor' => 'black',
							'className' => 'gutena-forms-error-text',
							'content'   => __( 'Sorry! your form was not submitted properly, Please check the errors above.', 'gutena-forms' ),
						),
					),
				),
			);
		}

		/**
		 * Two-column wrapper for left/right field groups.
		 *
		 * @since 2.2.0
		 * @param array $left_fields Left column field templates.
		 * @param array $right_fields Right column field templates.
		 * @return array
		 */
		public static function two_column_fields( $left_fields, $right_fields ) {
			$left_column  = array_merge( array( 'core/column', array() ), $left_fields );
			$right_column = array_merge( array( 'core/column', array() ), $right_fields );

			return array(
				array_merge(
					array( 'core/columns', array() ),
					array( $left_column, $right_column )
				),
			);
		}
	}
endif;
