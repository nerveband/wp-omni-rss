<?php
/**
 * The main plugin class
 */
class WP_Omni_RSS {
    /**
     * The single instance of the class.
     *
     * @var WP_Omni_RSS
     */
    private static $instance = null;

    /**
     * Main WP_Omni_RSS Instance.
     *
     * Ensures only one instance of WP_Omni_RSS is loaded or can be loaded.
     *
     * @return WP_Omni_RSS - Main instance.
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->setup_hooks();
        $this->initialize_components();
    }

    /**
     * Setup plugin hooks
     */
    private function setup_hooks() {
        // Add activation and deactivation hooks
        register_activation_hook(WP_OMNI_RSS_PATH . 'wp-omni-rss.php', array($this, 'activate'));
        register_deactivation_hook(WP_OMNI_RSS_PATH . 'wp-omni-rss.php', array($this, 'deactivate'));

        // Add action hooks
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
    }

    /**
     * Initialize plugin components
     */
    private function initialize_components() {
        // Load required files
        require_once WP_OMNI_RSS_PATH . 'includes/class-wp-omni-rss-feed.php';
        require_once WP_OMNI_RSS_PATH . 'includes/class-wp-omni-rss-settings.php';

        // Initialize components
        $this->feed = new WP_Omni_RSS_Feed();
        $this->settings = new WP_Omni_RSS_Settings();
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        $default_options = array(
            'included_types' => array('post', 'page'),
            'use_post_meta' => false,
            'excerpt_length' => 150
        );
        add_option('wp_omni_rss_settings', $default_options);

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Load plugin text domain
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'wp-omni-rss',
            false,
            dirname(plugin_basename(WP_OMNI_RSS_PATH . 'wp-omni-rss.php')) . '/languages/'
        );
    }
} 