<?php
/**
 * Delnavazan Expansion uninstall routine.
 *
 * @package DelnavazanExpansion
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'delnavazan_expansion_version' );
