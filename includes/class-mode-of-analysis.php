<?php
/**
 * Mode of Analysis Taxonomy
 *
 * @package PRC\Platform\Taxonomies
 */

namespace PRC\Platform\Taxonomies;

/**
 * Mode of Analysis Taxonomy
 *
 * @package PRC\Platform\Taxonomies
 */
class Mode_Of_Analysis {
	/**
	 * Taxonomy name.
	 *
	 * @var string
	 */
	protected static $taxonomy = 'mode-of-analysis';

	/**
	 * Constructor.
	 *
	 * @param Loader $loader The loader.
	 */
	public function __construct( $loader ) {
		$loader->add_action( 'init', $this, 'register' );
	}

	/**
	 * Register the taxonomy.
	 *
	 * @hook init
	 */
	public function register() {
		$taxonomy_name = self::$taxonomy;

		$labels = array(
			'name'                       => _x( 'Mode of Analysis', 'Taxonomy General Name', 'prc-taxonomies' ),
			'singular_name'              => _x( 'Mode of Analysis', 'Taxonomy Singular Name', 'prc-taxonomies' ),
			'menu_name'                  => __( 'Mode of Analysis', 'prc-taxonomies' ),
			'all_items'                  => __( 'All Mode of Analysis', 'prc-taxonomies' ),
			'parent_item'                => __( 'Parent Mode of Analysis', 'prc-taxonomies' ),
			'parent_item_colon'          => __( 'Parent Mode of Analysis:', 'prc-taxonomies' ),
			'new_item_name'              => __( 'New Mode of Analysis', 'prc-taxonomies' ),
			'add_new_item'               => __( 'Add Mode of Analysis', 'prc-taxonomies' ),
			'edit_item'                  => __( 'Edit Mode of Analysis', 'prc-taxonomies' ),
			'update_item'                => __( 'Update Mode of Analysis', 'prc-taxonomies' ),
			'view_item'                  => __( 'View Mode of Analysis', 'prc-taxonomies' ),
			'separate_items_with_commas' => __( 'Separate Mode of Analysis with commas', 'prc-taxonomies' ),
			'add_or_remove_items'        => __( 'Add or remove Mode of Analysis', 'prc-taxonomies' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'prc-taxonomies' ),
			'popular_items'              => __( 'Popular Mode of Analysis', 'prc-taxonomies' ),
			'search_items'               => __( 'Search Mode of Analysis', 'prc-taxonomies' ),
			'not_found'                  => __( 'Not Found', 'prc-taxonomies' ),
			'no_terms'                   => __( 'No Mode of Analysis', 'prc-taxonomies' ),
			'items_list'                 => __( 'Mode of Analysis list', 'prc-taxonomies' ),
			'items_list_navigation'      => __( 'Mode of Analysis list navigation', 'prc-taxonomies' ),
		);

		$args = array(
			'labels'            => $labels,
			'hierarchical'      => false,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => false,
			'show_tagcloud'     => false,
		);

		// @TODO: Add filters into modules to signal support for mode of analysis taxonomy.
		$post_types = apply_filters(
			"prc_taxonomy_{$taxonomy_name}_post_types",
			array(
				'post',
				'interactives',
				'interactive',
				'feature',
				'fact-sheet',
				'fact-sheets',
				'stub',
				'decoded',
			)
		);

		register_taxonomy( self::$taxonomy, $post_types, $args );
	}
}
