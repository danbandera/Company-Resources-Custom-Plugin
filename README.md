# Company Resources

A WordPress plugin that adds a **Company Resources** custom post type, a resource category taxonomy, a set of custom fields, and a reusable **CTA Box** block.

It lets you publish case studies / project showcases and attach structured metadata (client name, industry, summary, project image, project URL) to each one, plus drop a styled call-to-action block anywhere in the editor.

## Features

- **Custom post type** (`company_resources`) with archive and REST API support.
- **Resource Category taxonomy** (`company_resource_tags`) — hierarchical, with an admin column on the post list table.
- **Custom fields meta box** with sanitized, REST-API-registered fields:
  - Client Name (text)
  - Industry (select)
  - Summary (textarea)
  - Project Image (media picker)
  - Project URL (url)
- **Content filter** that appends a "thank you" footer with the client/industry info on singular resource views.
- **Excerpt length control** — trims Company Resources excerpts to 25 words.
- **CTA Box block** (`simple-block/cta-box`) — title, text, and a button with an editable URL and color, built without a build step.

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher

## Installation

### Manual install (upload ZIP)

1. Download or zip the `company-resources` folder.
2. In WordPress admin go to **Plugins → Add New → Upload Plugin**.
3. Choose the ZIP file and click **Install Now**.
4. Activate the plugin. Rewrite rules flush automatically on activation.

### Manual install (FTP / file copy)

1. Copy the `company-resources` folder into `wp-content/plugins/`.
2. Go to **Plugins** in WordPress admin and activate **Company Resources**.
3. If permalinks show 404s on resource pages, go to **Settings → Permalinks** and click **Save Changes** to flush rewrite rules.

> **Note:** The plugin flushes rewrite rules automatically on activation and deactivation, so the `company-resources` and `resource-category` slugs work right away.

## Usage

### Creating a resource

1. In the admin sidebar, go to **Company Resources → Add New**.
2. Fill in the title and main content as usual.
3. In the **Custom Fields** meta box, enter the Client Name, pick an Industry, write a Summary, choose a Project Image, and set a Project URL.
4. Assign one or more **Resource Categories** from the sidebar.
5. Publish. The resource appears at `/company-resources/{slug}/` and in the archive at `/company-resources/`.

### Displaying fields in a theme

Two template helpers are provided:

```php
// Get a raw field value.
echo esc_html( company_resources_get_field( 'client_name' ) );

// Output an image field as markup.
echo company_resources_get_image( 'project_image', 'large' );
```

Both accept an optional second argument for a post ID and default to the current post in the loop.

### Using the CTA Box block

In any post or page, open the block inserter and search for **CTA Box**. Edit the title and text inline, and use the **Button settings** panel in the sidebar to set the button URL and color.

## Developer Hooks

The custom fields are filterable:

```php
// Change which post types show the meta box.
add_filter( 'company_resources_post_types', function ( $post_types ) {
    $post_types[] = 'page';
    return $post_types;
} );

// Add or modify fields. Supported types: text, select, textarea, image, url.
add_filter( 'company_resources_fields', function ( $fields ) {
    $fields['budget'] = [
        'label' => __( 'Budget', 'company-resources-plugin' ),
        'type'  => 'text',
    ];
    return $fields;
} );
```

All fields are registered as post meta with `show_in_rest => true`, so they are available in the REST API and the block editor.

## File Structure

```
company-resources/
├── company-resources.php              # Main plugin file (bootstrap, i18n, activation hooks)
├── includes/
│   ├── post-types.php                 # company_resources post type
│   ├── taxonomies.php                 # company_resource_tags taxonomy
│   ├── action-filters.php             # content footer, excerpt length, admin column
│   └── custom-fields.php              # Company_Resources_Custom_Fields class + helpers
└── blocks/
    └── simple-cta-block/
        ├── block.json
        ├── index.js                   # Editor + save (no build step)
        ├── simple-cta-block.php       # Block registration
        └── style.css                  # Front-end + editor styles
```

## License

GPL-2.0-or-later
