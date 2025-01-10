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
        add_action('add_meta_boxes', array($this, 'add_post_settings'));
        add_action('save_post', array($this, 'save_post_settings'));
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
            'show_revisions',
            __('Show Revisions', 'wp-omni-rss'),
            array($this, 'render_show_revisions_field'),
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
            <form action="options.php" method="post">
                <?php
                settings_fields('wp_omni_rss_settings');
                do_settings_sections('wp-omni-rss');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render section info
     */
    public function render_section_info() {
        echo '<p>' . esc_html__('Configure how your RSS feed behaves.', 'wp-omni-rss') . '</p>';
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
    }

    /**
     * Render show revisions field
     */
    public function render_show_revisions_field() {
        $options = get_option('wp_omni_rss_settings');
        $show_revisions = isset($options['show_revisions']) ? $options['show_revisions'] : true;
        ?>
        <label>
            <input type="checkbox" name="wp_omni_rss_settings[show_revisions]" 
                   value="1" <?php checked($show_revisions); ?>>
            <?php esc_html_e('Include revision information in feed', 'wp-omni-rss'); ?>
        </label>
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
                __('RSS Settings', 'wp-omni-rss'),
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
        $enabled = get_post_meta($post->ID, '_wp_omni_rss_enabled', true);
        $description = get_post_meta($post->ID, '_wp_omni_rss_description', true);
        ?>
        <p>
            <label>
                <input type="checkbox" name="wp_omni_rss_enabled" value="1" <?php checked($enabled); ?>>
                <?php esc_html_e('Include in RSS feed', 'wp-omni-rss'); ?>
            </label>
        </p>
        <p>
            <label for="wp_omni_rss_description"><?php esc_html_e('Change Description:', 'wp-omni-rss'); ?></label><br>
            <textarea id="wp_omni_rss_description" name="wp_omni_rss_description" rows="3" style="width: 100%;"><?php echo esc_textarea($description); ?></textarea>
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

        $enabled = isset($_POST['wp_omni_rss_enabled']) ? 1 : 0;
        update_post_meta($post_id, '_wp_omni_rss_enabled', $enabled);

        if (isset($_POST['wp_omni_rss_description'])) {
            update_post_meta($post_id, '_wp_omni_rss_description', sanitize_textarea_field($_POST['wp_omni_rss_description']));
        }
    }
} 