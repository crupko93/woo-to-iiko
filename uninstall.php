<?php
/**
 * 
 * This file runs when the plugin in uninstalled (deleted).
 * This will not run when the plugin is deactivated.
 * Ideally you will add all your clean-up scripts here
 * that will clean-up unused meta, options, etc. in the database.
 *
 */

// If plugin is not being uninstalled, exit (do nothing)
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Do something here if plugin is being uninstalled.


// При дезактивации плагина или в других случаях, обязательно нужно удалить ранее созданную задачу:
register_deactivation_hook( __FILE__, 'iiko_deactivation');
function iiko_deactivation() {
	wp_clear_scheduled_hook('iiko_cron_auto_update');
}