<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param $settings
 *
 * @return mixed
 */
function um_friends_settings( $settings ) {
	$settings['extensions']['sections']['friends'] = array(
		'title'  => __( 'Friends', 'um-friends' ),
		'fields' => array(
			array(
				'id'    => 'friends_show_stats',
				'type'  => 'checkbox',
				'label' => __( 'Show friends stats in member directory', 'um-friends' ),
			),
			array(
				'id'    => 'friends_show_button',
				'type'  => 'checkbox',
				'label' => __( 'Show friend button in member directory', 'um-friends' ),
			),
		),
	);

	return $settings;
}
add_filter( 'um_settings_structure', 'um_friends_settings', 10, 1 );


/**
 * @param $settings
 * @param $key
 *
 * @return mixed
 */
function um_friends_activity_settings( $settings, $key ) {
	$settings['extensions']['sections'][ $key ]['fields'] = array_merge(
		$settings['extensions']['sections'][ $key ]['fields'],
		array(
			array(
				'id'    => 'activity_friends_mention',
				'type'  => 'checkbox',
				'label' => __( 'Enable integration with friends to convert user names to user profile links automatically (mention users)?', 'um-friends' ),
			),
			array(
				'id'    => 'activity_friends_users',
				'type'  => 'checkbox',
				'label' => __( 'Show only friends activity in the social wall', 'um-friends' ),
			),
		)
	);

	return $settings;
}
add_filter( 'um_activity_settings_structure', 'um_friends_activity_settings', 10, 2 );


/**
 * @param $email_notifications
 *
 * @return mixed
 */
function um_friends_email_notifications( $email_notifications ) {
	$email_notifications['new_friend_request'] = array(
		'key'           => 'new_friend_request',
		'title'         => __( 'New Friend Request Notification','um-friends' ),
		'subject'       => '{friend} wants to be friends with you on {site_name}',
		'body'          => 'Hi {receiver},<br /><br />' .
			'{friend} has just sent you a friend request on {site_name}.<br /><br />' .
			'View their profile to accept/reject this friendship request:<br />' .
			'{friend_profile}<br /><br />' .
			'This is an automated notification from {site_name}. You do not need to reply.',
		'description'   => __('Send a notification to user when they receive a new friend request','um-friends'),
		'recipient'   => 'user',
		'default_active' => true
	);

	$email_notifications['new_friend'] = array(
		'key'           => 'new_friend',
		'title'         => __( 'New Friend Notification','um-friends' ),
		'subject'       => '{friend} has accepted your friend request',
		'body'          => 'Hi {receiver},<br /><br />' .
			'You are now friends with {friend} on {site_name}.<br /><br />' .
			'View their profile:<br />' .
			'{friend_profile}<br /><br />' .
			'This is an automated notification from {site_name}. You do not need to reply.',
		'description'   => __('Send a notification to user when a friend request get approved','um-friends'),
		'recipient'   => 'user',
		'default_active' => true
	);

	return $email_notifications;
}
add_filter( 'um_email_notifications', 'um_friends_email_notifications', 10, 1 );


/**
 * Adds a notification type
 *
 * @param array $logs
 *
 * @return array
 */
function um_friends_add_notification_type( $logs ) {
	$logs['new_friend_request'] = array(
		'title'         => __( 'User get a new friend request', 'um-friends' ),
		'account_desc'  => __( 'When someone requests friendship', 'um-friends' ),
	);

	$logs['new_friend'] = array(
		'title'         => __( 'User get a new friend', 'um-friends' ),
		'account_desc'  => __( 'When someone accepts friendship', 'um-friends' ),
	);

	return $logs;
}
add_filter( 'um_notifications_core_log_types', 'um_friends_add_notification_type', 200 );


/**
 * Adds a notification icon
 *
 * @param $output
 * @param $type
 *
 * @return string
 */
function um_friends_add_notification_icon( $output, $type ) {
	if ( $type == 'new_friend_request' ) {
		$output = '<i class="um-icon-android-person-add" style="color: #44b0ec"></i>';
	}

	if ( $type == 'new_friend' ) {
		$output = '<i class="um-icon-android-person" style="color: #44b0ec"></i>';
	}
	return $output;
}
add_filter( 'um_notifications_get_icon', 'um_friends_add_notification_icon', 10, 2 );


/**
 * Scan templates from extension
 *
 * @param $scan_files
 *
 * @return array
 */
function um_friends_extend_scan_files( $scan_files ) {
	$extension_files['um-friends'] = UM()->admin_settings()->scan_template_files( um_friends_path . '/templates/' );
	$scan_files                    = array_merge( $scan_files, $extension_files );

	return $scan_files;
}
add_filter( 'um_override_templates_scan_files', 'um_friends_extend_scan_files', 10, 1 );


/**
 * Get template paths
 *
 * @param $located
 * @param $file
 *
 * @return array
 */
function um_friends_get_path_template( $located, $file ) {
	if ( file_exists( get_stylesheet_directory() . '/ultimate-member/um-friends/' . $file ) ) {
		$located = array(
			'theme' => get_stylesheet_directory() . '/ultimate-member/um-friends/' . $file,
			'core'  => um_friends_path . 'templates/' . $file,
		);
	}

	return $located;
}
add_filter( 'um_override_templates_get_template_path__um-friends', 'um_friends_get_path_template', 10, 2 );
