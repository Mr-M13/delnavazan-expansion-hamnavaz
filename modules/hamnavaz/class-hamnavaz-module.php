<?php
/**
 * Hamnavaz module foundation.
 *
 * @package DelnavazanExpansion
 */

namespace Delnavazan\Expansion\Hamnavaz;

use Delnavazan\Expansion\Module;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the minimal Hamnavaz administration presence for Phase 1.
 */
final class Hamnavaz_Module implements Module {

	/**
	 * Admin page slug.
	 *
	 * @var string
	 */
	const ADMIN_SLUG = 'delnavazan-expansion';

	/**
	 * Hook suffix WordPress assigns to the admin screen.
	 *
	 * @var string|false
	 */
	private $admin_hook = false;

	/**
	 * Register the module's WordPress hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'register_admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Add a capability-protected admin page.
	 *
	 * @return void
	 */
	public function register_admin_page() {
		$this->admin_hook = add_menu_page(
			esc_html__( 'Delnavazan Expansion', 'delnavazan-expansion' ),
			esc_html__( 'Delnavazan', 'delnavazan-expansion' ),
			'manage_options',
			self::ADMIN_SLUG,
			array( $this, 'render_admin_page' ),
			'dashicons-groups',
			58
		);
	}

	/**
	 * Load styles only on the Delnavazan Expansion admin page.
	 *
	 * @param string $hook_suffix Current admin screen hook suffix.
	 * @return void
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		if ( $this->admin_hook !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'delnavazan-expansion-admin',
			DELNAVAZAN_EXPANSION_URL . 'assets/css/admin.css',
			array(),
			DELNAVAZAN_EXPANSION_VERSION
		);
	}

	/**
	 * Render the minimal Phase 1 status screen.
	 *
	 * @return void
	 */
	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'delnavazan-expansion' ) );
		}
		?>
		<div class="wrap delnavazan-expansion-admin" dir="auto">
			<h1><?php echo esc_html__( 'Delnavazan Expansion', 'delnavazan-expansion' ); ?></h1>
			<div class="delnavazan-expansion-admin__card">
				<h2><?php echo esc_html__( 'Hamnavaz', 'delnavazan-expansion' ); ?></h2>
				<p><?php echo esc_html__( 'The Hamnavaz module foundation is active.', 'delnavazan-expansion' ); ?></p>
				<p>
					<strong><?php echo esc_html__( 'Version:', 'delnavazan-expansion' ); ?></strong>
					<code><?php echo esc_html( DELNAVAZAN_EXPANSION_VERSION ); ?></code>
				</p>
			</div>
		</div>
		<?php
	}
}
