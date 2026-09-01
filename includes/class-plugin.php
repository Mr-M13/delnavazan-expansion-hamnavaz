<?php
/**
 * Main plugin coordinator.
 *
 * @package DelnavazanExpansion
 */

namespace Delnavazan\Expansion;

use Delnavazan\Expansion\Hamnavaz\Hamnavaz_Module;

defined( 'ABSPATH' ) || exit;

/**
 * Coordinates shared services and the expansion module registry.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Return the shared plugin instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Prevent direct construction.
	 */
	private function __construct() {
	}

	/**
	 * Register shared hooks and all enabled modules.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'admin_init', array( $this, 'maybe_update_version' ) );

		$loader = new Module_Loader(
			array(
				new Hamnavaz_Module(),
			)
		);

		$loader->register();
	}

	/**
	 * Load translations from the plugin languages directory.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'delnavazan-expansion',
			false,
			dirname( DELNAVAZAN_EXPANSION_BASENAME ) . '/languages'
		);
	}

	/**
	 * Keep the stored version current on code upgrades.
	 *
	 * Future upgrade orchestration can begin here without expanding the bootstrap.
	 * Schema migrations must track a separate data/schema version once required.
	 *
	 * @return void
	 */
	public function maybe_update_version() {
		$installed_version = get_option( 'delnavazan_expansion_version' );

		if ( DELNAVAZAN_EXPANSION_VERSION !== $installed_version ) {
			update_option( 'delnavazan_expansion_version', DELNAVAZAN_EXPANSION_VERSION, false );
		}
	}
}
