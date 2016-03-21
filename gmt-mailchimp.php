<?php

/**
 * Plugin Name: GMT MailChimp
 * Plugin URI: https://github.com/cferdinandi/gmt-mailchimp/
 * GitHub Plugin URI: https://github.com/cferdinandi/gmt-mailchimp/
 * Description: MailChimp integration for WordPress
 * Version: 1.1.0
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