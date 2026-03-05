<?php
/**
 * Fired during plugin activation.
 *
 * @package    PRC\Platform\Taxonomies
 */

namespace PRC\Platform\Taxonomies;

/**
 * The plugin activator class.
 *
 * @package    PRC\Platform\Taxonomies
 */
class Plugin_Activator {

	/**
	 * Activate the plugin.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		flush_rewrite_rules();

		wp_mail(
			DEFAULT_TECHNICAL_CONTACT,
			'PRC Taxonomies Activated',
			'The PRC Taxonomies plugin has been activated on ' . get_site_url()
		);
	}
}
