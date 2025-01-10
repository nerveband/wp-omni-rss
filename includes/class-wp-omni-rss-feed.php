<?php
/**
 * Feed Manager Class
 */
class WP_Omni_RSS_Feed {
    /**
     * Constructor
     */
    public function __construct() {
        $this->setup_hooks();
    }

    /**
     * Setup hooks
     */
    private function setup_hooks() {
        // Core feed modifications
        add_filter('request', array($this, 'extend_feed_query'));
        add_filter('the_title_rss', array($this, 'modify_feed_title'));
        add_filter('the_content_feed', array($this, 'modify_feed_content'), 10, 2);
        add_filter('the_excerpt_rss', array($this, 'modify_feed_excerpt'));
        
        // Additional feed modifications
        add_filter('pre_get_posts', array($this, 'modify_feed_query'));
    }

    /**
     * Modify the main feed query
     *
     * @param WP_Query $query The WP_Query instance
     * @return WP_Query Modified query
     */
    public function modify_feed_query($query) {
        if (!$query->is_feed()) {
            return $query;
        }

        $options = get_option('wp_omni_rss_settings', array());
        $selected_types = isset($options['selected_types']) ? $options['selected_types'] : array('post', 'page');
        
        // Set post types
        $query->set('post_type', $selected_types);
        
        // Set posts per feed
        $query->set('posts_per_rss', 50); // You might want to make this configurable
        
        return $query;
    }

    /**
     * Extend the feed query to include all content types
     *
     * @param array $query_vars The query variables
     * @return array Modified query variables
     */
    public function extend_feed_query($query_vars) {
        if (isset($query_vars['feed']) && !isset($query_vars['post_type'])) {
            $options = get_option('wp_omni_rss_settings', array());
            $selected_types = isset($options['selected_types']) ? $options['selected_types'] : array('post', 'page');
            
            $query_vars['post_type'] = $selected_types;
        }
        return $query_vars;
    }

    /**
     * Modify feed title to include content type prefix
     *
     * @param string $title The post title
     * @return string Modified title
     */
    public function modify_feed_title($title) {
        if (!is_feed()) {
            return $title;
        }

        $options = get_option('wp_omni_rss_settings', array());
        $prefix_type = isset($options['prefix_type']) ? $options['prefix_type'] : 'all';
        
        if ($prefix_type === 'none') {
            return $title;
        }

        $post_type = get_post_type();
        $selected_types = isset($options['selected_types']) ? $options['selected_types'] : array('post', 'page');

        if ($prefix_type === 'all' || ($prefix_type === 'selected' && in_array($post_type, $selected_types))) {
            $post_type_obj = get_post_type_object($post_type);
            $type_label = $post_type_obj ? $post_type_obj->labels->singular_name : $post_type;
            return "[{$type_label}] {$title}";
        }

        return $title;
    }

    /**
     * Modify feed content to include meta information and handle excerpt length
     *
     * @param string $content The post content
     * @param string $feed_type The feed type
     * @return string Modified content
     */
    public function modify_feed_content($content, $feed_type) {
        if (!is_feed()) {
            return $content;
        }

        $options = get_option('wp_omni_rss_settings', array());
        $use_post_meta = isset($options['use_post_meta']) ? $options['use_post_meta'] : true;
        $excerpt_length = isset($options['excerpt_length']) ? (int) $options['excerpt_length'] : 0;

        // Add meta information if enabled
        if ($use_post_meta) {
            $post_id = get_the_ID();
            $change_description = get_post_meta($post_id, '_wp_omni_rss_description', true);
            $additional_meta = get_post_meta($post_id, '_wp_omni_rss_custom_meta', true);

            if (!empty($change_description) || !empty($additional_meta)) {
                $meta_html = '<div class="wp-omni-rss-meta">';
                
                if (!empty($change_description)) {
                    $meta_html .= sprintf(
                        '<p class="change-description"><strong>%s:</strong> %s</p>',
                        esc_html__('Changes', 'wp-omni-rss'),
                        esc_html($change_description)
                    );
                }
                
                if (!empty($additional_meta)) {
                    $meta_html .= sprintf(
                        '<p class="additional-meta"><strong>%s:</strong> %s</p>',
                        esc_html__('Additional Information', 'wp-omni-rss'),
                        esc_html($additional_meta)
                    );
                }
                
                $meta_html .= '</div>';
                $content = $meta_html . $content;
            }
        }

        // Handle excerpt length
        if ($excerpt_length > 0) {
            $content = wp_trim_words(wp_strip_all_tags($content), $excerpt_length, '...');
        }

        return $content;
    }

    /**
     * Modify feed excerpt
     *
     * @param string $excerpt The post excerpt
     * @return string Modified excerpt
     */
    public function modify_feed_excerpt($excerpt) {
        if (!is_feed()) {
            return $excerpt;
        }

        $options = get_option('wp_omni_rss_settings', array());
        $excerpt_length = isset($options['excerpt_length']) ? (int) $options['excerpt_length'] : 0;

        if ($excerpt_length > 0) {
            return wp_trim_words(wp_strip_all_tags($excerpt), $excerpt_length, '...');
        }

        return $excerpt;
    }
} 