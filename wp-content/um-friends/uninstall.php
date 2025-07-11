<?php
/**
* Uninstall UM Friends
*
*/

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}


if ( ! defined( 'um_friends_path' ) ) {
	define( 'um_friends_path', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'um_friends_url' ) ) {
	define( 'um_friends_url', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'um_friends_plugin' ) ) {
	define( 'um_friends_plugin', plugin_basename( __FILE__ ) );
}

$options = get_option( 'um_options', array() );

if ( ! empty( $options['uninstall_on_delete'] ) ) {
	if ( ! class_exists( 'um_ext\um_friends\core\Friends_Setup' ) ) {
		require_once um_friends_path . 'includes/core/class-friends-setup.php';
	}

	$friends_setup = new um_ext\um_friends\core\Friends_Setup();

	//remove settings
	foreach ( $friends_setup->settings_defaults as $k => $v ) {
		unset( $options[ $k ] );
	}

	unset( $options['um_friends_license_key'] );

	update_option( 'um_options', $options );

	delete_option( 'um_friends_version' );
	delete_option( 'ultimatemember_friends_db' );
	delete_option( 'um_friends_last_version_upgrade' );
	delete_option( 'widget_um_my_friends' );

	//remove tables
	global $wpdb;
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}um_friends" );
}