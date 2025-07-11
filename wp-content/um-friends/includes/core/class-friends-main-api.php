<?php
namespace um_ext\um_friends\core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Friends_Main_API
 * @package um_ext\um_friends\core
 */
class Friends_Main_API {

	/**
	 * DB table name.
	 *
	 * @var string
	 */
	public $table_name = '';

	/**
	 * Friends_Main_API constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'um_friends';
	}

	/**
	 * Checks if user enabled email notification
	 *
	 * @param $user_id
	 *
	 * @return bool|int
	 */
	public function enabled_email( $user_id, $key ) {
		$_enable_new_friend = true;

		if ( get_user_meta( $user_id, $key, true ) == 'yes' ) {
			$_enable_new_friend = 1;
		} elseif ( get_user_meta( $user_id, $key, true ) == 'no' ) {
			$_enable_new_friend = 0;
		}

		return $_enable_new_friend;
	}

	/**
	 * Show the friends list URL
	 *
	 * @param $user_id
	 *
	 * @return bool|string
	 */
	public function friends_link( $user_id ) {
		return add_query_arg( 'profiletab', 'friends', um_user_profile_url( $user_id ) );
	}

	/**
	 * Show the friend button for two users
	 *
	 * @param int $user_id1 user profile ID
	 * @param int $user_id2 current user if logged in.
	 * @param bool $twobtn
	 *
	 * @return string
	 */
	public function friend_button( $user_id1, $user_id2, $twobtn = false ) {
		$res = '';

		if ( is_user_logged_in() && ! $this->can_friend( $user_id1, $user_id2 ) ) {
			return $res;
		}

		if ( defined( 'UM_DEV_MODE' ) && UM_DEV_MODE && UM()->options()->get( 'enable_new_ui' ) ) {
			$t_args = array(
				'redirect'          => '',
				'twobtn'            => $twobtn,
				'is_friend'         => false,
				'is_friend_pending' => false,
				'can_friend_add'    => false,
				'user_id1'          => absint( $user_id1 ),
				'user_id2'          => absint( $user_id2 ),
			);

			if ( ! is_user_logged_in() ) {
				if ( UM()->is_request( 'ajax' ) ) {
					if ( isset( $_REQUEST['post_refferer'] ) ) {
						$link = get_permalink( absint( $_REQUEST['post_refferer'] ) );
					}

					if ( empty( $link ) ) {
						$link = um_get_core_page( 'members' );
					}
				} else {
					$link = UM()->permalinks()->get_current_url();
				}

				$t_args['redirect'] = add_query_arg( 'redirect_to', $link, um_get_core_page( 'login' ) );
			} else {
				$t_args['is_friend'] = $this->is_friend( $user_id1, $user_id2 );
				if ( ! $t_args['is_friend'] ) {
					$t_args['is_friend_pending'] = $this->is_friend_pending( $user_id1, $user_id2 );
					$t_args['can_friend_add']    = $this->can_friend_add( $user_id1, $user_id2 );
				}
			}

			wp_enqueue_script( 'um_friends' );
			wp_enqueue_style( 'um_friends' );

			$res = UM()->get_template( 'v3/button.php', um_friends_plugin, $t_args );
			return apply_filters( 'um_friend_button_html', $res, $user_id1, $user_id2, $twobtn );
		}

		if ( ! is_user_logged_in() ) {
			if ( UM()->is_request( 'ajax' ) ) {
				if ( isset( $_REQUEST['post_refferer'] ) ) {
					$link = get_permalink( absint( $_REQUEST['post_refferer'] ) );
				}

				if ( empty( $link ) ) {
					$link = um_get_core_page( 'members' );
				}
			} else {
				$link = UM()->permalinks()->get_current_url();
			}

			$redirect = add_query_arg( 'redirect_to', $link, um_get_core_page( 'login' ) );
			$res = '<a href="' . esc_url( $redirect ) . '" class="um-login-to-friend-btn um-button um-alt">' . esc_html__( 'Add Friend', 'um-friends' ) . '</a>';
			return $res;
		}

		if ( ! $this->is_friend( $user_id1, $user_id2 ) ) {
			if ( $pending = $this->is_friend_pending( $user_id1, $user_id2 ) ) {

				if ( $pending == $user_id2 ) { // User should respond

					if ( $twobtn == false ) {

						$res = '<div class="um-friend-respond-zone">
							<a href="javascript:void(0);" class="um-friend-respond-btn um-button um-alt" data-user_id="' . esc_attr( $user_id1 ) . '">' . esc_html__( 'Respond to Friend Request', 'um-friends' ) . '</a>';

						$items = array(
							'confirm'   => '<a href="javascript:void(0);" class="um-friend-accept-btn" data-user_id="' . esc_attr( $user_id1 ) . '">' . esc_html__( 'Confirm', 'um-friends' ). '</a>',
							'delete'    => '<a href="javascript:void(0);" class="um-friend-reject-btn" data-user_id="' . esc_attr( $user_id1 ) . '">'. esc_html__( 'Delete Request', 'um-friends' ). '</a>',
							'cancel'    => '<a href="javascript:void(0);" class="um-dropdown-hide">' . esc_html__( 'Cancel', 'um-friends' ) . '</a>',
						);

						ob_start();
						UM()->profile()->new_ui( 'bc', '.um-friend-respond-zone', 'click', $items );
						$res .= ob_get_clean();

						$res .= '</div>';

					} else {
						$res = '<a href="javascript:void(0);" class="um-friend-accept-btn um-button" data-user_id="' . esc_attr( $user_id1 ) . '">' . esc_html__( 'Confirm', 'um-friends' ). '</a>';
						$res .= '&nbsp;&nbsp;<a href="javascript:void(0);" class="um-friend-reject-btn um-button um-alt" data-user_id="' . esc_attr( $user_id1 ) . '">'. esc_html__( 'Delete Request', 'um-friends' ). '</a>';
					}

				} else {
					$res = '<a href="javascript:void(0);" class="um-friend-pending-btn um-button um-alt" data-cancel-friend-request="' . esc_attr__( 'Cancel Friend Request', 'um-friends' ) . '" data-pending-friend-request="' . esc_attr__( 'Friend Request Sent', 'um-friends' ) . '" data-user_id="' . esc_attr( $user_id1 ) . '">' . esc_html__( 'Friend Request Sent', 'um-friends' ) . '</a>';
				}

			} elseif ( $this->can_friend_add( $user_id1, $user_id2 ) ) {
				$res = '<a href="javascript:void(0);" class="um-friend-btn um-button um-alt" data-user_id="' . esc_attr( $user_id1 ) . '">' . esc_html__( 'Add Friend', 'um-friends' ). '</a>';
			}
		} else {
			$res = '<a href="javascript:void(0);" class="um-unfriend-btn um-button um-alt" data-user_id="' . esc_attr( $user_id1 ) . '" data-friends="' . esc_attr__( 'Friends', 'um-friends' ) . '"  data-unfriend="' . esc_attr__( 'Unfriend', 'um-friends' ) . '">' . esc_html__( 'Friends', 'um-friends' ) . '</a>';
		}

		return apply_filters( 'um_friend_button_html', $res, $user_id1, $user_id2, $twobtn );
	}

	/**
	 * If user can friend
	 *
	 * @param $user_id1
	 * @param $user_id2
	 * @return bool
	 */
	public function can_friend( $user_id1, $user_id2 ) {
		if ( ! is_user_logged_in() ) {
			return true;
		}

		$roles1 = UM()->roles()->get_all_user_roles( $user_id1 );

		$role2      = UM()->roles()->get_priority_user_role( $user_id2 );
		$role_data2 = UM()->roles()->role_data( $role2 );
		/** This filter is documented in ultimate-member/includes/core/class-roles-capabilities.php */
		$role_data2 = apply_filters( 'um_user_permissions_filter', $role_data2, $user_id2 );

		if ( ! $role_data2['can_friend'] ) {
			return false;
		}

		if ( ! empty( $role_data2['can_friend_roles'] ) &&
			 ( empty( $roles1 ) || count( array_intersect( $roles1, maybe_unserialize( $role_data2['can_friend_roles'] ) ) ) <= 0 ) ) {
			return false;
		}

		if ( $user_id1 != $user_id2 && is_user_logged_in() ) {
			return true;
		}

		return false;
	}

	/**
	 * If user can add friend.
	 * Depends on user role option "Friends limit"
	 *
	 * @since  2.1.7
	 *
	 * @param  integer $user_id1
	 * @param  integer $user_id2
	 * @return boolean
	 */
	public function can_friend_add( $user_id1, $user_id2 ) {
		if ( ! $this->can_friend( $user_id1, $user_id2 ) ) {
			return false;
		}

		$role2      = UM()->roles()->get_priority_user_role( $user_id2 );
		$role_data2 = UM()->roles()->role_data( $role2 );
		if ( $role_data2 ) {
			/** This filter is documented in ultimate-member/includes/core/class-roles-capabilities.php */
			$role_data2 = apply_filters( 'um_user_permissions_filter', $role_data2, $user_id2 );
		}

		return empty( $role_data2['friends_max'] ) || $role_data2['friends_max'] > $this->count_friends_plain( $user_id2 );
	}

	/**
	 * Get the count of friends
	 *
	 * @param int $user_id
	 * @return null|string
	 */
	public function count_friends_plain( $user_id = 0 ) {
		global $wpdb;

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->prefix}um_friends
				WHERE status = 1 AND
					  ( user_id1= %d OR user_id2 = %d )",
				$user_id,
				$user_id
			)
		);

		return $count;
	}

	/**
	 * Get the count of received requests
	 *
	 * @param int $user_id
	 * @return int
	 */
	public function count_friend_requests_received( $user_id = 0 ) {
		global $wpdb;

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->prefix}um_friends
				WHERE status = 0 AND
					  user_id1 = %d",
				$user_id
			)
		);

		return absint( $count );
	}

	/**
	 * Get the count of sent requests
	 *
	 * @param int $user_id
	 * @return int
	 */
	public function count_friend_requests_sent( $user_id = 0 ) {
		global $wpdb;

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->prefix}um_friends
				WHERE status = 0 AND
					  user_id2 = %d",
				$user_id
			)
		);

		return absint( $count );
	}

	/**
	 * Get the count of friends in nice format
	 *
	 * @param int $user_id
	 * @param bool $html
	 *
	 * @return string
	 */
	public function count_friends( $user_id = 0, $html = true ) {
		$count = $this->count_friends_plain( $user_id );
		if ( $html ) {
			return '<span class="um-ajax-count-friends">' . number_format( $count ) . '</span>';
		}
		return number_format( $count );
	}

	/**
	 * Add a friend action.
	 *
	 * @param int $user_id1
	 * @param int $user_id2
	 * @return bool|int
	 */
	public function add( $user_id1, $user_id2 ) {
		global $wpdb;

		// if already friends do not add
		if ( $this->is_friend( $user_id1, $user_id2 ) ) {
			return false;
		}

		$result = $wpdb->insert(
			$this->table_name,
			array(
				'time'     => current_time( 'mysql' ),
				'user_id1' => $user_id1,
				'user_id2' => $user_id2,
				'status'   => 0,
			),
			array(
				'%s',
				'%d',
				'%d',
				'%d',
			)
		);

		return $result;
	}

	/**
	 * Approve friend.
	 *
	 * @param int $user_id1
	 * @param int $user_id2
	 */
	public function approve( $user_id1, $user_id2 ) {
		global $wpdb;

		// if already friends do not add
		if ( $this->is_friend( $user_id1, $user_id2 ) ) {
			return;
		}

		$wpdb->update(
			$this->table_name,
			array(
				'status' => 1,
			),
			array(
				'user_id1' => $user_id2,
				'user_id2' => $user_id1,
			),
			array(
				'%d',
			),
			array(
				'%d',
				'%d',
			)
		);
	}

	/**
	 * Removes a friend connection.
	 *
	 * @param int $user_id1
	 * @param int $user_id2
	 */
	public function remove( $user_id1, $user_id2 ) {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}um_friends
				WHERE ( user_id1 = %d AND user_id2 = %d ) OR
					  ( user_id1 = %d AND user_id2 = %d )",
				$user_id2,
				$user_id1,
				$user_id1,
				$user_id2
			)
		);
	}

	/**
	 * Cancel a pending friend connection.
	 *
	 * @param int $user_id1
	 * @param int $user_id2
	 * @return bool
	 */
	public function cancel( $user_id1, $user_id2 ) {
		global $wpdb;

		// Not applicable to pending requests
		if ( $this->is_friend( $user_id1, $user_id2 ) ) {
			return false;
		}

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}um_friends
				WHERE status = 0 AND
					  ( ( user_id1 = %d AND user_id2 = %d ) OR
						( user_id1 = %d AND user_id2 = %d ) )",
				$user_id2,
				$user_id1,
				$user_id1,
				$user_id2
			)
		);

		return true;
	}

	/**
	 * Checks if user is friend of another user.
	 *
	 * @param int $user_id1
	 * @param int $user_id2
	 * @return bool
	 */
	public function is_friend( $user_id1, $user_id2 ) {
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user_id1
				FROM {$wpdb->prefix}um_friends
				WHERE status = 1 AND
					  ( ( user_id1 = %d AND user_id2 = %d ) OR
						( user_id1 = %d AND user_id2 = %d ) )
				LIMIT 1",
				$user_id2,
				$user_id1,
				$user_id1,
				$user_id2
			)
		);

		return $results && isset( $results[0] );
	}

	/**
	 * Checks if user is pending friend of another user
	 *
	 * @param int $user_id1
	 * @param int $user_id2
	 * @return int|bool
	 */
	public function is_friend_pending( $user_id1, $user_id2 ) {
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user_id1
				FROM {$wpdb->prefix}um_friends
				WHERE status = 0 AND
					  ( ( user_id1 = %d AND user_id2 = %d ) OR
						( user_id1 = %d AND user_id2 = %d ) )
				LIMIT 1",
				$user_id2,
				$user_id1,
				$user_id1,
				$user_id2
			)
		);

		if ( $results && isset( $results[0] ) ) {
			return absint( $results[0]->user_id1 );
		}

		return false;
	}

	/**
	 * Get friends as array
	 *
	 * @param int $user_id1
	 * @return array|bool|null|object
	 */
	public function friends( $user_id1 ) {
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user_id1,
					user_id2
				FROM {$wpdb->prefix}um_friends
				WHERE status = 1 AND
					  ( user_id1 = %d OR user_id2 = %d )
				ORDER BY time DESC",
				$user_id1,
				$user_id1
			),
			ARRAY_A
		);

		if ( $results ) {
			return $results;
		}

		return false;
	}

	/**
	 * Get friend requests as array.
	 *
	 * @param int $user_id1
	 * @return array|bool|null|object
	 */
	public function friend_reqs( $user_id1 ) {
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user_id2
				FROM {$wpdb->prefix}um_friends
				WHERE status = 0 AND
					  user_id1 = %d
				ORDER BY time DESC",
				$user_id1
			),
			ARRAY_A
		);

		if ( $results ) {
			return $results;
		}

		return false;
	}

	/**
	 * Get friend requests as array.
	 *
	 * @param int $user_id1
	 * @return array|bool|null|object
	 */
	public function friend_reqs_sent( $user_id1 ) {
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user_id1
				FROM {$wpdb->prefix}um_friends
				WHERE status = 0 AND
					  user_id2 = %d
				ORDER BY time DESC",
				$user_id1
			),
			ARRAY_A
		);

		if ( $results ) {
			return $results;
		}

		return false;
	}

	/**
	 * AJAX Approve friend request
	 */
	public function ajax_friends_approve() {
		UM()->check_ajax_nonce();

		// phpcs:disable WordPress.Security.NonceVerification
		if ( ! isset( $_POST['user_id'] ) ) {
			wp_send_json_error( __( 'Invalid user ID.', 'um-friends' ) );
		}
		$user_id = absint( $_POST['user_id'] );
		// phpcs:enable WordPress.Security.NonceVerification

		$user_id2 = get_current_user_id();

		if ( empty( $user_id ) || ! is_numeric( $user_id ) ) {
			wp_send_json_error( __( 'Invalid user ID.', 'um-friends' ) );
		}

		if ( ! $this->can_friend( $user_id, $user_id2 ) ) {
			wp_send_json_error( __( 'You haven\'t capabilities to be a friends.', 'um-friends' ) );
		}

		if ( $this->is_friend( $user_id, $user_id2 ) ) {
			wp_send_json_error( __( 'You are friends already.', 'um-friends' ) );
		}

		if ( ! $this->is_friend_pending( $user_id, $user_id2 ) ) {
			wp_send_json_error( __( 'There isn\'t a friends request to approve.', 'um-friends' ) );
		}

		$this->approve( $user_id, $user_id2 );

		$output             = array();
		$output['btn']      = $this->friend_button( $user_id, $user_id2 );
		$output['friends']  = $this->count_friends( $user_id2, false );
		$output['friends2'] = $this->count_friends( $user_id, false );
		$output['received'] = $this->count_friend_requests_received( $user_id2 );

		do_action( 'um_friends_after_user_friend', $user_id, $user_id2 );

		wp_send_json_success( $output );
	}

	/**
	 * AJAX Add friend.
	 */
	public function ajax_friends_add() {
		// phpcs:disable WordPress.Security.NonceVerification
		if ( ! isset( $_POST['user_id'] ) ) {
			wp_send_json_error( __( 'Invalid user ID', 'um-friends' ) );
		}
		$user_id = absint( $_POST['user_id'] );
		// phpcs:enable WordPress.Security.NonceVerification

		if ( defined( 'UM_DEV_MODE' ) && UM_DEV_MODE && UM()->options()->get( 'enable_new_ui' ) ) {
			if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'um_friends_add' . $user_id ) ) {
				wp_send_json_error( __( 'Wrong nonce', 'um-messaging' ) );
			}
		} else {
			UM()->check_ajax_nonce();
		}

		$user_id2 = get_current_user_id();

		if ( empty( $user_id ) || ! is_numeric( $user_id ) ) {
			wp_send_json_error( __( 'Invalid user ID', 'um-friends' ) );
		}

		if ( ! $this->can_friend( $user_id, $user_id2 ) ) {
			wp_send_json_error( __( 'You haven\'t capabilities to be a friends.', 'um-friends' ) );
		}

		if ( $this->is_friend( $user_id, $user_id2 ) ) {
			wp_send_json_error( __( 'You are friends already.', 'um-friends' ) );
		}

		if ( $this->is_friend_pending( $user_id, $user_id2 ) ) {
			wp_send_json_error( __( 'There is a friends request to approve. You cannot add friend directly.', 'um-friends' ) );
		}

		$this->add( $user_id, $user_id2 );

		$output                  = array();
		$output['btn']           = $this->friend_button( $user_id, $user_id2 ); // re-init friends button between users
		$output['requests_sent'] = $this->count_friend_requests_sent( $user_id2 );

		do_action( 'um_friends_after_user_friend_request', $user_id, $user_id2 );

		wp_send_json_success( $output );
	}

	/**
	 * AJAX UnFriend.
	 */
	public function ajax_friends_unfriend() {
		UM()->check_ajax_nonce();

		// phpcs:disable WordPress.Security.NonceVerification
		if ( ! isset( $_POST['user_id'] ) ) {
			wp_send_json_error( __( 'Invalid user ID', 'um-friends' ) );
		}
		$user_id = absint( $_POST['user_id'] );
		// phpcs:enable WordPress.Security.NonceVerification

		$user_id2 = get_current_user_id();

		if ( empty( $user_id ) || ! is_numeric( $user_id ) ) {
			wp_send_json_error( __( 'Invalid user ID', 'um-friends' ) );
		}

		if ( ! $this->can_friend( $user_id, $user_id2 ) ) {
			wp_send_json_error( __( 'You haven\'t capabilities to be a friends.', 'um-friends' ) );
		}

		if ( ! $this->is_friend( $user_id, $user_id2 ) ) {
			wp_send_json_error( __( 'You aren\'t friends already.', 'um-friends' ) );
		}

		$this->remove( $user_id, $user_id2 );

		$output             = array();
		$output['btn']      = $this->friend_button( $user_id, $user_id2 );
		$output['friends']  = $this->count_friends( $user_id2, false );
		$output['received'] = $this->count_friend_requests_received( $user_id2 );

		do_action( 'um_friends_after_user_unfriend', $user_id, $user_id2 );

		wp_send_json_success( $output );
	}

	/**
	 * AJAX cancel friend's request
	 */
	public function ajax_friends_cancel_request() {
		// phpcs:disable WordPress.Security.NonceVerification
		if ( ! isset( $_POST['user_id'] ) ) {
			wp_send_json_error( __( 'Invalid user ID', 'um-friends' ) );
		}
		$user_id = absint( $_POST['user_id'] );
		// phpcs:enable WordPress.Security.NonceVerification

		if ( defined( 'UM_DEV_MODE' ) && UM_DEV_MODE && UM()->options()->get( 'enable_new_ui' ) ) {
			if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'um_friends_cancel_request' . $user_id ) ) {
				wp_send_json_error( __( 'Wrong nonce', 'um-messaging' ) );
			}
		} else {
			UM()->check_ajax_nonce();
		}

		$user_id2 = get_current_user_id();

		if ( empty( $user_id ) || ! is_numeric( $user_id ) ) {
			wp_send_json_error( __( 'Invalid user ID', 'um-friends' ) );
		}

		if ( ! $this->can_friend( $user_id, $user_id2 ) ) {
			wp_send_json_error( __( 'You haven\'t capabilities to be a friends.', 'um-friends' ) );
		}

		if ( ! $this->is_friend_pending( $user_id, $user_id2 ) ) {
			wp_send_json_error( __( 'There isn\'t a friends request to cancel.', 'um-friends' ) );
		}

		$this->cancel( $user_id, $user_id2 );

		$output                  = array();
		$output['btn']           = $this->friend_button( $user_id, $user_id2 );
		$output['requests_sent'] = $this->count_friend_requests_sent( $user_id2 );
		$output['friends']       = $this->count_friends( $user_id2, false );

		do_action( 'um_friends_after_user_cancel_request', $user_id, $user_id2 );

		wp_send_json_success( $output );
	}
}
