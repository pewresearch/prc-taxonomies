<?php
/**
 * Regions & Countries Taxonomy
 *
 * @package PRC\Platform\Taxonomies
 */

namespace PRC\Platform\Taxonomies;

/**
 * Regions & Countries Taxonomy
 *
 * @package PRC\Platform\Taxonomies
 */
class Regions_Countries {
	/**
	 * Taxonomy name.
	 *
	 * @var string
	 */
	protected static $taxonomy = 'regions-countries';

	/**
	 * Constructor.
	 *
	 * @param Loader $loader The loader.
	 */
	public function __construct( $loader ) {
		$loader->add_action( 'init', $this, 'register' );
		$loader->add_filter( 'prc_sitemap_supported_taxonomies', $this, 'opt_into_sitemap', 10, 1 );
		$loader->add_filter( 'prc_schema_seo_primary_term_taxonomies', $this, 'opt_into_primary_term_support', 20, 1 );
	}

	/**
	 * Register the taxonomy.
	 *
	 * @hook init
	 */
	public function register() {
		$taxonomy_name = self::$taxonomy;

		$labels = array(
			'name'                       => _x( 'Regions & Countries', 'Taxonomy General Name', 'prc-taxonomies' ),
			'singular_name'              => _x( 'Region/Country', 'Taxonomy Singular Name', 'prc-taxonomies' ),
			'menu_name'                  => __( 'Regions & Countries', 'prc-taxonomies' ),
			'all_items'                  => __( 'All Regions & Countries', 'prc-taxonomies' ),
			'parent_item'                => __( 'Parent Region/Country', 'prc-taxonomies' ),
			'parent_item_colon'          => __( 'Parent Region/Country:', 'prc-taxonomies' ),
			'new_item_name'              => __( 'New Region/Country', 'prc-taxonomies' ),
			'add_new_item'               => __( 'Add New Region/Country', 'prc-taxonomies' ),
			'edit_item'                  => __( 'Edit Region/Country', 'prc-taxonomies' ),
			'update_item'                => __( 'Update Region/Country', 'prc-taxonomies' ),
			'view_item'                  => __( 'View Region/Country', 'prc-taxonomies' ),
			'separate_items_with_commas' => __( 'Separate regions & countries with commas', 'prc-taxonomies' ),
			'add_or_remove_items'        => __( 'Add or remove region/country', 'prc-taxonomies' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'prc-taxonomies' ),
			'popular_items'              => __( 'Popular Regions & Countries', 'prc-taxonomies' ),
			'search_items'               => __( 'Search Regions & Countries', 'prc-taxonomies' ),
			'not_found'                  => __( 'Not Found', 'prc-taxonomies' ),
			'no_terms'                   => __( 'No Regions & Countries', 'prc-taxonomies' ),
			'items_list'                 => __( 'Regions & Countries list', 'prc-taxonomies' ),
			'items_list_navigation'      => __( 'Regions & Countries list navigation', 'prc-taxonomies' ),
			'item_link'                  => __( 'Region/Country Link', 'prc-taxonomies' ),
			'item_link_description'      => __( 'The link to the region/country page.', 'prc-taxonomies' ),
		);

		$args = array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
			'show_in_rest'      => true,
		);

		// @TODO: Add filters into modules to signal support for regions & countries taxonomy.
		$post_types = apply_filters(
			"prc_taxonomy_{$taxonomy_name}_post_types",
			array(
				'post',
				'feature',
				'fact-sheet',
				'short-read',
				'quiz',
				'stub',
				'decoded',
				'block_module',
			)
		);

		register_taxonomy( self::$taxonomy, $post_types, $args );
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
		if ( ! in_array( self::$taxonomy, $taxonomies ) ) {
			$taxonomies[] = self::$taxonomy;
		}
		return $taxonomies;
	}

	/**
	 * Opt into sitemap.
	 *
	 * @hook prc_sitemap_supported_taxonomies
	 *
	 * @param array $taxonomy_types The taxonomy types.
	 * @return array The taxonomy types.
	 */
	public function opt_into_sitemap( $taxonomy_types ) {
		$taxonomy_types[] = self::$taxonomy;
		return $taxonomy_types;
	}
}
