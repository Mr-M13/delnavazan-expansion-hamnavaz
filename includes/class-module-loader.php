<?php
/**
 * Module registration orchestration.
 *
 * @package DelnavazanExpansion
 */

namespace Delnavazan\Expansion;

defined( 'ABSPATH' ) || exit;

/**
 * Registers independently implemented expansion modules.
 */
final class Module_Loader {

	/**
	 * Modules to register.
	 *
	 * @var Module[]
	 */
	private $modules;

	/**
	 * Create a module loader.
	 *
	 * @param Module[] $modules Modules to register.
	 */
	public function __construct( array $modules ) {
		$this->modules = $modules;
	}

	/**
	 * Register every configured module.
	 *
	 * @return void
	 */
	public function register() {
		foreach ( $this->modules as $module ) {
			$module->register();
		}
	}
}
