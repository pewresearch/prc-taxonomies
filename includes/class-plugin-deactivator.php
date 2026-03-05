<?php
/**
 * Fired during plugin deactivation.
 *
 * @package    PRC\Platform\Taxonomies
 */

namespace PRC\Platform\Taxonomies;

/**
 * The plugin deactivator class.
 *
 * @package    PRC\Platform\Taxonomies
 */
class Plugin_Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		flush_rewrite_rules();

		wp_mail(
			DEFAULT_TECHNICAL_CONTACT,
			'PRC Taxonomies Deactivated',
			'The PRC Taxonomies plugin has been deactivated on ' . get_site_url()
		);
	}
}
