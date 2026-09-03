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
	 * Persistent teacher data remains intact; only rewrite rules are refreshed.
	 *
	 * @return void
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}
}
