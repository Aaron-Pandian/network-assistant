<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array $settings
 *
 * @return array
 */
function um_friends_license_settings( $settings ) {
	$settings['licenses']['fields'][] = array(
		'id'        => 'um_friends_license_key',
		'label'     => __( 'Friends License Key', 'um-friends' ),
		'item_name' => 'Friends',
		'author'    => 'Ultimate Member',
		'version'   => um_friends_version,
	);

	return $settings;
}
add_filter( 'um_settings_structure', 'um_friends_license_settings' );
