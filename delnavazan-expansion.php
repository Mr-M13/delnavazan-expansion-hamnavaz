<?php
/**
 * Plugin Name:       Delnavazan Expansion
 * Plugin URI:        https://delnavazan.com/
 * Description:       Extensible feature modules for Delnavazan, beginning with Hamnavaz.
 * Version:           0.2.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Delnavazan
 * Author URI:        https://delnavazan.com/
 * Text Domain:       delnavazan-expansion
 * Domain Path:       /languages
 *
 * @package DelnavazanExpansion
 */

defined( 'ABSPATH' ) || exit;

define( 'DELNAVAZAN_EXPANSION_VERSION', '0.2.0' );
define( 'DELNAVAZAN_EXPANSION_FILE', __FILE__ );
define( 'DELNAVAZAN_EXPANSION_PATH', plugin_dir_path( __FILE__ ) );
define( 'DELNAVAZAN_EXPANSION_URL', plugin_dir_url( __FILE__ ) );
define( 'DELNAVAZAN_EXPANSION_BASENAME', plugin_basename( __FILE__ ) );

require_once DELNAVAZAN_EXPANSION_PATH . 'includes/interface-module.php';
require_once DELNAVAZAN_EXPANSION_PATH . 'includes/class-module-loader.php';
require_once DELNAVAZAN_EXPANSION_PATH . 'includes/class-activator.php';
require_once DELNAVAZAN_EXPANSION_PATH . 'includes/class-deactivator.php';
require_once DELNAVAZAN_EXPANSION_PATH . 'includes/class-plugin.php';
require_once DELNAVAZAN_EXPANSION_PATH . 'modules/hamnavaz/class-teacher-post-type.php';
require_once DELNAVAZAN_EXPANSION_PATH . 'modules/hamnavaz/class-teacher-taxonomies.php';
require_once DELNAVAZAN_EXPANSION_PATH . 'modules/hamnavaz/class-teacher-meta.php';
require_once DELNAVAZAN_EXPANSION_PATH . 'modules/hamnavaz/class-teacher-admin-list.php';
require_once DELNAVAZAN_EXPANSION_PATH . 'modules/hamnavaz/class-hamnavaz-module.php';

register_activation_hook( __FILE__, array( 'Delnavazan\\Expansion\\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Delnavazan\\Expansion\\Deactivator', 'deactivate' ) );

Delnavazan\Expansion\Plugin::instance()->register();
