<?php

/**
 * Theme Options v1.1.0
 * Adjust theme settings from the admin dashboard.
 * Find and replace `YourTheme` with your own namepspacing.
 *
 * Created by Michael Fields.
 * https://gist.github.com/mfields/4678999
 *
 * Forked by Chris Ferdinandi
 * http://gomakethings.com
 *
 * Free to use under the MIT License.
 * http://gomakethings.com/mit/
 */


	/**
	 * Theme Options Fields
	 * Each option field requires its own uniquely named function. Select options and radio buttons also require an additional uniquely named function with an array of option choices.
	 */

	function mailchimp_settings_field_mailchimp_api_key() {
		$options = mailchimp_get_theme_options();
		?>
		<input type="text" name="mailchimp_theme_options[mailchimp_api_key]" class="regular-text" id="mailchimp_api_key" value="<?php echo esc_attr( $options['mailchimp_api_key'] ); ?>" />
		<label class="description" for="mailchimp_api_key"><?php _e( 'MailChimp API key', 'mailchimp' ); ?></label>
		<?php
	}

	function mailchimp_settings_field_mailchimp_list_id() {
		$options = mailchimp_get_theme_options();
		?>
		<input type="text" name="mailchimp_theme_options[mailchimp_list_id]" class="regular-text" id="mailchimp_list_id" value="<?php echo esc_attr( $options['mailchimp_list_id'] ); ?>" />
		<label class="description" for="mailchimp_list_id"><?php _e( 'MailChimp list ID', 'mailchimp' ); ?></label>
		<?php
	}

	function mailchimp_settings_field_label_class() {
		$options = mailchimp_get_theme_options();
		?>
		<input type="text" name="mailchimp_theme_options[label_class]" class="regular-text" id="mailchimp_label_class" value="<?php echo esc_attr( $options['label_class'] ); ?>" />
		<label class="description" for="mailchimp_label_class"><?php _e( 'Email label class', 'mailchimp' ); ?></label>
		<?php
	}

	function mailchimp_settings_field_button_class() {
		$options = mailchimp_get_theme_options();
		?>
		<input type="text" name="mailchimp_theme_options[button_class]" class="regular-text" id="mailchimp_button_class" value="<?php echo esc_attr( $options['button_class'] ); ?>" />
		<label class="description" for="mailchimp_button_class"><?php _e( 'Button class', 'mailchimp' ); ?></label>
		<?php
	}

	function mailchimp_settings_field_honeypot_class() {
		$options = mailchimp_get_theme_options();
		?>
		<input type="text" name="mailchimp_theme_options[honeypot]" class="regular-text" id="mailchimp_honeypot" value="<?php echo esc_attr( $options['honeypot'] ); ?>" />
		<label class="description" for="mailchimp_honeypot"><?php _e( 'Honeypot class', 'mailchimp' ); ?></label>
		<?php
	}



	/**
	 * Theme Option Defaults & Sanitization
	 * Each option field requires a default value under mailchimp_get_theme_options(), and an if statement under mailchimp_theme_options_validate();
	 */

	// Get the current options from the database.
	// If none are specified, use these defaults.
	function mailchimp_get_theme_options() {
		$saved = (array) get_option( 'mailchimp_theme_options' );
		$defaults = array(
			'mailchimp_api_key' => '',
			'mailchimp_list_id' => '',
			'label_class' => 'screen-reader',
			'button_class' => 'btn',
			'honeypot' => '',
		);

		$defaults = apply_filters( 'mailchimp_default_theme_options', $defaults );

		$options = wp_parse_args( $saved, $defaults );
		$options = array_intersect_key( $options, $defaults );

		return $options;
	}

	// Sanitize and validate updated theme options
	function mailchimp_theme_options_validate( $input ) {
		$output = array();

		if ( isset( $input['mailchimp_api_key'] ) && ! empty( $input['mailchimp_api_key'] ) )
			$output['mailchimp_api_key'] = wp_filter_nohtml_kses( $input['mailchimp_api_key'] );

		if ( isset( $input['mailchimp_list_id'] ) && ! empty( $input['mailchimp_list_id'] ) )
			$output['mailchimp_list_id'] = wp_filter_nohtml_kses( $input['mailchimp_list_id'] );

		if ( isset( $input['label_class'] ) && ! empty( $input['label_class'] ) )
			$output['label_class'] = wp_filter_nohtml_kses( $input['label_class'] );

		if ( isset( $input['button_class'] ) && ! empty( $input['button_class'] ) )
			$output['button_class'] = wp_filter_nohtml_kses( $input['button_class'] );

		if ( isset( $input['honeypot'] ) && ! empty( $input['honeypot'] ) )
			$output['honeypot'] = wp_filter_nohtml_kses( $input['honeypot'] );

		return apply_filters( 'mailchimp_theme_options_validate', $output, $input );
	}



	/**
	 * Theme Options Menu
	 * Each option field requires its own add_settings_field function.
	 */

	// Create theme options menu
	// The content that's rendered on the menu page.
	function mailchimp_theme_options_render_page() {
		?>
		<div class="wrap">
			<h2><?php _e( 'MailChimp Settings', 'mailchimp' ); ?></h2>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'mailchimp_options' );
					do_settings_sections( 'mailchimp_options' );
					submit_button();
				?>
			</form>
		</div>
		<?php
	}

	// Register the theme options page and its fields
	function mailchimp_theme_options_init() {

		// Register a setting and its sanitization callback
		// register_setting( $option_group, $option_name, $sanitize_callback );
		// $option_group - A settings group name.
		// $option_name - The name of an option to sanitize and save.
		// $sanitize_callback - A callback function that sanitizes the option's value.
		register_setting( 'mailchimp_options', 'mailchimp_theme_options', 'mailchimp_theme_options_validate' );


		// Register our settings field group
		// add_settings_section( $id, $title, $callback, $page );
		// $id - Unique identifier for the settings section
		// $title - Section title
		// $callback - // Section callback (we don't want anything)
		// $page - // Menu slug, used to uniquely identify the page. See mailchimp_theme_options_add_page().
		add_settings_section( 'mailchimp', '', '__return_false', 'mailchimp_options' );


		// Register our individual settings fields
		// add_settings_field( $id, $title, $callback, $page, $section );
		// $id - Unique identifier for the field.
		// $title - Setting field title.
		// $callback - Function that creates the field (from the Theme Option Fields section).
		// $page - The menu page on which to display this field.
		// $section - The section of the settings page in which to show the field.
		add_settings_field( 'mailchimp_api_key', __( 'API Key', 'mailchimp' ), 'mailchimp_settings_field_mailchimp_api_key', 'mailchimp_options', 'mailchimp' );
		add_settings_field( 'mailchimp_list_id', __( 'List ID', 'mailchimp' ), 'mailchimp_settings_field_mailchimp_list_id', 'mailchimp_options', 'mailchimp' );
		add_settings_field( 'label_class', __( 'Label Class', 'mailchimp' ), 'mailchimp_settings_field_label_class', 'mailchimp_options', 'mailchimp' );
		add_settings_field( 'button_class', __( 'Button Class', 'mailchimp' ), 'mailchimp_settings_field_button_class', 'mailchimp_options', 'mailchimp' );
		add_settings_field( 'honeypot', __( 'Honeypot', 'mailchimp' ), 'mailchimp_settings_field_honeypot_class', 'mailchimp_options', 'mailchimp' );

	}
	add_action( 'admin_init', 'mailchimp_theme_options_init' );

	// Add the theme options page to the admin menu
	// Use add_theme_page() to add under Appearance tab (default).
	// Use add_menu_page() to add as it's own tab.
	// Use add_submenu_page() to add to another tab.
	function mailchimp_theme_options_add_page() {

		// add_theme_page( $page_title, $menu_title, $capability, $menu_slug, $function );
		// add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function );
		// add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
		// $page_title - Name of page
		// $menu_title - Label in menu
		// $capability - Capability required
		// $menu_slug - Used to uniquely identify the page
		// $function - Function that renders the options page
		// $theme_page = add_theme_page( __( 'Theme Options', 'mailchimp' ), __( 'Theme Options', 'mailchimp' ), 'edit_theme_options', 'theme_options', 'mailchimp_theme_options_render_page' );

		// $theme_page = add_menu_page( __( 'Theme Options', 'mailchimp' ), __( 'Theme Options', 'mailchimp' ), 'edit_theme_options', 'theme_options', 'mailchimp_theme_options_render_page' );
		$theme_page = add_submenu_page( 'edit.php?post_type=gmt-mailchimp', __( 'Options', 'mailchimp' ), __( 'Options', 'mailchimp' ), 'edit_theme_options', 'mailchimp_options', 'mailchimp_theme_options_render_page' );
	}
	add_action( 'admin_menu', 'mailchimp_theme_options_add_page' );



	// Restrict access to the theme options page to admins
	function mailchimp_option_page_capability( $capability ) {
		return 'edit_theme_options';
	}
	add_filter( 'option_page_capability_mailchimp_options', 'mailchimp_option_page_capability' );
