# PRC Taxonomies

Custom taxonomies for the PRC Platform, providing the foundational content classification system for pewresearch.org. This plugin manages research team associations, content formats, geographic regions, languages, and topic categorization.

## Overview

The PRC Taxonomies plugin registers and manages custom taxonomies that power content organization across Pew Research Center's digital publishing platform. The most significant feature is the **Research Teams URL Rewrite System**, which prefixes content URLs with the associated research team slug, creating a hierarchical URL structure that reflects PRC's organizational model.

### Why This Plugin Exists

Pew Research Center organizes content by research teams (e.g., Global Attitudes, Internet & Technology, Religion). This plugin:

1. **Creates a research team-based URL hierarchy** - Content appears under its team's URL namespace (e.g., `/internet/2025/01/15/article-slug/` instead of `/2025/01/15/article-slug/`)
2. **Provides content classification taxonomies** - Formats, regions, languages, and modes of analysis
3. **Integrates with SEO systems** - Primary term support for canonical URLs and structured data
4. **Supports multisite architecture** - Different behavior for primary vs. secondary sites

### Key Concepts

| Term               | Definition                                                                                         |
| ------------------ | -------------------------------------------------------------------------------------------------- |
| **Research Team**  | A PRC research unit (e.g., `internet`, `global`, `religion`) that appears as the first URL segment |
| **Primary Term**   | The designated "main" term when a post has multiple research teams; determines the URL prefix      |
| **Rewrite Rules**  | WordPress URL matching patterns that map team-prefixed URLs to the correct content                 |
| **Excluded Slugs** | URL segments that should never be treated as research team names (e.g., `wp-admin`, `feature`)     |

## Taxonomies

### Research Teams (`research-teams`)

The primary organizational taxonomy. Associates content with PRC research units and controls URL structure.

**Registered for post types:** `post`, `interactives`, `interactive`, `feature`, `fact-sheet`, `quiz`, `short-read`, `staff`, `dataset`, `stub`, `decoded`

**Key features:**

- Hierarchical (supports parent/child relationships)
- Visible in REST API
- Controls permalink structure via URL rewrites
- Supports primary term selection

### Formats (`formats`)

Categorizes content by publication type (report, short read, fact sheet, etc.).

**Registered for post types:** `post`, `short-read`, `fact-sheet`, `feature`, `press-release`, `quiz`, `decoded`, `dataset`, `prc_newsletter`, `collections`

**Key features:**

- Hierarchical
- Enforces format assignment for specific post types
- Integrated with sitemap generation
- Supports primary term selection

### Topic/Category (`category`)

WordPress's built-in category taxonomy, relabeled as "Topics" for editorial clarity.

**Key features:**

- Permalink base changed to `/topic/` (enforced on `init` only when the option differs)
- Legacy `/category/` → `/topic/` 301 is owned by `vip-config/server-redirects.php` in this plugin (loaded pre-WordPress via platform allowlist)
- Labels changed from "Category" to "Topic" in the block editor
- JavaScript filter modifies editor UI text
- The Tags (`post_tag`) document settings panel is removed in the block editor (PRC does not use tags)

### Regions & Countries (`regions-countries`)

Geographic classification for internationally-focused content.

**Registered for post types:** `post`, `feature`, `fact-sheet`, `short-read`, `quiz`, `stub`, `decoded`, `block_module`

**Key features:**

- Hierarchical (regions contain countries)
- Integrated with sitemap generation
- Supports primary term selection

### Languages (`languages`)

Tracks the language of content.

**Registered for post types:** `post`, `fact-sheets`, `fact-sheet`, `stub`, `decoded`, `short-read`

### Mode of Analysis (`mode-of-analysis`)

Classifies content by research methodology.

**Registered for post types:** `post`, `interactives`, `interactive`, `feature`, `fact-sheet`, `stub`, `decoded`

### Fund Pools (`_fund_pool`)

Private taxonomy for tracking grant funding sources.

**Key features:**

- Not public (internal use only)
- Stores funder metadata (URL, ID, budget)

### Decoded Category (`decoded-category`)

Subcategorization specific to the "Decoded" content series.

**Registered for post types:** `decoded`

---

## Research Teams URL Rewrite System

The Research Teams rewrite system is the core feature that creates pewresearch.org's distinctive URL hierarchy. Instead of standard WordPress permalinks, content URLs are prefixed with the primary research team slug.

### URL Structure Examples

| Standard WordPress URL       | Research Team Prefixed URL           |
| ---------------------------- | ------------------------------------ |
| `/2025/01/15/article-name/`  | `/internet/2025/01/15/article-name/` |
| `/feature/interactive-tool/` | `/global/feature/interactive-tool/`  |
| `/fact-sheet/country-data/`  | `/religion/fact-sheet/country-data/` |

### Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    Incoming Request                              │
│              /internet/2025/01/15/article-slug/                  │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                 WordPress Rewrite Rules                          │
│                                                                  │
│  Pattern: (?!excluded-slugs...)([^/]+)/YYYY/MM/DD/post-name/    │
│  Captures: research_team=$1, year=$2, monthnum=$3, day=$4...    │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│              validate_research_team_query_var()                  │
│                                                                  │
│  - Check if captured slug is a valid research team term         │
│  - If invalid, remove query var (404 will be handled normally)  │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    WordPress Query                               │
│                                                                  │
│  Load the post matching year/month/day/name                     │
└─────────────────────────────────────────────────────────────────┘
```

### Rewrite Configuration

The rewrite system uses a filterable configuration array. Each post type can define:

```php
$config = array(
    'post' => array(
        'slug_pattern'       => '([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)',
        'query_string'       => 'year=$matches[2]&monthnum=$matches[3]&day=$matches[4]&name=$matches[5]',
        'supports'           => array( 'iframe', 'embed', 'attachment' ),
        'attachment_pattern' => '[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)',
    ),
    'feature' => array(
        'slug_pattern'       => 'feature/([^/]+)',
        'query_string'       => 'post_type=feature&name=$matches[2]',
        'supports'           => array( 'iframe', 'embed', 'attachment' ),
        'attachment_pattern' => 'feature/(?!news-media-tracker)[^/]+/([^/]{5,})',
    ),
    // ... more post types
);
```

**Configuration options:**

| Option               | Description                                                                   |
| -------------------- | ----------------------------------------------------------------------------- |
| `slug_pattern`       | Regex pattern for the post type URL structure (without team prefix)           |
| `query_string`       | WordPress query vars to set (use `$matches[2]+` for captures after team)      |
| `supports`           | Array of additional URL suffixes to support (`iframe`, `embed`, `attachment`) |
| `attachment_pattern` | Custom regex for attachment URLs under this post type                         |
| `additional_rules`   | Array of extra pattern => query_string pairs for custom routes                |

### Excluded URL Slugs

The system automatically excludes certain URL segments from being treated as research team names:

- **Post type slugs** - `feature`, `fact-sheet`, `quiz`, etc.
- **Taxonomy slugs** - `topic`, `regions-countries`, etc.
- **WordPress core paths** - `wp-admin`, `wp-json`, `feed`, `embed`, etc.
- **Hardcoded exclusions** - `decoded`, `pew-research-center`

This prevents URL collisions where `/feature/some-article/` would incorrectly match as research team "feature".

### Primary Term Selection

When a post has multiple research teams, the **primary term** determines the URL prefix. This integrates with the `prc-schema-seo` plugin:

```php
// Get the primary research team for a post
$primary_term_id = \PRC\Platform\Taxonomies\get_primary_term_id( $post_id, 'research-teams' );
```

### Disabling Team Prefixes

Individual posts can opt out of research team URL prefixes:

```php
// Disable research team prefix for this post
update_post_meta( $post_id, 'disable_research_team_permalink', true );
```

---

## API Reference

### Filters

#### `prc_research_teams_rewrite_config`

Modify or extend the rewrite configuration for post types.

```php
add_filter( 'prc_research_teams_rewrite_config', function( $config ) {
    // Add support for a custom post type
    $config['custom-type'] = array(
        'slug_pattern' => 'custom/([^/]+)',
        'query_string' => 'post_type=custom-type&name=$matches[2]',
        'supports'     => array( 'iframe' ),
    );
    return $config;
} );
```

#### `prc_research_teams_excluded_url_slugs`

Add additional slugs to exclude from research team matching.

```php
add_filter( 'prc_research_teams_excluded_url_slugs', function( $excluded ) {
    $excluded[] = 'my-custom-slug';
    return $excluded;
} );
```

#### `prc_taxonomy_{taxonomy_name}_post_types`

Modify which post types a taxonomy is registered for.

```php
add_filter( 'prc_taxonomy_research-teams_post_types', function( $post_types ) {
    $post_types[] = 'my-custom-post-type';
    return $post_types;
} );
```

#### `prc_sitemap_supported_taxonomies`

Taxonomies that opt into sitemap generation.

#### `prc_schema_seo_primary_term_taxonomies`

Taxonomies that support primary term selection.

### Functions

#### `get_primary_term_id( int $post_id, string $taxonomy ): ?int`

Get the primary term ID for a post in a given taxonomy.

```php
use function PRC\Platform\Taxonomies\get_primary_term_id;

$primary_team_id = get_primary_term_id( $post_id, 'research-teams' );
```

**Parameters:**

- `$post_id` (int) - The post ID
- `$taxonomy` (string) - The taxonomy slug

**Returns:** Term ID if found, `false` otherwise. Requires `prc-schema-seo` plugin.

---

## Integration Examples

### Adding a New Post Type to Research Team Rewrites

```php
// In your plugin's initialization
add_filter( 'prc_research_teams_rewrite_config', function( $config ) {
    $config['my-post-type'] = array(
        'slug_pattern'       => 'my-type/([^/]+)',
        'query_string'       => 'post_type=my-post-type&name=$matches[2]',
        'supports'           => array( 'iframe', 'embed' ),
    );
    return $config;
} );

// Also register the post type for the taxonomy
add_filter( 'prc_taxonomy_research-teams_post_types', function( $post_types ) {
    $post_types[] = 'my-post-type';
    return $post_types;
} );
```

### Getting Content by Research Team

```php
$args = array(
    'post_type' => 'post',
    'tax_query' => array(
        array(
            'taxonomy' => 'research-teams',
            'field'    => 'slug',
            'terms'    => 'internet',
        ),
    ),
);
$posts = new WP_Query( $args );
```

---

## Common Pitfalls

### 1. Forgetting to Flush Rewrite Rules

After adding new rewrite configurations, you must flush rewrite rules:

```php
// Via WP-CLI
wp rewrite flush

// Or programmatically (on activation only)
flush_rewrite_rules();
```

### 2. URL Collisions with Post Type Slugs

If you register a post type with a slug that matches a research team name, URLs will conflict. The system excludes registered post type slugs automatically, but be mindful of naming.

### 3. Multisite Blog ID Checks

Research team rewrites are disabled on blog ID 1 (the primary site). This is intentional for PRC's multisite architecture.

### 4. Primary Term Not Set

If a post has multiple research teams but no primary term is set, the permalink will not include the team prefix. Always ensure primary terms are set for published content.

### 5. Cache Invalidation

Research team term slugs are cached for 1 hour. When creating or modifying terms, the cache is automatically flushed, but be aware of this when debugging.

---

## Development

### Building Assets

```bash
# Install dependencies
npm install

# Build production assets
npm run build

# Development with watch mode
npm run start
```

### File Structure

```
prc-taxonomies/
├── prc-taxonomies.php          # Main plugin file
├── package.json                # NPM configuration
├── src/
│   └── index.js               # Topic i18n + post_tag panel removal
├── build/                      # Compiled assets
└── includes/
    ├── class-bootstrap.php     # Plugin initialization
    ├── class-loader.php        # Hook loader utility
    ├── class-taxonomy-utils.php # Post tag UI hide + activity trail
    ├── class-research-teams.php
    ├── class-formats.php
    ├── class-topic-category.php
    ├── class-regions-countries.php
    ├── class-languages.php
    ├── class-mode-of-analysis.php
    ├── class-fund-pools.php
    ├── class-decoded-category.php
    └── utils.php               # Utility functions
```

### Dependencies

- **Required:** `prc-platform-core`
- **Optional:** `prc-schema-seo` (for primary term support)
- **Optional:** `facetwp` (for dataset archive rewrites)

---

## Changelog

### 1.0.0

- Initial release
- Extracted from `prc-platform-core`
- Research Teams URL rewrite system
- 8 custom taxonomies
- Primary term integration with `prc-schema-seo`
