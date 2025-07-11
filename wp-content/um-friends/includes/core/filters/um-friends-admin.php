<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter user permissions in Friends
 *
 * @param array $meta
 *
 * @return array
 */
function um_friends_user_permissions_filter( $meta ) {
	if ( ! isset( $meta['can_friend'] ) ) {
		$meta['can_friend'] = 1;
	}

	return $meta;
}
add_filter( 'um_user_permissions_filter', 'um_friends_user_permissions_filter' );

/**
 * Filter for Groups invites
 *
 * @param array $options
 *
 * @return array
 */
function um_friends_groups_invite_people( $options ) {
	$options['friends'] = __( 'Friends only', 'um-friends' );

	if ( ! empty( $options['followers'] ) ) {
		$options['friends_followers'] = __( 'Friends & Followers only', 'um-friends' );
	}

	return $options;
}
add_filter( 'um_groups_invite_people', 'um_friends_groups_invite_people', 20 );

/**
 * Creates options in Role page
 *
 * @param array $roles_metaboxes
 *
 * @return array
 */
function um_friends_add_role_metabox( $roles_metaboxes ) {
	$roles_metaboxes[] = array(
		'id'       => 'um-admin-form-friends{' . um_friends_path . '}',
		'title'    => __( 'Friends', 'um-friends' ),
		'callback' => array( UM()->metabox(), 'load_metabox_role' ),
		'screen'   => 'um_role_meta',
		'context'  => 'normal',
		'priority' => 'default',
	);

	return $roles_metaboxes;
}
add_filter( 'um_admin_role_metaboxes', 'um_friends_add_role_metabox' );

/**
 * Add options for field's privacy
 *
 * @param array $options
 *
 * @return array
 */
function um_friends_privacy_options( $options ) {
	$options['-4'] = __( 'Only friends', 'um-friends' );

	// check if there is 'only followers' option
	if ( isset( $options['-5'] ) ) {
		$options['-6'] = __( 'Only friends and followers', 'um-friends' );
	}

	return $options;
}
add_filter( 'um_field_privacy_options', 'um_friends_privacy_options' );
