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
        
        // Add settings data for preview
        wp_localize_script('wp-omni-rss-admin', 'wpOmniRssSettings', array(
            'options' => get_option('wp_omni_rss_settings', array(
                'show_type_prefix' => true,
                'excerpt_length' => 0,
                'use_post_meta' => false
            ))
        ));
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
        register_setting(
            'wp_omni_rss_settings',
            'wp_omni_rss_settings',
            array(
                'sanitize_callback' => array($this, 'sanitize_settings'),
                'default' => array(
                    'prefix_type' => 'all',
                    'selected_types' => array('post', 'page'),
                    'excerpt_length' => 0,
                    'use_post_meta' => true
                )
            )
        );

        add_settings_section(
            'wp_omni_rss_main',
            __('Feed Settings', 'wp-omni-rss'),
            array($this, 'render_section_info'),
            'wp-omni-rss'
        );

        add_settings_field(
            'prefix_type',
            __('Content Type Prefix', 'wp-omni-rss'),
            array($this, 'render_prefix_type_field'),
            'wp-omni-rss',
            'wp_omni_rss_main'
        );

        add_settings_field(
            'included_content_types',
            __('Include Content Types', 'wp-omni-rss'),
            array($this, 'render_included_content_types_field'),
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

        add_settings_field(
            'use_post_meta',
            __('Change Tracking', 'wp-omni-rss'),
            array($this, 'render_use_post_meta_field'),
            'wp-omni-rss',
            'wp_omni_rss_main'
        );
    }

    /**
     * Sanitize settings before saving
     *
     * @param array $input The raw settings input
     * @return array Sanitized settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // Prefix type
        $sanitized['prefix_type'] = isset($input['prefix_type']) && 
            in_array($input['prefix_type'], array('none', 'all', 'selected')) ? 
            $input['prefix_type'] : 'all';

        // Selected types
        $post_types = array_keys(get_post_types(array('public' => true)));
        $sanitized['selected_types'] = isset($input['selected_types']) && is_array($input['selected_types']) ?
            array_intersect($input['selected_types'], $post_types) :
            array('post', 'page');

        // Excerpt length
        $sanitized['excerpt_length'] = isset($input['excerpt_length']) ?
            absint($input['excerpt_length']) : 0;

        // Use post meta
        $sanitized['use_post_meta'] = !empty($input['use_post_meta']);

        // Clear feed cache after saving settings
        delete_transient('feed_' . md5(get_bloginfo('url')));
        
        return $sanitized;
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
            <div class="wp-omni-rss-header">
                <img src="<?php echo esc_url(plugin_dir_url(dirname(__FILE__)) . 'admin/images/banner.png'); ?>" alt="WP Omni RSS">
            </div>
            
            <div class="wp-omni-rss-container">
                <div class="feed-url-container">
                    <h2><?php esc_html_e('Your RSS Feed', 'wp-omni-rss'); ?></h2>
                    <p class="description"><?php esc_html_e('Share this URL with your subscribers:', 'wp-omni-rss'); ?></p>
                    <code id="feed-url"><?php echo esc_url(get_feed_link('rss2')); ?></code>
                </div>

                <form action="options.php" method="post">
                    <?php
                    settings_fields('wp_omni_rss_settings');
                    do_settings_sections('wp-omni-rss');
                    ?>
                    
                    <div class="wp-omni-rss-preview">
                        <h2><?php esc_html_e('Feed Preview', 'wp-omni-rss'); ?></h2>
                        <div class="preview-container">
                            <div class="preview-column">
                                <h3><?php esc_html_e('Default WordPress Feed', 'wp-omni-rss'); ?></h3>
                                <div class="preview-content default">
                                    <div class="preview-item" data-content-type="post">
                                        <h4 data-original-title="My First Blog Post">My First Blog Post</h4>
                                        <div class="preview-excerpt" data-original-text="Lorem ipsum dolor sit amet, consectetur adipiscing elit...">
                                            Lorem ipsum dolor sit amet, consectetur adipiscing elit...
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="preview-column">
                                <h3><?php esc_html_e('Enhanced Omni RSS Feed', 'wp-omni-rss'); ?></h3>
                                <div class="preview-content enhanced">
                                    <div class="preview-item" data-content-type="Blog Post">
                                        <h4 data-original-title="My First Blog Post">My First Blog Post</h4>
                                        <div class="preview-meta">
                                            <em>Updated homepage link and fixed typos</em>
                                            <div class="additional-meta">Author Notes: This post introduces our new website design and explains the key features.</div>
                                        </div>
                                        <div class="preview-excerpt" data-original-text="Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.">
                                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                                        </div>
                                    </div>
                                    <div class="preview-item" data-content-type="Page">
                                        <h4 data-original-title="About Us">About Us</h4>
                                        <div class="preview-meta">
                                            <em>Added new team member information</em>
                                            <div class="additional-meta">References: Company history, Team bios</div>
                                        </div>
                                        <div class="preview-excerpt" data-original-text="Learn more about our company and mission. We are dedicated to providing the best service to our customers.">
                                            Learn more about our company and mission. We are dedicated to providing the best service to our customers.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="submit">
                        <?php submit_button(__('Save Changes', 'wp-omni-rss'), 'primary', 'submit', false); ?>
                        <button type="button" class="button button-secondary" id="reset-defaults">
                            <?php esc_html_e('Reset to Defaults', 'wp-omni-rss'); ?>
                        </button>
                    </p>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Render included content types field
     */
    public function render_included_content_types_field() {
        $options = get_option('wp_omni_rss_settings');
        $selected_types = isset($options['selected_types']) ? $options['selected_types'] : array('post');
        ?>
        <fieldset>
            <p class="description">
                <?php esc_html_e('Select which content types to include in your RSS feed. By default, only posts are included.', 'wp-omni-rss'); ?>
            </p>
            <?php
            $post_types = get_post_types(array('public' => true), 'objects');
            foreach ($post_types as $type) {
                ?>
                <label style="display: block; margin-bottom: 8px;">
                    <input type="checkbox" 
                           name="wp_omni_rss_settings[selected_types][]" 
                           value="<?php echo esc_attr($type->name); ?>"
                           <?php checked(in_array($type->name, $selected_types)); ?>>
                    <span><?php echo esc_html($type->labels->name); ?></span>
                    <span class="description">
                        (<?php echo esc_html($type->labels->singular_name); ?>)
                    </span>
                </label>
                <?php
            }
            ?>
        </fieldset>
        <?php
    }

    /**
     * Render prefix type field
     */
    public function render_prefix_type_field() {
        $options = get_option('wp_omni_rss_settings');
        $prefix_type = isset($options['prefix_type']) ? $options['prefix_type'] : 'all';
        ?>
        <fieldset>
            <label>
                <input type="radio" name="wp_omni_rss_settings[prefix_type]" value="none" <?php checked($prefix_type, 'none'); ?>>
                <?php esc_html_e('No prefix', 'wp-omni-rss'); ?>
            </label>
            <br>
            <label>
                <input type="radio" name="wp_omni_rss_settings[prefix_type]" value="all" <?php checked($prefix_type, 'all'); ?>>
                <?php esc_html_e('Show prefix for all content', 'wp-omni-rss'); ?>
            </label>
            <br>
            <label>
                <input type="radio" name="wp_omni_rss_settings[prefix_type]" value="selected" <?php checked($prefix_type, 'selected'); ?>>
                <?php esc_html_e('Show prefix only for selected content types', 'wp-omni-rss'); ?>
            </label>
        </fieldset>
        <?php
    }

    /**
     * Render excerpt length field
     */
    public function render_excerpt_length_field() {
        $options = get_option('wp_omni_rss_settings');
        $excerpt_length = isset($options['excerpt_length']) ? $options['excerpt_length'] : 0;
        ?>
        <input type="number" name="wp_omni_rss_settings[excerpt_length]" value="<?php echo esc_attr($excerpt_length); ?>" min="0" step="1">
        <p class="description">
            <?php esc_html_e('Enter 0 for full content (recommended) or specify the number of words for excerpts.', 'wp-omni-rss'); ?>
        </p>
        <?php
    }

    /**
     * Render use post meta field
     */
    public function render_use_post_meta_field() {
        $options = get_option('wp_omni_rss_settings');
        $use_post_meta = isset($options['use_post_meta']) ? $options['use_post_meta'] : true;
        ?>
        <label>
            <input type="checkbox" name="wp_omni_rss_settings[use_post_meta]" value="1" <?php checked($use_post_meta); ?>>
            <?php esc_html_e('Enable change tracking and post meta in RSS feed', 'wp-omni-rss'); ?>
        </label>
        <p class="description">
            <?php esc_html_e('When enabled, you can add a description of your changes each time you update content. This helps your subscribers understand what changed and why.', 'wp-omni-rss'); ?>
        </p>
        <?php
    }

    /**
     * Render section info
     */
    public function render_section_info() {
        ?>
        <p>
            <?php esc_html_e('Configure how your content appears in the RSS feed. Changes will be reflected in the preview below.', 'wp-omni-rss'); ?>
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
        $custom_meta = get_post_meta($post->ID, '_wp_omni_rss_custom_meta', true);
        ?>
        <div class="wp-omni-rss-post-settings">
            <p>
                <label for="wp_omni_rss_description"><?php esc_html_e('What changed?', 'wp-omni-rss'); ?></label><br>
                <textarea id="wp_omni_rss_description" name="wp_omni_rss_description" rows="3" style="width: 100%;"><?php echo esc_textarea($description); ?></textarea>
                <p class="description">
                    <?php esc_html_e('Briefly describe what you changed. This will be included in the RSS feed to help subscribers understand the update.', 'wp-omni-rss'); ?>
                </p>
            </p>
            
            <p>
                <label for="wp_omni_rss_custom_meta"><?php esc_html_e('Additional Meta Information', 'wp-omni-rss'); ?></label><br>
                <textarea id="wp_omni_rss_custom_meta" name="wp_omni_rss_custom_meta" rows="3" style="width: 100%;"><?php echo esc_textarea($custom_meta); ?></textarea>
                <p class="description">
                    <?php esc_html_e('Optional metadata to include with this content (e.g., author notes, references, related content).', 'wp-omni-rss'); ?>
                </p>
            </p>
        </div>
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

        if (isset($_POST['wp_omni_rss_custom_meta'])) {
            update_post_meta($post_id, '_wp_omni_rss_custom_meta', sanitize_textarea_field($_POST['wp_omni_rss_custom_meta']));
        }
    }
} 