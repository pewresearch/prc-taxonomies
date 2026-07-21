<?php
/**
 * Topic Category Taxonomy
 *
 * @package PRC\Platform\Taxonomies
 */

namespace PRC\Platform\Taxonomies;

use WP_Error;

/**
 * The "Category" core taxonomy as our "Topic" taxonomy.
 *
 * @package PRC\Platform\Taxonomies
 */
class Topic_Category {
	/**
	 * The taxonomy slug.
	 *
	 * @var string
	 */
	protected static $taxonomy = 'category';

	/**
	 * The handle for the category taxonomy.
	 *
	 * @var string
	 */
	public static $handle = 'prc-taxonomies-topic-category';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param Loader $loader The loader.
	 */
	public function __construct( $loader ) {
		$loader->add_action( 'init', $this, 'enforce_category_permalink_structure' );
		$loader->add_filter( 'register_taxonomy_args', $this, 'change_category_labels_to_topic', 10, 2 );
		$loader->add_action( 'enqueue_block_editor_assets', $this, 'enqueue_category_name_change_script' );
	}

	/**
	 * Enforce the category permalink structure.
	 *
	 * @TODO: add this to the plugin activation hook, when we get around to building that.
	 * @return void
	 */
	public function enforce_category_permalink_structure() {
		if ( 'topic' !== get_option( 'category_base' ) ) {
			update_option( 'category_base', 'topic' );
		}
	}

	/**
	 * On the primary site we want to change the vernacular of "Categories" to "Topics".
	 *
	 * @param mixed $args The arguments.
	 * @param mixed $taxonomy The taxonomy.
	 * @return mixed The arguments.
	 */
	public function change_category_labels_to_topic( $args, $taxonomy ) {
		if ( $taxonomy === self::$taxonomy ) {
			$args['labels']                               = array();
			$args['labels']['name']                       = 'Topics';
			$args['labels']['singular_name']              = 'Topic';
			$args['labels']['menu_name']                  = 'Topics';
			$args['labels']['all_items']                  = 'All Topics';
			$args['labels']['edit_item']                  = 'Edit Topic';
			$args['labels']['view_item']                  = 'View Topic';
			$args['labels']['update_item']                = 'Update Topic';
			$args['labels']['add_new_item']               = 'Add New Topic';
			$args['labels']['new_item_name']              = 'New Topic Name';
			$args['labels']['parent_item']                = 'Parent Topic';
			$args['labels']['search_items']               = 'Search Topics';
			$args['labels']['popular_items']              = 'Popular Topics';
			$args['labels']['separate_items_with_commas'] = 'Separate topics with commas';
			$args['labels']['add_or_remove_items']        = 'Add or remove topics';
			$args['labels']['choose_from_most_used']      = 'Choose from the most used topics';
			$args['labels']['not_found']                  = 'No topics found';
			$args['labels']['no_terms']                   = 'No topics';
		}
		return $args;
	}

	/**
	 * Register the category name change filters.
	 *
	 * @return mixed The arguments.
	 */
	public function register_category_name_change_filters() {
		$asset_file = include PRC_TAXONOMIES_DIR . '/build/index.asset.php';
		$asset_slug = self::$handle;
		$script_src = plugins_url( 'build/index.js', PRC_TAXONOMIES_FILE );

		$script = wp_register_script(
			$asset_slug,
			$script_src,
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		if ( ! $script ) {
			return new WP_Error( self::$handle, 'Failed to register all category name change assets' );
		}

		return true;
	}

	/**
	 * Enqueue the category name change script.
	 *
	 * @hook enqueue_block_editor_assets
	 */
	public function enqueue_category_name_change_script() {
		$registered = $this->register_category_name_change_filters();
		if ( is_admin() && ! is_wp_error( $registered ) ) {
			wp_enqueue_script( self::$handle );
			wp_enqueue_style( self::$handle );
		}
	}
}
