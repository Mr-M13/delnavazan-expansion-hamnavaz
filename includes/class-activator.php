<?php
/**
 * Plugin activation lifecycle.
 *
 * @package DelnavazanExpansion
 */

namespace Delnavazan\Expansion;

defined( 'ABSPATH' ) || exit;

/**
 * Performs only reversible foundation setup on activation.
 */
final class Activator {

	/**
	 * Store the installed plugin version for future migrations.
	 *
	 * No tables, roles, capabilities, content, or rewrite rules are created in Phase 1.
	 *
	 * @return void
	 */
	public static function activate() {
		update_option( 'delnavazan_expansion_version', DELNAVAZAN_EXPANSION_VERSION, false );
	}
}
