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
        add_filter('request', array($this, 'extend_feed_query'));
        add_filter('the_content_feed', array($this, 'enhance_feed_item'), 10, 2);
        add_filter('the_excerpt_rss', array($this, 'add_change_context'));
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
            $included_types = isset($options['included_types']) ? $options['included_types'] : array('post', 'page');
            
            $query_vars['post_type'] = $included_types;
        }
        return $query_vars;
    }

    /**
     * Enhance feed item content
     *
     * @param string $content The post content
     * @param int    $post_id The post ID
     * @return string Modified content
     */
    public function enhance_feed_item($content, $post_id) {
        $post = get_post($post_id);
        $post_type_obj = get_post_type_object($post->post_type);
        $type_label = $post_type_obj ? $post_type_obj->labels->singular_name : $post->post_type;

        $change_note = get_post_meta($post_id, '_wp_omni_rss_description', true);
        if (!empty($change_note)) {
            $options = get_option('wp_omni_rss_settings', array());
            $format = isset($options['change_format']) ? $options['change_format'] : '[{type}] {title} - {change_note}';
            
            $header = str_replace(
                array('{type}', '{title}', '{change_note}'),
                array($type_label, $post->post_title, $change_note),
                $format
            );
            
            $content = '<p><strong>' . esc_html($header) . '</strong></p>' . $content;
        }

        return $content;
    }

    /**
     * Add change context to feed excerpt
     *
     * @param string $excerpt The post excerpt
     * @return string Modified excerpt
     */
    public function add_change_context($excerpt) {
        global $post;
        
        $change_note = get_post_meta($post->ID, '_wp_omni_rss_description', true);
        if (!empty($change_note)) {
            $excerpt = '<p><em>' . esc_html($change_note) . '</em></p>' . $excerpt;
        }
        
        return $excerpt;
    }
} 