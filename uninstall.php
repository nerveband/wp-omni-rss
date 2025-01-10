<?php
// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('wp_omni_rss_settings');

// Get plugin settings to check if post meta was used
$settings = get_option('wp_omni_rss_settings', array());
if (isset($settings['use_post_meta']) && $settings['use_post_meta']) {
    // Delete post meta only if it was enabled
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_wp_omni_rss_description'");
}

// Clear any cached data
wp_cache_flush(); 