<?php
/**
 * Class WP_Omni_RSS_Test
 *
 * @package WP_Omni_RSS
 */

/**
 * Test case for the WP Omni RSS plugin.
 */
class WP_Omni_RSS_Test extends WP_UnitTestCase {

    /**
     * Test that the plugin is loaded.
     */
    public function test_plugin_loaded() {
        $this->assertTrue(class_exists('WP_Omni_RSS'));
    }

    /**
     * Test that the plugin instance is created.
     */
    public function test_plugin_instance() {
        $instance = WP_Omni_RSS::get_instance();
        $this->assertInstanceOf('WP_Omni_RSS', $instance);
    }

    /**
     * Test that default options are set on activation.
     */
    public function test_default_options() {
        $instance = WP_Omni_RSS::get_instance();
        $instance->activate();

        $options = get_option('wp_omni_rss_settings');
        $this->assertIsArray($options);
        $this->assertArrayHasKey('included_types', $options);
        $this->assertArrayHasKey('use_post_meta', $options);
        $this->assertContains('post', $options['included_types']);
        $this->assertContains('page', $options['included_types']);
    }

    /**
     * Test that the feed query is modified when the plugin is active.
     */
    public function test_feed_query_modification() {
        $feed = new WP_Omni_RSS_Feed();
        $query_vars = array('feed' => 'rss2');
        
        $modified_vars = $feed->extend_feed_query($query_vars);
        
        $this->assertArrayHasKey('post_type', $modified_vars);
        $this->assertIsArray($modified_vars['post_type']);
    }

    /**
     * Test that feed content is enhanced with meta information when enabled.
     */
    public function test_feed_content_enhancement() {
        $feed = new WP_Omni_RSS_Feed();
        $post_id = $this->factory->post->create(array(
            'post_title' => 'Test Post',
            'post_content' => 'Test content'
        ));
        
        update_post_meta($post_id, '_wp_omni_rss_description', 'Test change description');
        
        $enhanced_content = $feed->enhance_feed_item('Original content', $post_id);
        
        $this->assertStringContainsString('Test change description', $enhanced_content);
    }
} 