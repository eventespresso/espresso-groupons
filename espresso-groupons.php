<?php
/*
Plugin Name: Event Espresso - Groupons Addon
Plugin URI: http://eventespresso.com/
Description: Groupon integration addon for Event Espresso. <a href="admin.php?page=support">Support</a>

Version: 1.6

Author: Seth Shoultes
Author URI: http://www.eventespresso.com

Copyright (c) 2008-2011 Seth Shoultes  All Rights Reserved.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

define("EVENTS_GROUPON_CODES_TABLE", $wpdb->prefix .'events_groupon_codes'); //Define Groupon db table shortname
require_once("event_management_functions.php");
require_once("use_groupon_code.php");
require_once("groupons_admin_page.php");

register_activation_hook(__FILE__,'event_espresso_groupon_install');//Install groupon tables
register_deactivation_hook(__FILE__,'event_espresso_groupon_deactivate');
add_action('plugins_loaded', 'event_espresso_update_banner');

function event_espresso_groupon_install(){
	//get previous version
	$prev_version = get_option('events_groupon_codes_table_version');
	update_option('events_groupon_codes_table_version', '1.6');
	//Groupon database install end
	add_option('events_groupons_active', 'true', '', 'yes');
	update_option('events_groupons_active', 'true');

	//trigger import if pre 1.6 is installed.
	if ( $prev_version <= 1.5 ) {
		update_option('events_groupons_update', 'update');
	}

}

function event_espresso_groupon_deactivate(){
	update_option( 'events_groupons_active', 'false');
}

function event_espresso_update_banner() {
	if ( 'update' == get_option('events_groupons_update', 'update') ) {
		//let's show the import banner and button for triggering the import.
		add_action('wp_ajax_run_events_groupon_update', 'run_events_groupon_update' ); //ajax handler for when the upgrade button is pressed.
		add_action('admin_notices', 'display_events_groupon_update_notice');
	}
}

/**
 * Do the upgrade to transfer groupon codes from the old table structure into the new one. TODO we need to work out how users will indicate which events the groupon_codes will be assigned to. And it would be good to allow batch assigning.
 */
function run_events_groupon_update() {
	//todo
}

/**
 * Display the upgrade container for handling the transfer of groupon codes from the old table structure into the new one.
 */
function display_events_groupon_update_notice() {
	//todo
}