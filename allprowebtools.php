<?php
/*
Plugin Name: AllProWebTools Leadboxes
Plugin URI: http://www.AllProWebTools.com
Description: Use widgets to access your AllProWebTools Leadboxes
Author: AllProWebTools
Author URI: http://www.AllProWebTools.com
Version: 1.1.4
License: GPLv2
*/
	require_once('includes/apwt.php');
	require_once('includes/apwt-wp-admin.php');

	add_action( 'wp_enqueue_scripts', 'APWT_enqueue_scripts' );
	add_action('admin_menu','APWT_admin_menu');

	add_action( 'widgets_init', 'APWTLeadBoxInit');

	if ( (!get_site_option("APWTAPIKEY")) || (!get_site_option("APWTAPIAUTH")) ) {
		//apikeys not set yet - show the demo
		update_site_option("APWTAPIKEY", "vWfIKyblAXXGxxJDTfBKyTKDI");
		update_site_option("APWTAPIAUTH", "YE9itZClNuEa2NRP");
	}

	register_activation_hook(__FILE__,'APWTLeadBoxActivate');
	register_deactivation_hook( __FILE__, 'APWTLeadBoxDeactivate' );

function APWTLeadBoxInit() {
	register_widget( 'APWTLeadBox' );
}

function APWT_plugin_get_version() {
	$plugin_data = get_plugin_data( __FILE__ );
	$plugin_version = $plugin_data['Version'];

	if ($plugin_version == '') {
		print "cannot access plugin version";
		exit;
	}

	return $plugin_version;
}
