jQuery(document).ready(function($) {
    // Cache DOM elements
    const $form = $('#wp-omni-rss-settings');
    const $prefixTypeRadios = $('input[name="wp_omni_rss_settings[prefix_type]"]');
    const $selectedTypesContainer = $('.prefix-types-selection');
    const $selectedTypesCheckboxes = $selectedTypesContainer.find('input[type="checkbox"]');
    const $excerptLengthInput = $('input[name="wp_omni_rss_settings[excerpt_length]"]');
    const $usePostMetaCheckbox = $('input[name="wp_omni_rss_settings[use_post_meta]"]');
    const $previewItems = $('.preview-item');
    const $feedUrl = $('#feed-url');

    // Initialize preview based on current settings
    updatePreview();

    // Event listeners
    $prefixTypeRadios.on('change', updatePreview);
    $selectedTypesCheckboxes.on('change', updatePreview);
    $excerptLengthInput.on('input', updatePreview);
    $usePostMetaCheckbox.on('change', updatePreview);

    // Show/hide selected types based on radio selection
    $prefixTypeRadios.on('change', function() {
        const selectedValue = $(this).val();
        if (selectedValue === 'selected') {
            $selectedTypesContainer.slideDown();
        } else {
            $selectedTypesContainer.slideUp();
        }
    });

    // Reset button handler
    $('#reset-defaults').on('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to reset all settings to defaults?')) {
            // Reset radio to default
            $prefixTypeRadios.filter('[value="all"]').prop('checked', true);
            $selectedTypesContainer.slideUp();
            
            // Reset checkboxes
            $selectedTypesCheckboxes.prop('checked', true);
            
            // Reset excerpt length
            $excerptLengthInput.val('0');
            
            // Reset post meta
            $usePostMetaCheckbox.prop('checked', true);
            
            // Update preview
            updatePreview();
        }
    });

    function updatePreview() {
        const prefixType = $prefixTypeRadios.filter(':checked').val();
        const excerptLength = parseInt($excerptLengthInput.val()) || 0;
        const usePostMeta = $usePostMetaCheckbox.prop('checked');
        
        $previewItems.each(function() {
            const $item = $(this);
            const $title = $item.find('h4');
            const $meta = $item.find('.preview-meta');
            const $excerpt = $item.find('.preview-excerpt');
            const contentType = $item.data('content-type');
            
            // Update title prefix
            let titleText = $title.data('original-title');
            if (prefixType === 'all' || 
                (prefixType === 'selected' && 
                 $selectedTypesCheckboxes.filter(`[value="${contentType}"]`).prop('checked'))) {
                titleText = `[${contentType}] ${titleText}`;
            }
            $title.text(titleText);
            
            // Show/hide meta information
            $meta.toggle(usePostMeta);
            
            // Update excerpt
            let excerptText = $excerpt.data('original-text');
            if (excerptLength > 0) {
                excerptText = excerptText.substring(0, excerptLength) + '...';
            }
            $excerpt.text(excerptText);
        });
    }

    // Make feed URL clickable
    $feedUrl.on('click', function() {
        const url = $(this).text();
        window.open(url, '_blank');
    }).css('cursor', 'pointer');

    // Add copy button for feed URL
    const $copyButton = $('<button>', {
        type: 'button',
        class: 'button button-secondary',
        text: 'Copy URL'
    }).insertAfter($feedUrl);

    $copyButton.on('click', function() {
        const tempInput = $('<input>');
        $('body').append(tempInput);
        tempInput.val($feedUrl.text()).select();
        document.execCommand('copy');
        tempInput.remove();
        
        const $this = $(this);
        const originalText = $this.text();
        $this.text('Copied!');
        setTimeout(() => {
            $this.text(originalText);
        }, 2000);
    });
}); 