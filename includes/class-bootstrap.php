<?php
/**
 * Bootstrap class.
 *
 * @package    PRC\Platform\Taxonomies
 */

namespace PRC\Platform\Taxonomies;

use WP_Error;

/**
 * Bootstrap class.
 *
 * @package    PRC\Platform\Taxonomies
 */
class Bootstrap {
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the platform as initialized by hooks.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->version     = PRC_TAXONOMIES_VERSION;
		$this->plugin_name = 'prc-taxonomies';

		$this->load_dependencies();
		$this->init_dependencies();
	}


	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		// Load plugin loading class.
		require_once plugin_dir_path( __DIR__ ) . '/includes/class-loader.php';

		// Initialize the loader.
		$this->loader = new Loader();

		// Load taxonomy classes.
		require_once plugin_dir_path( __DIR__ ) . '/includes/class-topic-category.php';
		require_once plugin_dir_path( __DIR__ ) . '/includes/class-decoded-category.php';
		require_once plugin_dir_path( __DIR__ ) . '/includes/class-formats.php';
		require_once plugin_dir_path( __DIR__ ) . '/includes/class-fund-pools.php';
		require_once plugin_dir_path( __DIR__ ) . '/includes/class-languages.php';
		require_once plugin_dir_path( __DIR__ ) . '/includes/class-mode-of-analysis.php';
		require_once plugin_dir_path( __DIR__ ) . '/includes/class-regions-countries.php';
		require_once plugin_dir_path( __DIR__ ) . '/includes/class-research-teams.php';
	}

	/**
	 * Initialize the dependencies.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function init_dependencies() {
		new Topic_Category( $this->get_loader() );
		new Decoded_Category( $this->get_loader() );
		new Formats( $this->get_loader() );
		new Fund_Pools( $this->get_loader() );
		new Languages( $this->get_loader() );
		new Mode_Of_Analysis( $this->get_loader() );
		new Regions_Countries( $this->get_loader() );
		new Research_Teams( $this->get_loader() );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    PRC\Platform\Taxonomies\Loader
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
