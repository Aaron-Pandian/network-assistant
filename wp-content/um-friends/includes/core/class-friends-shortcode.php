<?php
namespace um_ext\um_friends\core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Friends_Shortcode
 * @package um_ext\um_friends\core
 */
class Friends_Shortcode {

	/**
	 * Friends_Shortcode constructor.
	 */
	public function __construct() {
		add_shortcode( 'ultimatemember_friends_online', array( &$this, 'ultimatemember_friends_online' ) );
		add_shortcode( 'ultimatemember_friends', array( &$this, 'ultimatemember_friends' ) );
		add_shortcode( 'ultimatemember_friend_reqs', array( &$this, 'ultimatemember_friend_reqs' ) );
		add_shortcode( 'ultimatemember_friend_reqs_sent', array( &$this, 'ultimatemember_friend_reqs_sent' ) );
		add_shortcode( 'ultimatemember_friends_bar', array( &$this, 'ultimatemember_friends_bar' ) );
		add_shortcode( 'ultimatemember_friends_button', array( &$this, 'ultimatemember_friends_button' ) );
	}

	/**
	 * Shortcode [ultimatemember_friends_button] that displays the "Add Friend" button.
	 *
	 * The button is shown for logged-in users only.
	 * The button is hidden if the attribute `user_id` is the current user.
	 *
	 * Example: [ultimatemember_friends_button user_id="20"]
	 *
	 * @since 2.2.8
	 *
	 * @param array $atts {
	 *   Attributes of the shortcode.
	 *
	 *   @type int $post_id The post/page ID. Default current post/page ID.
	 *   @type int $user_id The user ID. Default requested user ID or the post/page author ID.
	 * }
	 *
	 * @return string
	 */
	public function ultimatemember_friends_button( $atts = array() ) {
		if ( ! is_user_logged_in() ) {
			return '';
		}

		$user_id2 = get_current_user_id();

		$args = shortcode_atts(
			array(
				'user_id' => um_get_requested_user(),
				'post_id' => get_the_ID(),
			),
			$atts,
			'ultimatemember_friends_button'
		);

		$args['user_id'] = absint( $args['user_id'] );
		$args['post_id'] = absint( $args['post_id'] );

		$user_obj = get_userdata( $args['user_id'] );
		if ( ! empty( $user_obj ) ) {
			$user_id1 = $args['user_id'];
		} elseif ( ! empty( $args['post_id'] ) ) {
			$post_obj = get_post( $args['post_id'] );
			if ( ! empty( $post_obj ) ) {
				$user_id1 = $post_obj->post_author;
			}
		}

		if ( ! empty( $user_id1 ) ) {
			wp_enqueue_script( 'um_friends' );
			wp_enqueue_style( 'um_friends' );

			return '<div class="um um-friends-button-shortcode">' . UM()->Friends_API()->api()->friend_button( $user_id1, $user_id2 ) . '</div>';
		}

		return '';
	}

	/**
	 * Shortcode that displays friends, who are online.
	 *
	 * Example: [ultimatemember_friends_online]
	 *
	 * @param array $args {
	 *   Attributes of the shortcode.
	 *
	 *   @type int    $user_id  The user ID. Default profile ID (on the User page) or current user ID.
	 *   @type string $style    Layout type. Default 'default'. Accepts 'avatars', 'default'.
	 *   @type int    $max      Number of friends to display. Default 12. Set 0 to show all friends.
	 * }
	 *
	 * @return string
	 */
	public function ultimatemember_friends_online( $args = array() ) {
		$args = shortcode_atts(
			array(
				'user_id' => um_is_core_page( 'user' ) ? um_profile_id() : get_current_user_id(),
				'style'   => 'default',
				'max'     => 12,
			),
			$args,
			'ultimatemember_friends_online'
		);

		$user_id = $args['user_id'];

		if ( 'avatars' === $args['style'] ) {
			$tpl = 'friends-mini';
		} else {
			$tpl = 'friends';
		}

		$online_ids = apply_filters( 'um_friends_online_users', array(), $args );
		if ( empty( $online_ids ) ) {
			return '';
		}

		$friends = UM()->Friends_API()->api()->friends( $user_id );
		if ( ! empty( $friends ) ) {
			foreach ( $friends as $k => $v ) {
				if ( empty( array_intersect( $online_ids, array_diff( $v, array( $user_id ) ) ) ) ) {
					unset( $friends[ $k ] );
				}
			}
		}

		if ( empty( $friends ) ) {
			return '';
		}

		wp_enqueue_script( 'um_friends' );
		wp_enqueue_style( 'um_friends' );

		$t_args = array_merge( $args, array( 'friends' => $friends ) );
		return UM()->get_template( "$tpl.php", um_friends_plugin, $t_args );
	}

	/**
	 * Shortcode
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public function ultimatemember_friends_bar( $args = array() ) {
		$args = shortcode_atts(
			array(
				'user_id' => um_profile_id(),
			),
			$args,
			'ultimatemember_friends_bar'
		);

		$user_id = absint( $args['user_id'] );

		$can_view = true;
		if ( ! is_user_logged_in() || get_current_user_id() !== $user_id ) {

			$is_private_case_old = UM()->user()->is_private_case( $user_id, __( 'Friends only', 'um-friends' ) );
			$is_private_case     = UM()->user()->is_private_case( $user_id, 'friends' );
			if ( $is_private_case || $is_private_case_old ) { // only friends can view my profile
				$can_view = false;
			}
		}

		wp_enqueue_script( 'um_friends' );
		wp_enqueue_style( 'um_friends' );

		$t_args = compact( 'args', 'can_view', 'user_id' );
		return UM()->get_template( 'friends-bar.php', um_friends_plugin, $t_args );
	}

	/**
	 * Shortcode
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public function ultimatemember_friends( $args = array() ) {
		$args = shortcode_atts(
			array(
				'user_id' => um_is_core_page( 'user' ) ? um_profile_id() : get_current_user_id(),
				'style'   => 'default',
				'max'     => 11,
			),
			$args,
			'ultimatemember_friends'
		);

		$max     = $args['max'];
		$user_id = absint( $args['user_id'] );

		$friends = UM()->Friends_API()->api()->friends( $user_id );
		$note    = '';
		if ( empty( $friends ) ) {
			$note = ( get_current_user_id() === $user_id ) ? esc_html__( 'You do not have any friends yet.', 'um-friends' ) : esc_html__( 'This user does not have any friends yet.', 'um-friends' );
		}

		if ( 'avatars' === $args['style'] ) {
			$tpl = 'friends-mini';
		} else {
			$tpl = 'friends';

			if ( empty( $note ) ) {
				$role      = UM()->roles()->get_priority_user_role( $user_id );
				$role_data = UM()->roles()->role_data( $role );

				if ( ! empty( $role_data['friends_max'] ) && $role_data['friends_max'] <= count( $friends ) ) {
					$note = __( 'You have reached a limit of friends.', 'um-friends' );
				}

				if ( empty( $friends ) ) {
					if ( get_current_user_id() === $user_id ) {
						$note = __( 'You do not have any friends yet.', 'um-friends' );
					} else {
						$note = __( 'This user does not have any friends yet.', 'um-friends' );
					}
				}
			}
		}

		wp_enqueue_script( 'um_friends' );
		wp_enqueue_style( 'um_friends' );

		$t_args = compact( 'args', 'friends', 'max', 'note', 'user_id' );
		return UM()->get_template( $tpl . '.php', um_friends_plugin, $t_args );
	}

	/**
	 * Shortcode
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public function ultimatemember_friend_reqs( $args = array() ) {
		$args = shortcode_atts(
			array(
				'user_id' => um_is_core_page( 'user' ) ? um_profile_id() : get_current_user_id(),
				'style'   => 'default',
				'max'     => 999,
			),
			$args,
			'ultimatemember_friend_reqs'
		);

		$max     = $args['max'];
		$user_id = absint( $args['user_id'] );

		if ( 'avatars' === $args['style'] ) {
			$tpl = 'friends-mini';
		} else {
			$tpl = 'friends';
		}

		$_is_reqs = true;
		$friends  = UM()->Friends_API()->api()->friend_reqs( $user_id );
		$note     = empty( $friends ) ? esc_html__( 'You do not have pending friend requests yet.', 'um-friends' ) : '';

		wp_enqueue_script( 'um_friends' );
		wp_enqueue_style( 'um_friends' );

		$t_args = compact( '_is_reqs', 'args', 'friends', 'max', 'note', 'user_id' );
		return UM()->get_template( $tpl . '.php', um_friends_plugin, $t_args );
	}

	/**
	 * Shortcode
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public function ultimatemember_friend_reqs_sent( $args = array() ) {
		$args = shortcode_atts(
			array(
				'user_id' => um_is_core_page( 'user' ) ? um_profile_id() : get_current_user_id(),
				'style'   => 'default',
				'max'     => 999,
			),
			$args,
			'ultimatemember_friend_reqs_sent'
		);

		$max     = $args['max'];
		$user_id = absint( $args['user_id'] );

		if ( 'avatars' === $args['style'] ) {
			$tpl = 'friends-mini';
		} else {
			$tpl = 'friends';
		}

		$_sent   = true;
		$friends = UM()->Friends_API()->api()->friend_reqs_sent( $user_id );
		$note    = empty( $friends ) ? esc_html__( 'You have not sent any friend requests yet.', 'um-friends' ) : '';

		wp_enqueue_script( 'um_friends' );
		wp_enqueue_style( 'um_friends' );

		$t_args = compact( '_sent', 'args', 'friends', 'max', 'note', 'user_id' );
		return UM()->get_template( $tpl . '.php', um_friends_plugin, $t_args );
	}
}
