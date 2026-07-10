<?php
/**
 * Languages Taxonomy
 *
 * @package PRC\Platform\Taxonomies
 */

namespace PRC\Platform\Taxonomies;

/**
 * Languages Taxonomy
 *
 * @package PRC\Platform\Taxonomies
 */
class Languages {
	/**
	 * Taxonomy name.
	 *
	 * @var string
	 */
	protected static $taxonomy = 'languages';

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
			'name'                       => _x( 'Languages', 'Taxonomy General Name', 'prc-taxonomies' ),
			'singular_name'              => _x( 'Language', 'Taxonomy Singular Name', 'prc-taxonomies' ),
			'menu_name'                  => __( 'Languages', 'prc-taxonomies' ),
			'all_items'                  => __( 'All Languages', 'prc-taxonomies' ),
			'parent_item'                => __( 'Parent Language', 'prc-taxonomies' ),
			'parent_item_colon'          => __( 'Parent Language:', 'prc-taxonomies' ),
			'new_item_name'              => __( 'New Language', 'prc-taxonomies' ),
			'add_new_item'               => __( 'Add New Language', 'prc-taxonomies' ),
			'edit_item'                  => __( 'Edit Languages', 'prc-taxonomies' ),
			'update_item'                => __( 'Update Language', 'prc-taxonomies' ),
			'view_item'                  => __( 'View Language', 'prc-taxonomies' ),
			'separate_items_with_commas' => __( 'Separate Languages with commas', 'prc-taxonomies' ),
			'add_or_remove_items'        => __( 'Add or remove Languages', 'prc-taxonomies' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'prc-taxonomies' ),
			'popular_items'              => __( 'Popular Languages', 'prc-taxonomies' ),
			'search_items'               => __( 'Search Languages', 'prc-taxonomies' ),
			'not_found'                  => __( 'Not Found', 'prc-taxonomies' ),
			'no_terms'                   => __( 'No Languages', 'prc-taxonomies' ),
			'items_list'                 => __( 'Languages list', 'prc-taxonomies' ),
			'items_list_navigation'      => __( 'Languages list navigation', 'prc-taxonomies' ),
		);
		$args   = array(
			'labels'            => $labels,
			'hierarchical'      => false,
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => false,
			'show_tagcloud'     => false,
			'show_in_rest'      => true,
		);

		// @TODO: Add filters into modules to signal support for language taxonomy.
		$post_types = apply_filters(
			"prc_taxonomy_{$taxonomy_name}_post_types",
			array(
				'post',
				'fact-sheets',
				'fact-sheet',
				'stub',
				'decoded',
				'short-read',
			)
		);

		register_taxonomy( self::$taxonomy, $post_types, $args );
	}
}
