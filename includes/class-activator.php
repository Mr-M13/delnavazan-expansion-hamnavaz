<?php
/**
 * Plugin activation lifecycle.
 *
 * @package DelnavazanExpansion
 */

namespace Delnavazan\Expansion;

use Delnavazan\Expansion\Hamnavaz\Public_Routing;

defined( 'ABSPATH' ) || exit;

/**
 * Performs only reversible foundation setup on activation.
 */
final class Activator {

	/**
	 * Store the installed version and make the controlled profile route available.
	 *
	 * @return void
	 */
	public static function activate() {
		Public_Routing::register_rewrite_rule();
		flush_rewrite_rules();
		update_option( 'delnavazan_expansion_version', DELNAVAZAN_EXPANSION_VERSION, false );
	}
}
