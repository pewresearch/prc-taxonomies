<?php
/**
 * Shared taxonomy utilities: post_tag admin visibility and activity trail term meta.
 *
 * @package PRC\Platform\Taxonomies
 */

namespace PRC\Platform\Taxonomies;

/**
 * Class Taxonomy_Utils
 */
class Taxonomy_Utils {
	/**
	 * Constructor.
	 *
	 * @param Loader $loader Loader instance.
	 */
	public function __construct( $loader ) {
		$this->init( $loader );
	}

	/**
	 * Register hooks.
	 *
	 * @param Loader $loader Loader instance.
	 */
	protected function init( $loader ) {
		// Disable "Post Tag" from appearing in the admin UI.
		$loader->add_filter( 'register_taxonomy_args', $this, 'modify_post_tag_taxonomy_args', 10, 2 );

		// Activity Trail.
		$loader->add_action( 'init', $this, 'register_activity_trail_meta' );
		$loader->add_action( 'create_term', $this, 'hook_on_to_term_update', 10, 4 );
		$loader->add_action( 'edit_term', $this, 'hook_on_to_term_update', 10, 4 );
	}

	/**
	 * Modify the arguments of the post_tag taxonomy here
	 * For example, you can set 'public' to false to hide it from the admin UI
	 *
	 * @hook register_taxonomy_args
	 *
	 * @param array  $args     Arguments.
	 * @param string $taxonomy Taxonomy.
	 * @return array
	 */
	public function modify_post_tag_taxonomy_args( $args, $taxonomy ) {
		if ( 'post_tag' === $taxonomy ) {
			$args['show_ui'] = false;
		}
		return $args;
	}

	/**
	 * Register the activity trail meta for the taxonomy.
	 *
	 * @hook init
	 *
	 * @param mixed $taxonomy Taxonomy.
	 */
	public function register_activity_trail_meta( $taxonomy = null ) {
		if ( ! $taxonomy ) {
			return;
		}
		register_term_meta(
			$taxonomy,
			'_last_updated_by',
			array(
				'type' => 'string',
			)
		);

		register_term_meta(
			$taxonomy,
			'_last_updated_at',
			array(
				'type' => 'string',
			)
		);
	}

	/**
	 * Whenever a term is created or edited this will log when it was changed and what user made that change.
	 *
	 * @hook create_term edit_term
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term Taxonomy ID.
	 * @param string $taxonomy Taxonomy.
	 * @param array  $args     Arguments.
	 */
	public function hook_on_to_term_update( int $term_id, int $tt_id, string $taxonomy, array $args ) {
		$this->log_activity_trail( $term_id, $tt_id, $taxonomy );
	}

	/**
	 * Log update metadata on the term.
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term Taxonomy ID.
	 * @param string $taxonomy Taxonomy.
	 */
	protected function log_activity_trail( $term_id, $tt_id, $taxonomy ) {
		$user_id = get_current_user_id();
		update_term_meta( $term_id, '_last_updated_by', $user_id );
		update_term_meta( $term_id, '_last_updated_at', current_time( 'mysql' ) );
	}
}
