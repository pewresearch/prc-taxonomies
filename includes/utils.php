<?php
/**
 * Utility functions.
 *
 * @package    PRC\Platform\Taxonomies
 */

namespace PRC\Platform\Taxonomies;

/**
 * Get the primary term id.
 *
 * @param int    $post_id Post ID.
 * @param string $taxonomy Taxonomy slug.
 * @return int|false Term ID if PRC Schema SEO active and primary term found, false otherwise.
 */
function get_primary_term_id( int $post_id, string $taxonomy ): ?int {
	if ( function_exists( '\PRC\Platform\Schema_SEO\Utils\get_primary_term_id' ) ) {
		return \PRC\Platform\Schema_SEO\Utils\get_primary_term_id( $post_id, $taxonomy );
	}
	return false;
}
