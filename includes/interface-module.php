<?php
/**
 * Contract implemented by Delnavazan Expansion modules.
 *
 * @package DelnavazanExpansion
 */

namespace Delnavazan\Expansion;

defined( 'ABSPATH' ) || exit;

/**
 * A module registers its own WordPress hooks without coupling them to the bootstrap.
 */
interface Module {

	/**
	 * Register the module's WordPress hooks.
	 *
	 * @return void
	 */
	public function register();
}
