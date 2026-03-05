<?php
/**
 * Decoded Category Taxonomy
 *
 * @package PRC\Platform\Taxonomies
 */

namespace PRC\Platform\Taxonomies;

/**
 * Decoded Category Taxonomy
 *
 * @package PRC\Platform\Taxonomies
 */
class Decoded_Category {
	/**
	 * The taxonomy slug.
	 *
	 * @var string
	 */
	protected static $taxonomy = 'decoded-category';

	/**
	 * Initialize the class and set its properties.
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
		$labels = array(
			'name'                       => _x( 'Decoded Category', 'Taxonomy General Name', 'prc-taxonomies' ),
			'singular_name'              => _x( 'Decoded Category', 'Taxonomy Singular Name', 'prc-taxonomies' ),
			'menu_name'                  => __( 'Decoded Category', 'prc-taxonomies' ),
			'all_items'                  => __( 'All Decoded Categories', 'prc-taxonomies' ),
			'parent_item'                => __( 'Parent Decoded Category', 'prc-taxonomies' ),
			'parent_item_colon'          => __( 'Parent Decoded Category:', 'prc-taxonomies' ),
			'new_item_name'              => __( 'New Decoded Category', 'prc-taxonomies' ),
			'add_new_item'               => __( 'Add Decoded Category', 'prc-taxonomies' ),
			'edit_item'                  => __( 'Edit Decoded Category', 'prc-taxonomies' ),
			'update_item'                => __( 'Update Decoded Category', 'prc-taxonomies' ),
			'view_item'                  => __( 'View Decoded Category', 'prc-taxonomies' ),
			'separate_items_with_commas' => __( 'Separate Decoded Category with commas', 'prc-taxonomies' ),
			'add_or_remove_items'        => __( 'Add or remove Decoded Category', 'prc-taxonomies' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'prc-taxonomies' ),
			'popular_items'              => __( 'Popular Decoded Category', 'prc-taxonomies' ),
			'search_items'               => __( 'Search Decoded Category', 'prc-taxonomies' ),
			'not_found'                  => __( 'Not Found', 'prc-taxonomies' ),
			'no_terms'                   => __( 'No Decoded Category', 'prc-taxonomies' ),
			'items_list'                 => __( 'Decoded Category list', 'prc-taxonomies' ),
			'items_list_navigation'      => __( 'Decoded Category list navigation', 'prc-taxonomies' ),
		);

		$args = array(
			'labels'            => $labels,
			'hierarchical'      => false,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => false,
			'show_tagcloud'     => false,
			'show_in_rest'      => true,
		);

		register_taxonomy( self::$taxonomy, 'decoded', $args );
	}
}
