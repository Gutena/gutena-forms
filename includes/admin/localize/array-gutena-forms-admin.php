<?php
/**
 * Localize array for Gutena Forms Admin.
 *
 * @since 1.8.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return the localized array for Gutena Forms Admin.
 *
 * @since 1.8.0
 * @return array The localized array for Gutena Forms Admin.
 */
return array(
	'pluginURL'               => esc_url( GUTENA_FORMS_PLUGIN_URL ),
	'adminURL'                => esc_url( admin_url() ),
	'hasPro'                  => is_gutena_forms_pro( false ),
	'featureList'             => array(
		__( 'Advance Filter for Entries', 'gutena-forms' ),
		__( 'Entry Notes', 'gutena-forms' ),
		__( 'Status Management', 'gutena-forms' ),
		__( 'Tags Management', 'gutena-forms' ),
		__( 'Manage User Access', 'gutena-forms' ),
		__( 'Premium Support', 'gutena-forms' ),
	),
	'gutenaFormsIntroduction' => array(
		'section' => array(
			'welcome'   => array(
				'into_img'			=> esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/welcome.png' ),
				'intro_video_link' => esc_url( 'https://www.youtube.com/watch?v=jkQs_6kwT2g' ),
				'title'			=> __( 'Welcome to Gutena Forms!', 'gutena-forms' ),
				'description' 	=> __( "Gutena Forms is the easiest way to create forms inside the WordPress block editor. Our plugin does not use jQuery and is lightweight, so you can rest assured that it won't slow down your website. Instead, it allows you to quickly and easily create custom forms right inside the block editor.", "gutena-forms" ),
				'pricing_btn_name'	=> __( 'See Pricing', 'gutena-forms' ),
				'help_btn_name'	=> __( 'Need Help?', 'gutena-forms' ),
			),
			'features'	=> array(
				'title' => __( 'Gutena Forms Features', 'gutena-forms' ),
				'items' => array(
					array(
						'is_pro' => false,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/features/form-editor.png' ),
						'title' => __( 'Build form with WP Editor', 'gutena-forms' ),
						'description' => __( 'Build forms effortlessly with WP Editor for a seamless and user-friendly form creation experience.', 'gutena-forms' ),
					),
					array(
						'is_pro' => true,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/features/entry-management.png' ),
						'title' => __( 'Entry Management', 'gutena-forms' ),
						'description' => __( 'Efficiently manage form submissions with comprehensive entry management and analysis capabilities.', 'gutena-forms' ),
					),
					array(
						'is_pro' => true,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/features/advance-filter.png' ),
						'title' => __( 'Advance Filter for Entries', 'gutena-forms' ),
						'description' => __( 'Easily locate specific entries with an advanced filtering system for efficient entry search.', 'gutena-forms' ),
					),
					array(
						'is_pro' => true,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/features/entry-notes.png' ),
						'title' => __( 'Entry Notes', 'gutena-forms' ),
						'description' => __( 'Collaborate and track progress by adding notes or comments to individual form entries.', 'gutena-forms' ),
					),
					array(
						'is_pro' => false,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/features/responsive-desigen.png' ),
						'title' => __( 'Responsive Mobile Friendly', 'gutena-forms' ),
						'description' => __( 'Ensure optimal user experience with fully responsive and mobile-friendly forms.', 'gutena-forms' ),
					),
					array(
						'is_pro' => true,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/features/premimum-support.png' ),
						'title' => __( 'Premium Support', 'gutena-forms' ),
						'description' => __( 'Get premium customer support for prompt assistance and guidance in using the plugin effectively.', 'gutena-forms' ),
					),
					array(
						'is_pro' => true,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/features/status-management.png' ),
						'title' => __( 'Status Management', 'gutena-forms' ),
						'description' => __( 'Organize and track form submissions with customizable entry statuses for streamlined workflow.', 'gutena-forms' ),
					),
					array(
						'is_pro' => true,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/features/tag-management.png' ),
						'title' => __( 'Tags Management', 'gutena-forms' ),
						'description' => __( 'Categorize and sort form entries using tags for efficient organization and reporting.', 'gutena-forms' ),
					),
					array(
						'is_pro' => true,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/features/user-access-management.png' ),
						'title' => __( 'User Access Management', 'gutena-forms' ),
						'description' => __( 'Manage user access and permissions to control form data security and privacy.', 'gutena-forms' ),
					),
					array(
						'is_pro' => false,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/features/no-jquery.png' ),
						'title' => __( 'No jQuery', 'gutena-forms' ),
						'description' => __( 'Enjoy improved performance and compatibility without jQuery dependencies.', 'gutena-forms' ),
					),
					array(
						'is_pro' => false,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/features/google-recaptcha.png' ),
						'title' => __( 'Google reCAPTCHA', 'gutena-forms' ),
						'description' => __( 'Enhance form security with Google reCAPTCHA integration to prevent spam submissions.', 'gutena-forms' ),
					),
					array(
						'is_pro' => false,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/features/fully-customizeable.png' ),
						'title' => __( 'Fully customizable', 'gutena-forms' ),
						'description' => __( 'Customize your forms extensively with various options for field types, layout, and styling.', 'gutena-forms' ),
					),
				)
			),
			'fields'	=> array(
				'title' => __( 'Gutena Forms Input Fields', 'gutena-forms' ),
				'items' => array(
					array(
						'is_pro' => false,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields/text.svg' ),
						'title' => __( 'Text', 'gutena-forms' ),
					),
					array(
						'is_pro' => false,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields/hash.svg' ),
						'title' => __( 'Number', 'gutena-forms' ),
					),
					array(
						'is_pro' => false,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/features/settings.svg' ),
						'title' => __( 'Range', 'gutena-forms' ),
					),
					array(
						'is_pro' => false,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields/email.svg' ),
						'title' => __( 'Email', 'gutena-forms' ),
					),
					array(
						'is_pro' => false,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields/dropdown.svg' ),
						'title' => __( 'Dropdown', 'gutena-forms' ),
					),
					array(
						'is_pro' => false,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields/checkbox.svg' ),
						'title' => __( 'Checkbox', 'gutena-forms' ),
					),
					array(
						'is_pro' => true,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields/phone.svg' ),
						'title' => __( 'Phone', 'gutena-forms' ),
					),
					array(
						'is_pro' => true,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields/radio.svg' ),
						'title' => __( 'Radio', 'gutena-forms' ),
					),
					array(
						'is_pro' => true,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields/calendar.svg' ),
						'title' => __( 'Date', 'gutena-forms' ),
					),
					array(
						'is_pro' => true,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields/time.svg' ),
						'title' => __( 'Time', 'gutena-forms' ),
					),
					array(
						'is_pro' => true,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields/rating.svg' ),
						'title' => __( 'Rating', 'gutena-forms' ),
					),
					array(
						'is_pro' => true,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields/country.svg' ),
						'title' => __( 'Country Dropdown ', 'gutena-forms' ),
					),
					array(
						'is_pro' => true,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields/state.svg' ),
						'title' => __( 'State', 'gutena-forms' ),
					),
					array(
						'is_pro' => true,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields/file.svg' ),
						'title' => __( 'File Upload', 'gutena-forms' ),
					),
					array(
						'is_pro' => true,
						'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields/link.svg' ),
						'title' => __( 'URL', 'gutena-forms' ),
					),
				)
			),
			'pricing'	=> array(
				'title' => __( 'Achieve more with Premium', 'gutena-forms' ),
				'subtitle' => __( 'Unlock the full potential of Gutena Form with Premium', 'gutena-forms' ),
				'billed_frequency' =>  __( 'Yearly', 'gutena-forms' ),
				'features' => array(
					__( 'All Premium Input Fields', 'gutena-forms' ),
					__( 'All Premium Features', 'gutena-forms' ),
					__( 'Premium Support', 'gutena-forms' ),
				),
				'items' => array(
					array(
						'title' => __( 'Basic', 'gutena-forms' ),
						'price'	=> '$29.99',
						'description' => __( '1 Site', 'gutena-forms' ),
						'btn_name' => __( 'Get Started', 'gutena-forms' ),
						'link'	=> 'https://gutenaforms.com/pricing/',
					),
					array(
						'title' => __( 'Professional', 'gutena-forms' ),
						'price'	=> '$49.99',
						'description' => __( '5 Site', 'gutena-forms' ),
						'link'	=> 'https://gutenaforms.com/pricing/',
						'btn_name' => __( 'Get Started', 'gutena-forms' ),
					),
					array(
						'title' => __( 'Business', 'gutena-forms' ),
						'price'	=> '$59.99',
						'description' => __( 'Unlimited Sites', 'gutena-forms' ),
						'link'	=> 'https://gutenaforms.com/pricing/',
						'btn_name' => __( 'Get Started', 'gutena-forms' ),
					),
				)
			),
			'faq'		=> array(
				'title' => __( 'Frequently asked questions', 'gutena-forms' ),
				'items' => array(
					array(
						'title'       => __( 'What is Gutena Forms?', 'gutena-forms' ),
						'description' => __( 'Gutena Forms is a WordPress plugin that allows you to create custom forms easily within the block editor, without jQuery, ensuring superior performance.', 'gutena-forms' ),
					),
					array(
						'title'       => __( "How does Gutena Forms differ from other form plugins?", "gutena-forms" ),
						'description' => __( "Gutena Forms integrates seamlessly with the block editor, offering a user-friendly form-building experience. It is lightweight and doesn't slow down your website.", "gutena-forms" ),
					),
					array(
						'title'       => __( 'Can I create different types of forms with Gutena Forms?', 'gutena-forms' ),
						'description' => __( 'Yes, Gutena Forms offers various form elements and customization options, enabling you to create contact forms, surveys, feedback forms, and more to suit your needs.', 'gutena-forms' ),
					),
					array(
						'title'       => __( 'Is Gutena Forms compatible with my theme?', 'gutena-forms' ),
						'description' => __( 'Yes, Gutena Forms is designed to be compatible with most WordPress themes, ensuring a consistent form-building experience regardless of your theme choice.', 'gutena-forms' ),
					),
					array(
						'title'       => __( 'Is Gutena Forms responsive and mobile-friendly?', 'gutena-forms' ),
						'description' => __( 'Yes, Gutena Forms ensures that your forms are fully responsive and adapt perfectly to different devices, providing an optimal user experience on smartphones, tablets, and desktops.', 'gutena-forms' ),
					),
					array(
						'title'       => __( ' Can I customize the look and feel of my forms?', 'gutena-forms' ),
						'description' => __( 'Absolutely! Gutena Forms offers extensive customization options, allowing you to personalize your forms with different field types, layouts, and custom styles.', 'gutena-forms' ),
					),
					array(
						'title'       => __( ' Is support available if I need assistance?', 'gutena-forms' ),
						'description' => __( 'Yes, we provide dedicated support for Gutena Forms to address any questions, issues, or guidance you may need during your form-building journey.', 'gutena-forms' ),
					),
					array(
						'title'       => __( 'Can Gutena Forms handle large volumes of form submissions?', 'gutena-forms' ),
						'description' => __( 'Yes, Gutena Forms offers robust entry management capabilities, allowing you to efficiently handle and analyze high volumes of form submissions from your WordPress dashboard.', 'gutena-forms' ),
					),
					array(
						'title'       => __( 'Can I transfer my license from one site to another?', 'gutena-forms' ),
						'description' => __( 'Yes, you can transfer your license from one site to another. Simply deactivate the license on the current site and then activate it on the new site. This process ensures that your license is valid and active for the new site, allowing you to continue using the plugin seamlessly.', 'gutena-forms' ),
					),
					array(
						'title'       => __( 'Is Gutena Forms regularly updated and maintained?', 'gutena-forms' ),
						'description' => __( 'Yes, Gutena Forms is regularly updated to ensure compatibility with the latest WordPress versions, security patches, and to bring new features and enhancements to the plugin.', 'gutena-forms' ),
					),
					array(
						'title'       => __( 'What happens when my license expires?', 'gutena-forms' ),
						'description' => __( 'When your license expires, you may lose access to updates, support, and premium features. However, the plugin will continue to function as it is.', 'gutena-forms' ),
					),
					array(
						'title'       => __( 'Can I upgrade my Premium plan?', 'gutena-forms' ),
						'description' => __( 'Yes, you can upgrade your Premium plan to include more sites. We currently offer 3 Premium Plans with increased site limits. Contact our support team for assistance with the upgrade process.', 'gutena-forms' ),
					),
				),
				'sales' => array(
					'title1' => __( 'Do you have any question?', 'gutena-forms' ),
					'title2' => __( 'Contact with Sales Team', 'gutena-forms' ),
					'link'	 => esc_attr( 'https://gutenaforms.com/contact?utm_source=plugin&utm_medium=footer&utm_campaign=contact_with_sales_team' ),
				)
			)
		)
	),
	'gutenaFormsDoc'          => array(
		'topics' => array(
			'title' => esc_html__( 'How to Topics and Tips', 'gutena-forms' ),
			'items' => array(
				array(
					'heading' =>  esc_html__( 'How to reuse Gutena forms on Multiple Pages?', 'gutena-forms' ),
					'link'    => esc_url( 'https://gutenaforms.com' . '/reuse-gutena-forms-on-multiple-pages' ),
				),
				array(
					'heading' =>  esc_html__( 'How to generate Google reCaptcha Site Key and Secret Key?', 'gutena-forms' ),
					'link'    => esc_url( 'https://gutenaforms.com' . '/how-to-generate-google-recaptcha-site-key-and-secret-key' ),
				),
				array(
					'heading' =>  esc_html__( 'How to start with Gutena Forms Pro?', 'gutena-forms' ),
					'link'    => esc_url( 'https://gutenaforms.com' . '/how-to-start-with-gutena-forms-pro' ),
				),
			)

		),
		'support' => array(
			'title'              => esc_html__( 'Need Help?', 'gutena-forms' ),
			'description'        => esc_html__( 'Have a question, we are happy to help! Get in touch with our support team.', 'gutena-forms' ),
			'documentation_link' => esc_attr( 'https://gutenaforms.com' . '/blog?utm_source=plugin&utm_medium=knowledge_base&utm_campaign=help_articles' ),
			'documentation_text' => esc_html__( 'Help Articles', 'gutena-forms' ),
			'link_text'          => esc_html__( 'Support', 'gutena-forms' ),
		),
		'changelog' => array(
			'title'       => esc_html__( 'Releases and fixes', 'gutena-forms' ),
			'description' => Gutena_Forms_Admin::get_instance()->get_changelog(),
		),
	),
);
