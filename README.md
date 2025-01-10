# WP Omni RSS

> ⚠️ **Alpha Release**: This plugin is currently in alpha stage. Use in production at your own risk.

A WordPress plugin that syndicates all your content - pages, posts, and custom types - in one unified RSS feed.

## Description

WP Omni RSS enhances WordPress's RSS functionality by including all content types in your site's feeds. Whether you're using standard posts, pages, or custom post types, everything can be included in a single, unified feed.

This plugin was inspired by Adam Newbold's essay ["Everything is a Web Page"](https://notes.neatnik.net/2025/01/everything-is-a-web-page), which argues that the distinction between static pages and blog posts is merely a mental model, and that all web content can be treated as syndication-worthy pages.

## Features

* Include any content type in your RSS feed
* Track and share content changes with your subscribers
* Add change descriptions to explain updates (e.g., "Updated pricing information", "Added new team member")
* Visual preview of enhanced feed format
* Configurable feed formatting
* Support for all public post types

## Installation

1. Upload the plugin files to the `/wp-content/plugins/wp-omni-rss` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->WP Omni RSS screen to configure the plugin

## Configuration

### Content Types
You can select which content types to include in the feed from the plugin settings page. By default, posts and pages are included.

### Change Tracking (Optional)
When enabled, you can add a description each time you update content. This helps your subscribers understand what changed and why. For example:
- "Updated pricing information for 2024"
- "Added new team member bio"
- "Fixed broken links in documentation"

The change descriptions will appear in your RSS feed along with the content, making it easy for subscribers to track important updates.

### Feed Preview
The settings page includes a live preview showing how your feed will look with and without the enhancements:
- Default WordPress feed
- Enhanced Omni RSS feed with content types and change tracking

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher

## Development

### Building from Source

```bash
# Clone the repository
git clone https://github.com/yourusername/wp-omni-rss.git
cd wp-omni-rss

# Install dependencies (if any)
composer install
```

### Running Tests

```bash
# Run PHP tests
composer test
```

## Changelog

### 1.0.0-alpha
* Initial alpha release
* Basic feed enhancement functionality
* Settings page with visual preview
* Optional change tracking features
* Reset to defaults functionality

## Credits

* Inspired by Adam Newbold's essay ["Everything is a Web Page"](https://notes.neatnik.net/2025/01/everything-is-a-web-page)
* Created by [Ashraf Ali](https://ashrafali.net)

## License

MIT License - see [LICENSE](LICENSE) for details. 