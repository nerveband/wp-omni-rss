<?php
// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('wp_omni_rss_settings');

// Get all post types
$post_types = get_post_types(array('public' => true));

// Delete post meta for all posts
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key IN ('_wp_omni_rss_enabled', '_wp_omni_rss_description')");

// Clear any cached data
wp_cache_flush(); 