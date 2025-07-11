<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array    $query_args
 * @param int      $user_id
 * @param null|int $wall_id
 * @param array    $metarows
 *
 * @return array
 */
function um_friends_user_notes_activity_query_privacy( $query_args, $user_id, $wall_id, $metarows ) {
	if ( empty( $user_id ) ) {
		return $query_args;
	}

	if ( in_array( 'friends', $metarows, true ) ) {
		if ( ! empty( $wall_id ) ) {
			if ( UM()->Friends_API()->api()->is_friend( $wall_id, $user_id ) ) {
				$query_args[] = array( // if my friend
					array(
						'key'     => 'um_note_privacy',
						'value'   => 'friends',
						'compare' => '=',
					),
				);
			}
		} else {
			$friends_array = array();

			$friends = UM()->Friends_API()->api()->friends( $user_id );
			if ( $friends && is_array( $friends ) ) {
				foreach ( $friends as $friend ) {
					$friends_array[] = $friend['user_id1'];
					$friends_array[] = $friend['user_id2'];
				}
				$friends_array = array_unique( $friends_array ); // friends and me
			}

			if ( ! empty( $friends_array ) ) {
				$query_args[] = array( // if not my friend
					'relation' => 'AND',
					array(
						'key'     => 'um_note_privacy',
						'value'   => 'friends',
						'compare' => '=',
					),
					array(
						'key'     => '_wall_id',
						'value'   => $friends_array,
						'compare' => 'IN',
					),
				);
			}
		}
	}

	return $query_args;
}
add_filter( 'um_user_notes_activity_query_privacy', 'um_friends_user_notes_activity_query_privacy', 10, 4 );

/**
 * @param array $query_args
 * @param int $user_id
 * @param null|int $wall_id
 * @param array $metarows
 *
 * @return array
 */
function um_friends_user_photos_activity_query_privacy( $query_args, $user_id, $wall_id, $metarows ) {
	if ( empty( $user_id ) || ! empty( $wall_id ) ) {
		return $query_args;
	}

	if ( in_array( 'friends', $metarows, true ) ) {
		$friends_array = array();

		$friends = UM()->Friends_API()->api()->friends( $user_id );
		if ( $friends && is_array( $friends ) ) {
			foreach ( $friends as $friend ) {
				$friends_array[] = $friend['user_id1'];
				$friends_array[] = $friend['user_id2'];
			}
			$friends_array = array_unique( $friends_array ); // friends and me
		}

		if ( ! empty( $friends_array ) ) {
			$query_args[] = array( // if not my friend
				'relation' => 'AND',
				array(
					'key'     => 'um_user_photos_privacy',
					'value'   => 'friends',
					'compare' => '=',
				),
				array(
					'key'     => '_wall_id',
					'value'   => $friends_array,
					'compare' => 'IN',
				),
			);
		}
	}

	return $query_args;
}
add_filter( 'um_user_photos_activity_query_privacy', 'um_friends_user_photos_activity_query_privacy', 10, 4 );

function um_friends_user_photos_activity_post_can_view( $can_view, $post_id ) {
	if ( ! $can_view ) {
		return $can_view;
	}

	$photos_privacy = get_post_meta( $post_id, 'um_user_photos_privacy', true );
	if ( 'friends' === $photos_privacy ) {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$wall_id = UM()->Activity_API()->api()->get_wall( $post_id );

		if ( absint( $wall_id ) === get_current_user_id() ) {
			return $can_view;
		}

		if ( ! UM()->Friends_API()->api()->is_friend( $wall_id, get_current_user_id() ) ) {
			return false;
		}
	}

	return $can_view;
}
add_filter( 'um_user_photos_activity_post_can_view', 'um_friends_user_photos_activity_post_can_view', 10, 2 );

function um_friends_user_notes_activity_post_can_view( $can_view, $post_id ) {
	if ( ! $can_view ) {
		return $can_view;
	}

	$note_privacy = get_post_meta( $post_id, 'um_note_privacy', true );
	if ( 'friends' === $note_privacy ) {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$wall_id = UM()->Activity_API()->api()->get_wall( $post_id );

		if ( absint( $wall_id ) === get_current_user_id() ) {
			return $can_view;
		}

		if ( ! UM()->Friends_API()->api()->is_friend( $wall_id, get_current_user_id() ) ) {
			return false;
		}
	}

	return $can_view;
}
add_filter( 'um_user_notes_activity_post_can_view', 'um_friends_user_notes_activity_post_can_view', 10, 2 );

/**
 * @param array    $privacy_array
 * @param int      $user_id
 * @param null|int $wall_id
 * @param array    $metarows
 *
 * @return array
 */
function um_friends_user_photos_notes_activity_query_privacy_array( $privacy_array, $user_id, $wall_id, $metarows ) {
	if ( empty( $user_id ) || empty( $wall_id ) ) {
		return $privacy_array;
	}

	if ( in_array( 'friends', $metarows, true ) ) {
		if ( UM()->Friends_API()->api()->is_friend( $wall_id, $user_id ) ) {
			$privacy_array[] = 'friends';
		}
	}

	return $privacy_array;
}
add_filter( 'um_user_photos_activity_query_privacy_array', 'um_friends_user_photos_notes_activity_query_privacy_array', 10, 4 );
add_filter( 'um_user_notes_activity_query_privacy_array', 'um_friends_user_photos_notes_activity_query_privacy_array', 10, 4 );
