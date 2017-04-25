<?php

/**
 * Plugin Name: GMT MailChimp
 * Plugin URI: https://github.com/cferdinandi/gmt-mailchimp/
 * GitHub Plugin URI: https://github.com/cferdinandi/gmt-mailchimp/
 * Description: MailChimp integration for WordPress
 * Version: 2.0.0
 * Author: Chris Ferdinandi
 * Author URI: http://gomakethings.com
 * License: MIT
 */

// Define constants
define( 'GMT_MAILCHIMP_VERSION', '2.0.0' );

// Includes
require_once( plugin_dir_path( __FILE__ ) . 'includes/wp-session-manager/wp-session-manager.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/helpers.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/updates.php' );

// Options
require_once( plugin_dir_path( __FILE__ ) . 'includes/options.php' );

// Custom Post Type
require_once( plugin_dir_path( __FILE__ ) . 'includes/cpt.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/metabox.php' );

// MailChimp integration
require_once( plugin_dir_path( __FILE__ ) . 'includes/mailchimp.php' );


/**
 * Check the plugin version and make updates if needed
 */
function gmt_mailchimp_check_version() {

	// Get plugin data
	$old_version = get_site_option( 'gmt_mailchimp_version' );

	// If plugin was just updated, make DB updates
	if ( empty( $old_version ) || version_compare( $old_version, '2.0.0', '<' ) ) {
		// Convert old data format to new one
		gmt_mailchimp_update_interest_groups_2_0_0();
	}

	// Update plugin to current version number
	if ( empty( $old_version ) || version_compare( $old_version, GMT_MAILCHIMP_VERSION, '<' ) ) {
		update_site_option( 'gmt_mailchimp_version', GMT_MAILCHIMP_VERSION );
	}

	update_site_option( 'gmt_mailchimp_version', GMT_MAILCHIMP_VERSION );

}
add_action( 'plugins_loaded', 'gmt_mailchimp_check_version' );


/**
 * Flush rewrite rules on activation and deactivation
 */
function gmt_mailchimp_flush_rewrites() {
	mailchimp_add_custom_post_type();
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
register_activation_hook( __FILE__, 'gmt_mailchimp_flush_rewrites' );