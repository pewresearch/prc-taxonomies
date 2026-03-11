<?php
/**
 * Research Teams Taxonomy
 *
 * @package PRC\Platform\Taxonomies
 */

namespace PRC\Platform\Taxonomies;

use WP_Taxonomy;
use WP_Error;

/**
 * Research Teams Taxonomy
 *
 * Provides research team taxonomy and dynamic URL rewrites for post types
 * that support research team prefixed permalinks.
 *
 * @package PRC\Platform\Taxonomies
 */
class Research_Teams {
	/**
	 * Taxonomy name.
	 *
	 * @var string
	 */
	protected static $taxonomy = 'research-teams';

	/**
	 * Cache key for term slugs.
	 *
	 * @var string
	 */
	const TERM_SLUGS_CACHE_KEY = 'prc_research_team_slugs';

	/**
	 * Term slugs to exclude from rewrite rules.
	 *
	 * @var array
	 */
	const EXCLUDED_TERM_SLUGS = array( 'decoded', 'pew-research-center' );

	/**
	 * Constructor.
	 *
	 * @param Loader $loader The loader.
	 */
	public function __construct( $loader ) {
		$loader->add_action( 'init', $this, 'register' );
		$loader->add_filter( 'post_link', $this, 'modify_post_permalinks', 10, 2 );
		$loader->add_filter( 'post_type_link', $this, 'modify_post_permalinks', 10, 2 );
		$loader->add_filter( 'rewrite_rules_array', $this, 'add_rewrite_rules', 10, 1 );
		$loader->add_filter( 'facetwp_preload_url_vars', $this, 'rewrite_datasets_archives', 10, 1 );
		$loader->add_filter( 'prc_schema_seo_primary_term_taxonomies', $this, 'opt_into_primary_term_support', 20, 1 );
		// Ensure canonical URLs also get research-teams URL rewrites.
		$loader->add_filter( 'prc_schema_seo_canonical_url', $this, 'modify_canonical_url', 10, 2 );

		// Register query var for research team validation.
		$loader->add_filter( 'prc_platform_rewrite_query_vars', $this, 'register_query_var' );

		// Validate research team query var on request.
		$loader->add_action( 'parse_request', $this, 'validate_research_team_query_var' );

		// Cache invalidation when terms change.
		$loader->add_action( 'created_' . self::$taxonomy, $this, 'flush_term_cache' );
		$loader->add_action( 'edited_' . self::$taxonomy, $this, 'flush_term_cache' );
		$loader->add_action( 'delete_' . self::$taxonomy, $this, 'flush_term_cache' );
	}

	/**
	 * Get the rewrite configuration for post types.
	 *
	 * This method returns a filterable configuration array that defines how
	 * research team prefixed URLs are handled for each post type.
	 *
	 * Configuration options:
	 * - slug_pattern: Regex pattern for the post type slug (without term prefix)
	 * - query_string: WordPress query string (use $matches[2]+ for captures after term)
	 * - supports: Array of features to support ('iframe', 'embed', 'attachment')
	 * - attachment_pattern: Optional custom pattern for attachment URLs
	 * - additional_rules: Array of additional pattern => query_string pairs
	 *
	 * @return array The rewrite configuration.
	 */
	public function get_rewrite_config() {
		// Core post types with research team rewrite support.
		// Quiz and dataset configs are provided by their respective plugins
		// via the prc_research_teams_rewrite_config filter.
		$config = array(
			'post'       => array(
				'slug_pattern'       => '([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)',
				'query_string'       => 'year=$matches[2]&monthnum=$matches[3]&day=$matches[4]&name=$matches[5]',
				'supports'           => array( 'iframe', 'embed', 'attachment', 'version' ),
				'attachment_pattern' => '[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)',
			),
			'feature'    => array(
				'slug_pattern'       => 'feature/([^/]+)',
				'query_string'       => 'post_type=feature&name=$matches[2]',
				'supports'           => array( 'iframe', 'embed', 'attachment', 'version' ),
				'attachment_pattern' => 'feature/(?!news-media-tracker)[^/]+/([^/]{5,})',
			),
			'fact-sheet' => array(
				'slug_pattern'       => 'fact-sheet/([^/]+)',
				'query_string'       => 'post_type=fact-sheet&name=$matches[2]',
				'supports'           => array( 'iframe', 'embed', 'attachment', 'version' ),
				'attachment_pattern' => 'fact-sheet/[^/]+/([^/]+)',
			),
		);

		/**
		 * Filter the research teams rewrite configuration.
		 *
		 * Allows plugins to add or modify rewrite rules for their post types.
		 *
		 * @param array $config The rewrite configuration array.
		 */
		return apply_filters( 'prc_research_teams_rewrite_config', $config );
	}

	/**
	 * Get the list of post types that support research team rewrites.
	 *
	 * @return array Array of post type names.
	 */
	public function get_rewrite_enabled_post_types() {
		return array_keys( $this->get_rewrite_config() );
	}

	/**
	 * Register the research_team query var.
	 *
	 * @hook prc_platform_rewrite_query_vars
	 *
	 * @param array $query_vars The query vars.
	 * @return array The modified query vars.
	 */
	public function register_query_var( $query_vars ) {
		$query_vars[] = 'research_team';
		return $query_vars;
	}

	/**
	 * Validate the research_team query var against valid term slugs.
	 *
	 * If the captured slug is not a valid research team term, the query var
	 * is removed and WordPress will handle the 404 naturally.
	 *
	 * @hook parse_request
	 *
	 * @param \WP $wp The WordPress environment instance.
	 */
	public function validate_research_team_query_var( $wp ) {
		if ( empty( $wp->query_vars['research_team'] ) ) {
			return;
		}

		$team_slug   = $wp->query_vars['research_team'];
		$valid_slugs = $this->get_cached_term_slugs();

		// Check if slug is valid and not excluded.
		if ( ! in_array( $team_slug, $valid_slugs, true ) || in_array( $team_slug, self::EXCLUDED_TERM_SLUGS, true ) ) {
			unset( $wp->query_vars['research_team'] );
			// WordPress will handle the 404 naturally since the URL won't match.
		}
	}

	/**
	 * Get cached term slugs for validation.
	 *
	 * @return array Array of term slugs.
	 */
	private function get_cached_term_slugs() {
		$slugs = wp_cache_get( self::TERM_SLUGS_CACHE_KEY );

		if ( false === $slugs ) {
			$terms = get_terms(
				array(
					'taxonomy'   => self::$taxonomy,
					'hide_empty' => false,
					'fields'     => 'slugs',
				)
			);
			$slugs = is_array( $terms ) ? $terms : array();
			wp_cache_set( self::TERM_SLUGS_CACHE_KEY, $slugs, '', HOUR_IN_SECONDS );
		}

		return $slugs;
	}

	/**
	 * Flush the term slugs cache when terms are modified.
	 *
	 * @hook created_research-teams
	 * @hook edited_research-teams
	 * @hook delete_research-teams
	 */
	public function flush_term_cache() {
		wp_cache_delete( self::TERM_SLUGS_CACHE_KEY );
	}

	/**
	 * Register the taxonomy.
	 *
	 * @return WP_Taxonomy|WP_Error
	 */
	public function register() {
		$taxonomy_name = self::$taxonomy;

		$labels = array(
			'name'                       => _x( 'Research Teams', 'Taxonomy General Name', 'prc-taxonomies' ),
			'singular_name'              => _x( 'Research Team', 'Taxonomy Singular Name', 'prc-taxonomies' ),
			'menu_name'                  => __( 'Research Teams', 'prc-taxonomies' ),
			'all_items'                  => __( 'All Research Teams', 'prc-taxonomies' ),
			'parent_item'                => __( 'Parent Research Team', 'prc-taxonomies' ),
			'parent_item_colon'          => __( 'Parent Research Team:', 'prc-taxonomies' ),
			'new_item_name'              => __( 'New Research Team', 'prc-taxonomies' ),
			'add_new_item'               => __( 'Add New Research Team', 'prc-taxonomies' ),
			'edit_item'                  => __( 'Edit Research Teams', 'prc-taxonomies' ),
			'update_item'                => __( 'Update Research Team', 'prc-taxonomies' ),
			'view_item'                  => __( 'View Research Team', 'prc-taxonomies' ),
			'separate_items_with_commas' => __( 'Separate projects with commas', 'prc-taxonomies' ),
			'add_or_remove_items'        => __( 'Add or remove projects', 'prc-taxonomies' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'prc-taxonomies' ),
			'popular_items'              => __( 'Popular Research Teams', 'prc-taxonomies' ),
			'search_items'               => __( 'Search Research Teams', 'prc-taxonomies' ),
			'not_found'                  => __( 'Not Found', 'prc-taxonomies' ),
			'no_terms'                   => __( 'No Research Teams', 'prc-taxonomies' ),
			'items_list'                 => __( 'Research Teams list', 'prc-taxonomies' ),
			'items_list_navigation'      => __( 'Research Teams list navigation', 'prc-taxonomies' ),
			'item_link'                  => __( 'Research Team Link', 'prc-taxonomies' ),
			'item_link_description'      => __( 'A link to the Research Team.', 'prc-taxonomies' ),
		);
		$args   = array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
			'show_in_rest'      => true,
		);

		// @TODO: Add filters into modules to signal support for research teams taxonomy.
		$post_types = apply_filters(
			"prc_taxonomy_{$taxonomy_name}_post_types",
			array(
				'post',
				'interactives',
				'interactive',
				'feature',
				'fact-sheet',
				'fact-sheets',
				'quiz',
				'short-read',
				'staff',
				'dataset',
				'stub',
				'decoded',
			)
		);

		return register_taxonomy( self::$taxonomy, $post_types, $args );
	}

	/**
	 * Get slugs to exclude from the research team wildcard pattern.
	 *
	 * Dynamically collects rewrite slugs from registered post types and taxonomies
	 * to prevent URL collisions where a post type or taxonomy slug could be mistaken
	 * for a research team.
	 *
	 * @return array Array of regex-escaped slugs to exclude.
	 */
	private function get_excluded_url_slugs() {
		$excluded = array();

		// Get all public post types with their rewrite slugs.
		$post_types = get_post_types( array( 'public' => true ), 'objects' );

		foreach ( $post_types as $post_type ) {
			if ( ! empty( $post_type->rewrite ) && ! empty( $post_type->rewrite['slug'] ) ) {
				$excluded[] = $post_type->rewrite['slug'];
			}
			// Also add the post type name itself as it could be used in URLs.
			$excluded[] = $post_type->name;
		}

		// Add WordPress core paths that should never match as research teams.
		$wp_reserved = array(
			'wp-admin',
			'wp-content',
			'wp-includes',
			'wp-json',
			'feed',
			'embed',
			'trackback',
			'page',
			'comments',
			'attachment',
			'author',
			'search',
		);

		// Add public taxonomy archive slugs.
		$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
		foreach ( $taxonomies as $taxonomy ) {
			if ( ! empty( $taxonomy->rewrite ) && ! empty( $taxonomy->rewrite['slug'] ) ) {
				$excluded[] = $taxonomy->rewrite['slug'];
			}
		}

		$excluded = array_merge( $excluded, $wp_reserved, self::EXCLUDED_TERM_SLUGS );
		$excluded = array_unique( array_filter( $excluded ) );

		/**
		 * Filter the slugs to exclude from research team URL matching.
		 *
		 * @param array $excluded Array of slugs to exclude.
		 */
		$excluded = apply_filters( 'prc_research_teams_excluded_url_slugs', $excluded );

		// Escape for regex use.
		return array_map(
			function ( $slug ) {
				return preg_quote( $slug, '/' );
			},
			$excluded
		);
	}

	/**
	 * Build rewrite rules dynamically from configuration.
	 *
	 * Uses a negative lookahead pattern to exclude known post type and taxonomy
	 * slugs from matching as research teams. This prevents URL collisions where
	 * URLs like /short-reads/2025/01/01/post-name/ would incorrectly match the
	 * research team pattern.
	 *
	 * @hook rewrite_rules_array
	 *
	 * @param array $rules The rewrite rules.
	 * @return array
	 */
	public function add_rewrite_rules( $rules ) {
		$new_rules      = array();
		$config         = $this->get_rewrite_config();
		$excluded_slugs = $this->get_excluded_url_slugs();

		// Build negative lookahead pattern to exclude known slugs.
		// Pattern: (?!slug1|slug2|...)([^/]+) - matches any segment NOT starting with excluded slugs.
		$term_pattern = '(?!' . implode( '|', $excluded_slugs ) . ')([^/]+)';

		foreach ( $config as $post_type => $settings ) {
			$slug_pattern = $settings['slug_pattern'];
			$query_string = $settings['query_string'];
			$supports     = $settings['supports'] ?? array();

			// Base rule: {team}/{post-type-pattern}.
			$new_rules[ $term_pattern . '/' . $slug_pattern . '/?$' ] =
				'index.php?research_team=$matches[1]&' . $query_string;

			// Auto-add iframe/embed rules if supported.
			if ( in_array( 'iframe', $supports, true ) ) {
				$new_rules[ $term_pattern . '/' . $slug_pattern . '/iframe/?$' ] =
					'index.php?research_team=$matches[1]&' . $query_string . '&iframe=true';
			}
			if ( in_array( 'embed', $supports, true ) ) {
				$new_rules[ $term_pattern . '/' . $slug_pattern . '/embed/?$' ] =
					'index.php?research_team=$matches[1]&' . $query_string . '&iframe=true';
			}

			// Add version rule if supported.
			if ( in_array( 'version', $supports, true ) ) {
				$new_rules[ $term_pattern . '/' . $slug_pattern . '/version/([a-zA-Z0-9-]+)/?$' ] =
					'index.php?research_team=$matches[1]&' . $query_string . '&version=$matches[6]';
			}

			// Attachment rule if pattern is defined.
			if ( in_array( 'attachment', $supports, true ) && ! empty( $settings['attachment_pattern'] ) ) {
				$new_rules[ $term_pattern . '/' . $settings['attachment_pattern'] . '/?$' ] =
					'index.php?attachment=$matches[2]';
			}

			// Additional custom rules (for quiz groups, dataset archives, etc.).
			foreach ( $settings['additional_rules'] ?? array() as $pattern => $query ) {
				$new_rules[ $term_pattern . '/' . $pattern . '/?$' ] =
					'index.php?research_team=$matches[1]&' . $query;
			}
		}

		return array_merge( $new_rules, $rules );
	}

	/**
	 * Rewrites pewresearch.org/{research-team-name}/datasets to preload the selected facet
	 *
	 * @hook facetwp_preload_url_vars
	 * @param array $url_vars The URL variables.
	 * @return array
	 */
	public function rewrite_datasets_archives( $url_vars ) {
		$current_url = FWP()->helper->get_uri();
		if ( strpos( $current_url, 'datasets' ) === false ) {
			return $url_vars;
		}

		$valid_slugs = $this->get_cached_term_slugs();
		foreach ( $valid_slugs as $term_slug ) {
			if ( strpos( $current_url, $term_slug . '/datasets' ) !== false && empty( $url_vars['research_teams'] ) ) {
				$url_vars['research_teams'] = array( $term_slug );
				break;
			}
		}
		return $url_vars;
	}

	/**
	 * Add rewrite tag to post permalinks.
	 *
	 * @hook post_link
	 * @param mixed $permalink The permalink.
	 * @param mixed $post The post.
	 * @return mixed
	 */
	public function modify_post_permalinks( $permalink, $post ) {
		if ( 1 === get_current_blog_id() ) {
			return $permalink;
		}
		// Check if post has the `disable_research_team_permalink` meta key.
		if ( get_post_meta( $post->ID, 'disable_research_team_permalink', true ) ) {
			return $permalink;
		}

		$enabled_post_types = $this->get_rewrite_enabled_post_types();

		// Check if the post belongs to the `research-teams` taxonomy and has rewrites enabled.
		if ( in_array( 'research-teams', get_object_taxonomies( $post ), true )
			&& in_array( $post->post_status, array( 'publish' ), true )
			&& in_array( $post->post_type, $enabled_post_types, true ) ) {

			// Get the terms associated with the post.
			$terms = get_the_terms( $post, self::$taxonomy );
			if ( $terms && ! is_wp_error( $terms ) ) {
				// Get the primary term, if none is set, return the permalink unmodified.
				$primary_term_id = get_primary_term_id( $post->ID, self::$taxonomy );
				if ( false === $primary_term_id || ! is_numeric( $primary_term_id ) ) {
					return $permalink;
				}

				// Search through $terms for a term object with term_id of $primary_term_id.
				$primary_term = array_filter(
					$terms,
					function ( $term ) use ( $primary_term_id ) {
						return $term->term_id == $primary_term_id;
					}
				);

				$primary_term = ! empty( $primary_term ) ? array_pop( $primary_term ) : array_pop( $terms );
				$team_slug    = $primary_term->slug;

				// Skip excluded terms.
				if ( in_array( $team_slug, self::EXCLUDED_TERM_SLUGS, true ) ) {
					return $permalink;
				}

				$site_base_url = get_site_url();
				if ( str_starts_with( $permalink, trailingslashit( $site_base_url ) . $team_slug . '/' ) ) {
					return $permalink;
				}
				$permalink = str_replace( $site_base_url, $site_base_url . '/' . $team_slug, $permalink );
			}
		}

		return $permalink;
	}

	/**
	 * Modify canonical URL to include research team slug.
	 *
	 * Applies the same URL rewrite logic as modify_post_permalinks to ensure
	 * canonical URLs match the actual permalink structure.
	 *
	 * @hook prc_schema_seo_canonical_url
	 *
	 * @param string $canonical The canonical URL.
	 * @param int    $post_id   The post ID.
	 * @return string Modified canonical URL.
	 */
	public function modify_canonical_url( $canonical, $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return $canonical;
		}

		// Apply the same permalink modification logic.
		return $this->modify_post_permalinks( $canonical, $post );
	}

	/**
	 * Opt into primary term support.
	 *
	 * @hook prc_schema_seo_primary_term_taxonomies
	 *
	 * @param array $taxonomies The taxonomies.
	 * @return array The taxonomies.
	 */
	public function opt_into_primary_term_support( $taxonomies ) {
		if ( ! in_array( self::$taxonomy, $taxonomies, true ) ) {
			$taxonomies[] = self::$taxonomy;
		}
		return $taxonomies;
	}
}
