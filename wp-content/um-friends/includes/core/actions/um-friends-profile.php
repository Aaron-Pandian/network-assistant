<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add button in cover
 * @todo find the proper place for friends button in new UI.
 * @param array $args
 * @param int   $user_id
 */
function um_friends_add_button( $args, $user_id = null ) {
	if ( defined( 'UM_DEV_MODE' ) && UM_DEV_MODE && UM()->options()->get( 'enable_new_ui' ) ) {
		return;
	}

	if ( ! $user_id ) {
		$user_id = um_profile_id();
	}

	if ( ! empty( $args['cover_enabled'] ) ) {
		wp_enqueue_script( 'um_friends' );
		wp_enqueue_style( 'um_friends' );

		echo '<div class="um-friends-coverbtn">' . UM()->Friends_API()->api()->friend_button( $user_id, get_current_user_id() ) . '</div>';
	}
}
add_action( 'um_before_profile_main_meta', 'um_friends_add_button', 10, 2 );

/**
 * Add button in case that cover is disabled
 * @todo find the proper place for friends button in new UI.
 * @param $args
 */
function um_friends_add_button_nocover( $args = null, $user_id = null ) {
	if ( defined( 'UM_DEV_MODE' ) && UM_DEV_MODE && UM()->options()->get( 'enable_new_ui' ) ) {
		return;
	}

	wp_enqueue_script( 'um_friends' );
	wp_enqueue_style( 'um_friends' );

	if ( ! $user_id ) {
		$user_id = um_profile_id();
	}

	if ( ! empty( $args['cover_enabled'] ) ) {
		echo '<div class="um-friends-nocoverbtn" style="display: none;">' . UM()->Friends_API()->api()->friend_button( $user_id, get_current_user_id() ) . '</div>';
	} else {
		echo '<div class="um-friends-nocoverbtn" style="display: block;">' . UM()->Friends_API()->api()->friend_button( $user_id, get_current_user_id() ) . '</div>';
	}
}
add_action( 'um_after_profile_header_name', 'um_friends_add_button_nocover', 60, 2 );

if ( defined( 'UM_DEV_MODE' ) && UM_DEV_MODE && UM()->options()->get( 'enable_new_ui' ) ) {
	/**
	 * Add button in case that cover is disabled
	 *
	 * @param array $args
	 * @param int   $user_id
	 */
	function um_friends_add_navbar( $args, $user_id, &$index ) {
		$content = UM()->Friends_API()->api()->friend_button( $user_id, get_current_user_id() );
		if ( ! empty( $content ) ) {
			++$index;
			echo wp_kses( $content, UM()->get_allowed_html( 'templates' ) );
		}
	}
	add_action( 'um_profile_navbar_content', 'um_friends_add_navbar', 3, 3 );

	/**
	 * @param array $classes
	 * @return array
	 */
	function um_friends_profile_navbar_classes( $classes ) {
		$classes[] = 'um-has-friends-bar';
		return $classes;
	}
	add_filter( 'um_profile_navbar_classes', 'um_friends_profile_navbar_classes' );
}

/**
 * Add friendship state
 * @todo find the proper place for friendship state in new UI.
 * @param $args
 */
function um_friends_add_state( $args, $user_id = null ) {
	if ( defined( 'UM_DEV_MODE' ) && UM_DEV_MODE && UM()->options()->get( 'enable_new_ui' ) ) {
		return;
	}

	if ( ! is_user_logged_in() ) {
		return;
	}

	if ( ! $user_id ) {
		$user_id = um_profile_id();
	}

	if ( get_current_user_id() === absint( $user_id ) ) {
		return;
	}

	if ( UM()->Friends_API()->api()->is_friend( get_current_user_id(), $user_id ) ) {
		wp_enqueue_script( 'um_friends' );
		wp_enqueue_style( 'um_friends' );

		echo '<span class="um-friend-you"></span>';
	}
}
add_action( 'um_after_profile_name_inline', 'um_friends_add_state', 200, 2 );

/**
 * Friends List.
 */
function um_profile_content_friends() {
	echo apply_shortcodes('[ultimatemember_friends user_id="' . um_profile_id() . '"]');
}

if ( defined( 'UM_DEV_MODE' ) && UM_DEV_MODE && UM()->options()->get( 'enable_new_ui' ) ) {
	// default tab is handled in UM core
} else {
	add_action( 'um_profile_content_friends_default', 'um_profile_content_friends' );
}
add_action( 'um_profile_content_friends_myfriends', 'um_profile_content_friends' );

/**
 * Friend requests List.
 */
function um_profile_content_friends_friendreqs() {
	echo apply_shortcodes('[ultimatemember_friend_reqs user_id="' . um_profile_id() . '"]');
}
add_action( 'um_profile_content_friends_friendreqs', 'um_profile_content_friends_friendreqs' );

/**
 * Friend requests sent List.
 */
function um_profile_content_friends_sentreqs() {
	echo apply_shortcodes('[ultimatemember_friend_reqs_sent user_id="' . um_profile_id() . '"]');
}
add_action( 'um_profile_content_friends_sentreqs', 'um_profile_content_friends_sentreqs' );

/**
 * User suggestions for Social Activity
 *
 * @param $data
 * @param string $term
 *
 * @return mixed
 */
function um_friends_ajax_get_user_suggestions( $data, $term ) {
	if ( ! UM()->options()->get( 'activity_friends_mention' ) ) {
		return $data;
	}

	$term = str_replace( '@', '', $term );
	if ( empty( $term ) ) {
		return $data;
	}

	$users_data = array();

	$user_id = get_current_user_id();

	$friends = UM()->Friends_API()->api()->friends( $user_id );
	if ( $friends ) {
		foreach ( $friends as $arr ) {
			if ( ! isset( $arr['user_id1'] ) ) {
				continue;
			}
			$user_id1 = $arr['user_id1'];

			if ( $user_id1 === $user_id ) {
				continue;
			}

			$mentioned_data = um_friends_fetch_mentioned_user( $user_id1, $term );
			if ( false === $mentioned_data ) {
				continue;
			}
			$users_data[ $user_id1 ] = $mentioned_data;
		}

		foreach ( $friends as $arr ) {
			if ( ! isset( $arr['user_id2'] ) ) {
				continue;
			}
			$user_id2 = $arr['user_id2'];

			if ( $user_id2 === $user_id ) {
				continue;
			}

			$mentioned_data = um_friends_fetch_mentioned_user( $user_id2, $term );
			if ( false === $mentioned_data ) {
				continue;
			}
			$users_data[] = $mentioned_data;
		}
	}

	if ( ! empty( $users_data ) ) {
		$users_data = array_merge( ...$users_data );
		$data       = array_merge( $data, $users_data );
	}

	return $data;
}
add_filter( 'um_activity_ajax_get_user_suggestions', 'um_friends_ajax_get_user_suggestions', 10, 2 );

function um_friends_fetch_mentioned_user( $user_id, $term ) {
	um_fetch_user( $user_id );

	$start = mb_stripos( um_user( 'display_name' ), $term );
	if ( false === $start ) {
		return false;
	}
	$find_length = mb_strlen( $term );

	$first_sub  = mb_substr( um_user( 'display_name' ), 0, $start );
	$second_sub = mb_substr( um_user( 'display_name' ), $start, $find_length );
	$third_sub  = mb_substr( um_user( 'display_name' ), $start + $find_length );
	$name       = $first_sub . '<strong>' . $second_sub . '</strong>' . $third_sub;

	$users_data             = array();
	$users_data[ $user_id ] = array(
		'user_id'  => $user_id,
		'photo'    => get_avatar( $user_id, 80 ),
		'name'     => $name,
		'username' => um_user( 'display_name' ),
	);

	return $users_data;
}
