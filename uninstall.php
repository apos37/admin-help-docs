<?php
/**
 * Uninstall script
 */

use PluginRx\AdminHelpDocs\Cleanup;

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

$helpdocs_remove_all = get_option( 'helpdocs_remove_on_uninstall', false );
if ( ! $helpdocs_remove_all ) {
    return;
}

require_once __DIR__ . '/inc/cleanup.php';
Cleanup::run();