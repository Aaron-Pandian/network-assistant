<?php
/**
 * Uninstall UM User Location
 *
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


if ( ! defined( 'um_user_locations_path' ) ) {
	define( 'um_user_locations_path', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'um_user_locations_url' ) ) {
	define( 'um_user_locations_url', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'um_user_locations_plugin' ) ) {
	define( 'um_user_locations_plugin', plugin_basename( __FILE__ ) );
}

$options = get_option( 'um_options', array() );

if ( ! empty( $options['uninstall_on_delete'] ) ) {
	if ( ! class_exists( 'um_ext\um_user_locations\core\Setup' ) ) {
		require_once um_user_locations_path . 'includes/core/class-setup.php';
	}

	$user_locations_setup = new um_ext\um_user_locations\core\Setup();

	//remove settings
	foreach ( $user_locations_setup->settings_defaults as $k => $v ) {
		unset( $options[ $k ] );
	}

	unset( $options['um_user_locations_license_key'] );

	update_option( 'um_options', $options );

	delete_option( 'um_user_locations_last_version_upgrade' );
	delete_option( 'um_user_locations_version' );
	delete_option( 'um_map_user_fields' );
}