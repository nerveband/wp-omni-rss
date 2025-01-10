<?php
/**
 * Plugin Name: WP Omni RSS
 * Plugin URI: https://wordpress.org/plugins/wp-omni-rss
 * Description: Syndicate all your content - pages, posts, and custom types - in one unified RSS feed. Inspired by Adam Newbold's essay "Everything is a Web Page".
 * Version: 1.0.0
 * Author: Ashraf Ali
 * Author URI: https://ashrafali.net
 * Text Domain: wp-omni-rss
 * Domain Path: /languages
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Plugin version
define('WP_OMNI_RSS_VERSION', '1.0.0');

// Plugin directory path
define('WP_OMNI_RSS_PATH', plugin_dir_path(__FILE__));

// Plugin directory URL
define('WP_OMNI_RSS_URL', plugin_dir_url(__FILE__));

// Require the main plugin class
require_once WP_OMNI_RSS_PATH . 'includes/class-wp-omni-rss.php';

/**
 * Begins execution of the plugin.
 */
function run_wp_omni_rss() {
    $plugin = WP_Omni_RSS::get_instance();
}

// Start the plugin
run_wp_omni_rss(); 