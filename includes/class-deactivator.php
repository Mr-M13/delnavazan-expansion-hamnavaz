<?php
/**
 * Plugin deactivation lifecycle.
 *
 * @package DelnavazanExpansion
 */

namespace Delnavazan\Expansion;

defined( 'ABSPATH' ) || exit;

/**
 * Keeps persistent plugin data intact when the plugin is temporarily disabled.
 */
final class Deactivator {

	/**
	 * Deactivate the plugin safely.
	 *
	 * The plugin registers no schedules or rewrite rules that require cleanup.
	 *
	 * @return void
	 */
	public static function deactivate() {
		// Intentionally empty. Persistent data is removed only by uninstall.php.
	}
}
