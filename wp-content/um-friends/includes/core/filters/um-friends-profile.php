<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * More profile privacy options
 *
 * @param array $options
 *
 * @return array
 */
function um_friends_profile_privacy_options( $options ) {
	$options = array_merge( $options, array(
		'friends' => __( 'Friends only', 'um-friends' ),
	) );

	return $options;
}
add_filter( 'um_profile_privacy_options', 'um_friends_profile_privacy_options', 100, 1 );


/**
 * Show field only for friends
 * @param bool $can_view
 * @param array $data
 *
 * @return bool
 */
function um_friends_can_view_field( $can_view, $data ) {
	if ( isset( $data['public'] ) && ( $data['public'] == '-4' || $data['public'] == '-6' ) ) {
		if ( ! is_user_logged_in() ) {
			$can_view = false;
		} else {
			if ( ! um_is_user_himself() && ! UM()->Friends_API()->api()->is_friend( get_current_user_id(), um_get_requested_user() ) ) {
				$can_view = apply_filters( 'um_friends_not_friend_maybe_other', false, $data );
			}
		}
	}

	return $can_view;
}
add_filter( 'um_can_view_field_custom', 'um_friends_can_view_field', 10, 2 );


/**
 * Show field 'only for friends and followers',
 * case if not follower maybe friend
 *
 * @param bool $can_view
 * @param array $data
 *
 * @return bool
 */
function um_friends_not_follower_maybe_other( $can_view, $data ) {
	if ( isset( $data['public'] ) && $data['public'] == '-6' ) {
		if ( UM()->Friends_API()->api()->is_friend( get_current_user_id(), um_get_requested_user() ) ) {
			$can_view = true;
		}
	}

	return $can_view;
}
add_filter( 'um_followers_not_follower_maybe_other', 'um_friends_not_follower_maybe_other', 10, 2 );


/**
 * Make private messaging privacy
 *
 * @param array $options
 *
 * @return array
 */
function um_friends_messaging_privacy_options( $options ) {
	$options['friends'] = __( 'Friends', 'um-friends' );
	return $options;
}
add_filter( 'um_messaging_privacy_options', 'um_friends_messaging_privacy_options', 10, 1 );
add_filter( 'um_user_notes_privacy_options_dropdown', 'um_friends_messaging_privacy_options', 10, 1 );
add_filter( 'um_user_photos_privacy_options_dropdown', 'um_friends_messaging_privacy_options', 10, 1 );


/**
 * @param bool $can_view
 * @param string $privacy
 * @param int $user_id
 *
 * @return bool
 */
function um_user_notes_friends_privacy( $can_view, $privacy, $user_id ) {
	if ( 'friends' === $privacy && ! UM()->Friends_API()->api()->is_friend( $user_id, um_profile_id() ) ) {
		return false;
	}

	return $can_view;
}
add_filter( 'um_user_notes_custom_privacy', 'um_user_notes_friends_privacy', 10, 3 );


/**
 * @param bool $can_view
 * @param string $privacy
 * @param int $user_id
 * @param int $profile_id
 *
 * @return bool
 */
function um_user_photos_friends_privacy( $can_view, $privacy, $user_id, $profile_id ) {
	if ( 'friends' === $privacy && ! UM()->Friends_API()->api()->is_friend( $user_id, $profile_id ) ) {
		return false;
	}

	return $can_view;
}
add_filter( 'um_user_photos_custom_privacy', 'um_user_photos_friends_privacy', 10, 4 );


/**
 * @param array    $query
 * @param null|int $user_id
 * @param null|int $profile_id
 *
 * @return array
 */
function um_user_notes_add_friends_to_meta_query( $query, $user_id, $profile_id ) {
	if ( empty( $user_id ) ) {
		return $query;
	}

	if ( absint( $user_id ) === absint( $profile_id ) || UM()->Friends_API()->api()->is_friend( $user_id, $profile_id ) ) {
		$query[] = array(
			array(
				'key'     => '_privacy',
				'value'   => 'friends',
				'compare' => '=',
			),
		);
	}

	return $query;
}
add_filter( 'um_user_notes_change_meta_query', 'um_user_notes_add_friends_to_meta_query', 20, 3 );

/**
 * @param array    $query
 * @param null|int $user_id
 * @param null|int $profile_id
 *
 * @return array
 */
function um_user_photos_add_friends_to_meta_query( $query, $user_id, $profile_id ) {
	if ( empty( $user_id ) ) {
		return $query;
	}

	if ( absint( $user_id ) === absint( $profile_id ) || UM()->Friends_API()->api()->is_friend( $user_id, $profile_id ) ) {
		$query[] = array(
			array(
				'key'     => '_privacy',
				'value'   => 'friends',
				'compare' => '=',
			),
		);
	}

	return $query;
}
add_filter( 'um_user_photos_change_meta_query', 'um_user_photos_add_friends_to_meta_query', 20, 3 );

function um_user_photos_remove_friends_as_visible( $privacy ) {
	$privacy = array_flip( $privacy );
	unset( $privacy['friends'] );
	return array_keys( $privacy );
}
add_filter( 'um_user_photos_accessible_by_author_privacy', 'um_user_photos_remove_friends_as_visible' );
add_filter( 'um_user_notes_accessible_by_author_privacy', 'um_user_photos_remove_friends_as_visible' );

/**
 * Filters an array of albums current user can access.
 *
 * @param array $public_ids An array of public albums.
 *
 * @return array An array of albums current user can access.
 */
function um_user_photo_albums_public_for_friends( $public_ids ) {
	if ( is_user_logged_in() ) {

		// Get friends.
		$friends = UM()->Friends_API()->api()->friends( get_current_user_id() );
		if ( $friends && is_array( $friends ) ) {

			// Prepare friends array.
			$friends_ids = array();
			foreach ( $friends as $arr ) {
				$friends_ids[] = absint( $arr['user_id1'] ) === get_current_user_id() ? $arr['user_id2'] : $arr['user_id1'];
			}
			$author__in = array_unique( $friends_ids );

			// Get albums with the "Friends" privacy.
			$args = array(
				'author__in'  => $author__in,
				'fields'      => 'ids',
				'post_type'   => 'um_user_photos',
				'post_status' => 'publish',
				'meta_query'  => array(
					'privacy' => array(
						'relation' => 'OR',
						array(
							'key'     => '_privacy',
							'value'   => 'friends',
							'compare' => '=',
						),
					),
				),
			);

			$ids        = get_posts( $args );
			$public_ids = array_merge( $public_ids, $ids );
		}
	}
	return $public_ids;
}
add_filter( 'um_user_photo_albums_public_ids', 'um_user_photo_albums_public_for_friends' );


/**
 * Extend profile tabs
 *
 * @param array $tabs
 *
 * @return array
 */
function um_friends_add_tabs( $tabs ) {
	$tabs['friends'] = array(
		'name' => __( 'Friends', 'um-friends' ),
		'icon' => 'um-faicon-users',
	);

	return $tabs;
}
add_filter( 'um_profile_tabs', 'um_friends_add_tabs', 2000 );


/**
 * Add tabs based on user
 *
 * @param array $tabs
 *
 * @return array
 */
function um_friends_user_add_tab( $tabs ) {
	if ( empty( $tabs['friends'] ) ) {
		return $tabs;
	}

	$user_id = um_user( 'ID' );
	if ( ! $user_id ) {
		return $tabs;
	}

	$username = um_user( 'display_name' );
	if ( defined( 'UM_DEV_MODE' ) && UM_DEV_MODE && UM()->options()->get( 'enable_new_ui' ) ) {
		$tabs['friends']['subnav_default'] = 'myfriends';
		$tabs['friends']['subnav']         = array(
			'myfriends' => array(
				// translators: %s is the profile user's display name.
				'title'        => ( um_is_myprofile() ) ? __( 'My Friends', 'um-friends' ) : sprintf( __( '%s\'s friends', 'um-friends' ), $username ),
				'notifier'     => UM()->Friends_API()->api()->count_friends( $user_id, false ), // @todo searching and change for v3 .um-profile-friends
			),
		);

		if ( um_is_myprofile() ) {
			$new_reqs = UM()->Friends_API()->api()->count_friend_requests_received( $user_id );
			// Display number of requests on the friends tab
			$tabs['friends']['notifier']     = $new_reqs;
			$tabs['friends']['max_notifier'] = 10;

			$tabs['friends']['subnav']['friendreqs'] = array(
				'title'    => __( 'Friend Requests', 'um-friends' ),
				'notifier' => $new_reqs, // @todo searching and change for v3 .um-profile-friends-requests .um-friends-notf
			);
			$tabs['friends']['subnav']['sentreqs']   = array(
				'title'    => __( 'Friend Requests Sent', 'um-friends' ),
				'notifier' => UM()->Friends_API()->api()->count_friend_requests_sent( $user_id ), // @todo searching and change for v3 .um-profile-friends-requests-sent
			);
		}
	} else {
		// translators: %s is the profile user's display name.
		$myfriends  = ( um_is_myprofile() ) ? __( 'My Friends', 'um-friends' ) : sprintf( __( '%s\'s friends', 'um-friends' ), $username );
		$myfriends .= '<span class="um-profile-friends">' . UM()->Friends_API()->api()->count_friends( $user_id, false ) . '</span>';

		$tabs['friends']['subnav_default'] = 'myfriends';
		$tabs['friends']['subnav']         = array(
			'myfriends' => $myfriends,
		);

		if ( um_is_myprofile() ) {
			$new_reqs = UM()->Friends_API()->api()->count_friend_requests_received( $user_id );
			if ( $new_reqs > 0 ) {
				$class = 'um-friends-notf';
			} else {
				$class = '';
			}

			// Display number of requests on the friends tab
			$tabs['friends']['notifier']             = $new_reqs;
			$tabs['friends']['subnav']['friendreqs'] = __( 'Friend Requests', 'um-friends' ) . '<span class="um-profile-friends-requests ' . $class . '">' . $new_reqs . '</span>';
			$tabs['friends']['subnav']['sentreqs']   = __( 'Friend Requests Sent', 'um-friends' ) . '<span class="um-profile-friends-requests-sent">' . UM()->Friends_API()->api()->count_friend_requests_sent( $user_id ) . '</span>';
		}
	}

	return $tabs;
}
add_filter( 'um_user_profile_tabs', 'um_friends_user_add_tab', 1000 );


/**
 * Check if user can view user profile
 *
 * @param $can_view
 * @param int $user_id
 *
 * @return string
 */
function um_friends_can_view_main( $can_view, $user_id ) {
	if ( ! is_user_logged_in() || get_current_user_id() != $user_id ) {
		$is_private_case_old = UM()->user()->is_private_case( $user_id, __( 'Friends only', 'um-friends' ) );
		$is_private_case = UM()->user()->is_private_case( $user_id, 'friends' );
		if ( ( $is_private_case || $is_private_case_old ) && ! current_user_can( 'manage_options' ) ) { //Enable admin to be able to view
			$can_view = __( 'You must be a friend of this user to view their profile', 'um-friends' );
		}
	}

	return $can_view;
}
add_filter( 'um_profile_can_view_main', 'um_friends_can_view_main', 10, 2 );


/**
 * Test case to hide profile
 *
 * @param $default
 * @param $option
 * @param $user_id
 *
 * @return bool
 */
function um_friends_private_filter_hook( $default, $option, $user_id ) {
	// user selected this option in privacy
	if ( $option == 'friends' || $option == __( 'Friends only', 'um-friends' ) ) {
		if ( ! UM()->Friends_API()->api()->is_friend( $user_id, get_current_user_id() ) ) {
			return true;
		}
	}

	return $default;
}
add_filter( 'um_is_private_filter_hook', 'um_friends_private_filter_hook', 100, 3 );


/**
 * Case if user can message only with friends
 *
 * @param $restrict
 * @param $who_can_pm
 * @param $recipient
 *
 * @return bool
 */
function um_friends_can_message_restrict( $restrict, $who_can_pm, $recipient ) {
	// user selected this option in privacy
	if ( $who_can_pm == 'friends' ) {
		if ( ! UM()->Friends_API()->api()->is_friend( get_current_user_id(), $recipient ) ) {
			return true;
		}
	}

	return $restrict;
}
add_filter( 'um_messaging_can_message_restrict', 'um_friends_can_message_restrict', 10, 3 );

/**
 * @param string $content
 * @param int    $user_id
 * @param int    $post_id
 * @param string $status
 *
 * @return mixed
 */
function um_friends_activity_mention_integration( $content, $user_id, $post_id, $status ) {
	if ( ! UM()->options()->get( 'activity_friends_mention' ) ) {
		return $content;
	}

	$mention           = array();
	$mentioned_in_post = get_post_meta( $post_id, '_mentioned', true );
	$mentioned_in_post = $mentioned_in_post ? $mentioned_in_post : array();

	$friends = UM()->Friends_API()->api()->friends( $user_id );
	if ( ! empty( $friends ) ) {
		$friends_names = array();
		foreach ( $friends as $arr ) {
			if ( isset( $arr['user_id1'] ) ) {
				$user_id1 = absint( $arr['user_id1'] );

				if ( $user_id1 === $user_id ) {
					continue;
				}

				if ( array_key_exists( $user_id1, $friends_names ) ) {
					continue;
				}

				um_fetch_user( $user_id1 );
				$display_name = um_user( 'display_name' );
				if ( empty( $display_name ) ) {
					continue;
				}
				$friends_names[ $user_id1 ] = $display_name;
			}

			if ( isset( $arr['user_id2'] ) ) {
				$user_id2 = absint( $arr['user_id2'] );

				if ( $user_id2 === $user_id ) {
					continue;
				}

				if ( array_key_exists( $user_id2, $friends_names ) ) {
					continue;
				}

				um_fetch_user( $user_id2 );
				$display_name = um_user( 'display_name' );
				if ( empty( $display_name ) ) {
					continue;
				}
				$friends_names[ $user_id2 ] = $display_name;
			}
		}

		uasort(
			$friends_names,
			static function( $a, $b ) {
				return strlen( $b ) - strlen( $a );
			}
		);

		foreach ( $friends_names as $user_id1 => $name ) {
			preg_match( '/(^|\s)(@' . $name . ')($|\s)/um', $content, $matches );

			if ( empty( $matches[2] ) ) {
				continue;
			}

			$user_mentioned_in_post = false;
			if ( ! empty( $mentioned_in_post ) && in_array( $user_id1, $mentioned_in_post, true ) ) {
				$user_mentioned_in_post = true;
			}

			um_fetch_user( $user_id1 );
			$content = preg_replace( '/(^|\s)@(' . $name . ')($|\s)/um', '$1<a href="' . esc_url( um_user_profile_url() ) . '" class="um-link um-user-tag">$2</a>$3', $content, -1, $replacements );

			if ( false === $user_mentioned_in_post && ! empty( $replacements ) ) {
				do_action( 'um_friends_new_mention', $user_id, $user_id1, $post_id );
				$mention[] = $user_id1;
			}
		}
	}

	if ( ! empty( $mention ) ) {
		$mention = array_merge( $mentioned_in_post, $mention );
		update_post_meta( $post_id, '_mentioned', $mention );
	}

	return $content;
}
add_filter( 'um_activity_mention_integration', 'um_friends_activity_mention_integration', 10, 4 );


/**
 * Add options for profile tabs' privacy
 *
 * @param array $options
 *
 * @return array
 */
function um_friends_profile_tabs_privacy_options( $options ) {
	$options[7] = __( 'Only friends', 'um-friends' );

	// check if there is 'only followers' option
	if ( isset( $options[6] ) ) {
		$options[8] = __( 'Only friends and followers', 'um-friends' );
	}

	return $options;
}
add_filter( 'um_profile_tabs_privacy_list', 'um_friends_profile_tabs_privacy_options', 10, 1 );


/**
 * Show profile tab only for friends
 *
 * @param bool $can_view
 * @param int $privacy
 * @param string $tab
 * @param array $tab_data
 * @param int $user_id
 *
 * @return bool
 */
function um_friends_can_view_profile_tab( $can_view, $privacy, $tab, $tab_data, $user_id ) {
	if ( ! in_array( $privacy, [ 7, 8 ] ) ) {
		return $can_view;
	}

	if ( ! is_user_logged_in() ) {
		$can_view = false;
	} else {
		if ( get_current_user_id() == $user_id ) {
			$can_view = false;
		} else {
			if ( ! UM()->Friends_API()->api()->is_friend( get_current_user_id(), $user_id ) ) {
				$can_view = apply_filters( 'um_friends_profile_tab_not_friend_maybe_other', false, $privacy, $user_id );
			}
		}
	}

	return $can_view;
}
add_filter( 'um_profile_menu_can_view_tab', 'um_friends_can_view_profile_tab', 10, 5 );


/**
 * Show profile tab 'only for friends and followers',
 * case if not follower maybe friend
 *
 * @param bool $can_view
 * @param int $privacy
 * @param int $user_id
 *
 * @return bool
 */
function um_friends_profile_tab_not_follower_maybe_other( $can_view, $privacy, $user_id ) {
	if ( $privacy == 8 ) {
		if ( UM()->Friends_API()->api()->is_friend( get_current_user_id(), $user_id ) ) {
			$can_view = true;
		}
	}

	return $can_view;
}
add_filter( 'um_followers_profile_tab_not_follower_maybe_other', 'um_friends_profile_tab_not_follower_maybe_other', 10, 3 );
