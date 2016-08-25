<?php

/**
 * Plugin Name: GMT MailChimp
 * Plugin URI: https://github.com/cferdinandi/gmt-mailchimp/
 * GitHub Plugin URI: https://github.com/cferdinandi/gmt-mailchimp/
 * Description: MailChimp integration for WordPress
 * Version: 1.5.0
 * Author: Chris Ferdinandi
 * Author URI: http://gomakethings.com
 * License: MIT
 */


// Includes
require_once( plugin_dir_path( __FILE__ ) . 'includes/wp-session-manager/wp-session-manager.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/helpers.php' );

// Options
require_once( plugin_dir_path( __FILE__ ) . 'includes/options.php' );

// Custom Post Type
require_once( plugin_dir_path( __FILE__ ) . 'includes/cpt.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/metabox.php' );

// MailChimp integration
require_once( plugin_dir_path( __FILE__ ) . 'includes/mailchimp.php' );


// Flush rewrite rules on activation and deactivation
function gmt_mailchimp_flush_rewrites() {
	mailchimp_add_custom_post_type();
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
register_activation_hook( __FILE__, 'gmt_mailchimp_flush_rewrites' );