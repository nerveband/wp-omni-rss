<?php
/**
 * Settings Handler Class
 */
class WP_Omni_RSS_Settings {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Only add meta boxes if post meta is enabled
        $options = get_option('wp_omni_rss_settings', array());
        if (isset($options['use_post_meta']) && $options['use_post_meta']) {
            add_action('add_meta_boxes', array($this, 'add_post_settings'));
            add_action('save_post', array($this, 'save_post_settings'));
        }
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ('settings_page_wp-omni-rss' !== $hook) {
            return;
        }

        wp_enqueue_style('wp-omni-rss-admin', plugin_dir_url(dirname(__FILE__)) . 'admin/css/admin.css', array(), WP_OMNI_RSS_VERSION);
        wp_enqueue_script('wp-omni-rss-admin', plugin_dir_url(dirname(__FILE__)) . 'admin/js/admin.js', array('jquery'), WP_OMNI_RSS_VERSION, true);
    }

    /**
     * Add settings page to admin menu
     */
    public function add_settings_page() {
        add_options_page(
            __('WP Omni RSS Settings', 'wp-omni-rss'),
            __('WP Omni RSS', 'wp-omni-rss'),
            'manage_options',
            'wp-omni-rss',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('wp_omni_rss_settings', 'wp_omni_rss_settings');

        add_settings_section(
            'wp_omni_rss_main',
            __('Main Settings', 'wp-omni-rss'),
            array($this, 'render_section_info'),
            'wp-omni-rss'
        );

        add_settings_field(
            'included_types',
            __('Included Content Types', 'wp-omni-rss'),
            array($this, 'render_included_types_field'),
            'wp-omni-rss',
            'wp_omni_rss_main'
        );

        add_settings_field(
            'use_post_meta',
            __('Enable Change Tracking', 'wp-omni-rss'),
            array($this, 'render_use_post_meta_field'),
            'wp-omni-rss',
            'wp_omni_rss_main'
        );

        add_settings_field(
            'excerpt_length',
            __('Excerpt Length', 'wp-omni-rss'),
            array($this, 'render_excerpt_length_field'),
            'wp-omni-rss',
            'wp_omni_rss_main'
        );
    }

    /**
     * Render the settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="wp-omni-rss-preview">
                <h2><?php esc_html_e('Feed Preview', 'wp-omni-rss'); ?></h2>
                <div class="preview-container">
                    <div class="preview-column">
                        <h3><?php esc_html_e('Default WordPress Feed', 'wp-omni-rss'); ?></h3>
                        <div class="preview-content default">
                            <div class="preview-item">
                                <h4>My First Blog Post</h4>
                                <div class="preview-excerpt">
                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit...
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="preview-column">
                        <h3><?php esc_html_e('Enhanced Omni RSS Feed', 'wp-omni-rss'); ?></h3>
                        <div class="preview-content enhanced">
                            <div class="preview-item">
                                <h4>[Blog Post] My First Blog Post</h4>
                                <div class="preview-meta">
                                    <em>Updated homepage link and fixed typos</em>
                                </div>
                                <div class="preview-excerpt">
                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit...
                                </div>
                            </div>
                            <div class="preview-item">
                                <h4>[Page] About Us</h4>
                                <div class="preview-meta">
                                    <em>Added new team member information</em>
                                </div>
                                <div class="preview-excerpt">
                                    Learn more about our company and mission...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form action="options.php" method="post">
                <?php
                settings_fields('wp_omni_rss_settings');
                do_settings_sections('wp-omni-rss');
                ?>
                <p class="submit">
                    <?php submit_button(__('Save Changes', 'wp-omni-rss'), 'primary', 'submit', false); ?>
                    <button type="button" class="button button-secondary" id="reset-defaults">
                        <?php esc_html_e('Reset to Defaults', 'wp-omni-rss'); ?>
                    </button>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Render section info
     */
    public function render_section_info() {
        echo '<p>' . esc_html__('Configure how your RSS feed behaves. The enhanced feed will include all selected content types and optional change tracking information.', 'wp-omni-rss') . '</p>';
    }

    /**
     * Render included types field
     */
    public function render_included_types_field() {
        $options = get_option('wp_omni_rss_settings');
        $post_types = get_post_types(array('public' => true), 'objects');
        $included_types = isset($options['included_types']) ? $options['included_types'] : array('post', 'page');

        foreach ($post_types as $post_type) {
            $checked = in_array($post_type->name, $included_types) ? 'checked' : '';
            ?>
            <label>
                <input type="checkbox" name="wp_omni_rss_settings[included_types][]" 
                       value="<?php echo esc_attr($post_type->name); ?>" <?php echo $checked; ?>>
                <?php echo esc_html($post_type->labels->name); ?>
            </label><br>
            <?php
        }
        ?>
        <p class="description">
            <?php esc_html_e('Select which content types should appear in your RSS feed.', 'wp-omni-rss'); ?>
        </p>
        <?php
    }

    /**
     * Render use post meta field
     */
    public function render_use_post_meta_field() {
        $options = get_option('wp_omni_rss_settings');
        $use_post_meta = isset($options['use_post_meta']) ? $options['use_post_meta'] : false;
        ?>
        <label>
            <input type="checkbox" name="wp_omni_rss_settings[use_post_meta]" 
                   value="1" <?php checked($use_post_meta); ?>>
            <?php esc_html_e('Enable change tracking in RSS feed', 'wp-omni-rss'); ?>
        </label>
        <p class="description">
            <?php esc_html_e('When enabled, you can add a description of your changes each time you update content. This helps your subscribers understand what changed and why.', 'wp-omni-rss'); ?>
        </p>
        <p class="description example">
            <?php esc_html_e('Example: "Updated pricing information" or "Added new team member bio"', 'wp-omni-rss'); ?>
        </p>
        <?php
    }

    /**
     * Render excerpt length field
     */
    public function render_excerpt_length_field() {
        $options = get_option('wp_omni_rss_settings');
        $excerpt_length = isset($options['excerpt_length']) ? $options['excerpt_length'] : 150;
        ?>
        <input type="number" name="wp_omni_rss_settings[excerpt_length]" 
               value="<?php echo esc_attr($excerpt_length); ?>" min="0" max="1000">
        <p class="description">
            <?php esc_html_e('Number of words to show in excerpts (0 for full content)', 'wp-omni-rss'); ?>
        </p>
        <?php
    }

    /**
     * Add post settings meta box
     */
    public function add_post_settings() {
        $post_types = get_post_types(array('public' => true));
        foreach ($post_types as $post_type) {
            add_meta_box(
                'wp_omni_rss_post_settings',
                __('RSS Change Tracking', 'wp-omni-rss'),
                array($this, 'render_post_settings'),
                $post_type,
                'side'
            );
        }
    }

    /**
     * Render post settings meta box
     *
     * @param WP_Post $post The post object
     */
    public function render_post_settings($post) {
        wp_nonce_field('wp_omni_rss_post_settings', 'wp_omni_rss_post_nonce');
        $description = get_post_meta($post->ID, '_wp_omni_rss_description', true);
        ?>
        <p>
            <label for="wp_omni_rss_description"><?php esc_html_e('What changed?', 'wp-omni-rss'); ?></label><br>
            <textarea id="wp_omni_rss_description" name="wp_omni_rss_description" rows="3" style="width: 100%;"><?php echo esc_textarea($description); ?></textarea>
            <p class="description">
                <?php esc_html_e('Briefly describe what you changed. This will be included in the RSS feed to help subscribers understand the update.', 'wp-omni-rss'); ?>
            </p>
        </p>
        <?php
    }

    /**
     * Save post settings
     *
     * @param int $post_id The post ID
     */
    public function save_post_settings($post_id) {
        if (!isset($_POST['wp_omni_rss_post_nonce']) || 
            !wp_verify_nonce($_POST['wp_omni_rss_post_nonce'], 'wp_omni_rss_post_settings')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['wp_omni_rss_description'])) {
            update_post_meta($post_id, '_wp_omni_rss_description', sanitize_textarea_field($_POST['wp_omni_rss_description']));
        }
    }
} 