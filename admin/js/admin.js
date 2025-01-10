jQuery(document).ready(function($) {
    // Default settings
    const defaultSettings = {
        included_types: ['post', 'page'],
        use_post_meta: false,
        excerpt_length: 150
    };

    // Handle reset to defaults button
    $('#reset-defaults').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm(wp_omni_rss.confirm_reset)) {
            return;
        }

        // Reset included types
        $('input[name="wp_omni_rss_settings[included_types][]"]').each(function() {
            $(this).prop('checked', defaultSettings.included_types.includes($(this).val()));
        });

        // Reset use post meta
        $('input[name="wp_omni_rss_settings[use_post_meta]"]').prop('checked', defaultSettings.use_post_meta);

        // Reset excerpt length
        $('input[name="wp_omni_rss_settings[excerpt_length]"]').val(defaultSettings.excerpt_length);
    });
}); 