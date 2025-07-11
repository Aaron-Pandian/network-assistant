<?php
/**
 * Template for the UM Friends button. The list of user friends
 *
 * @version 2.2.6
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-friends/v3/button.php
 *
 * @var $redirect
 * @var $twobtn
 * @var $user_id1
 * @var $user_id2
 * @var $is_friend
 * @var $is_friend_pending
 * @var $can_friend_add
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$content = '';
if ( ! is_user_logged_in() ) {
	$content = UM()->frontend()::layouts()::button(
		__( 'Add Friend', 'um-friends' ),
		array(
			'type'    => 'button',
			'size'    => 's',
			'classes' => array( 'um-login-to-friend-btn' ),
			'title'   => __( 'Add Friend', 'um-friends' ),
			'data'    => array(
				'redirect' => $redirect,
			),
		)
	);
} elseif ( ! $is_friend ) {
	if ( $is_friend_pending ) {
		if ( $is_friend_pending === $user_id2 ) {
			// Pending respond
			if ( ! $twobtn ) {
				$accept_link = UM()->frontend()::layouts()::link(
					__( 'Confirm', 'um-friends' ),
					array(
						'type'          => 'raw',
						'size'          => 's',
						'design'        => 'primary',
						'icon_position' => '',
						'title'         => esc_attr__( 'Confirm', 'um-friends' ),
						'classes'       => array(
							'um-friend-accept-btn',
						),
						'data'          => array(
							'user_id' => $user_id1,
						),
					)
				);

				$reject_link = UM()->frontend()::layouts()::link(
					__( 'Delete Request', 'um-friends' ),
					array(
						'type'          => 'raw',
						'size'          => 's',
						'design'        => 'primary',
						'icon_position' => '',
						'title'         => esc_attr__( 'Delete Request', 'um-friends' ),
						'classes'       => array(
							'um-friend-reject-btn',
						),
						'data'          => array(
							'user_id' => $user_id1,
						),
					)
				);

				$content = UM()->frontend()::layouts()::dropdown_menu(
					'um-friend-respond-btn',
					array(
						$accept_link,
						$reject_link,
					),
					array(
						'type'         => 'button',
						'button_label' => __( 'Respond to Friend Request', 'um-friends' ),
						'width'        => 'toggle-button-width',
					)
				);
			} else {
				$content = UM()->frontend()::layouts()::button(
					__( 'Confirm', 'um-friends' ),
					array(
						'type'    => 'button',
						'size'    => 's',
						'classes' => array( 'um-friend-accept-btn' ),
						'title'   => __( 'Confirm', 'um-friends' ),
						'data'    => array(
							'user_id' => $user_id1,
						),
					)
				);
				$content .= UM()->frontend()::layouts()::button(
					__( 'Delete Request', 'um-friends' ),
					array(
						'type'    => 'button',
						'size'    => 's',
						'classes' => array( 'um-friend-reject-btn' ),
						'title'   => __( 'Delete Request', 'um-friends' ),
						'data'    => array(
							'user_id' => $user_id1,
						),
					)
				);
			}
		} else {
//			$cancel_link = UM()->frontend()::layouts()::link(
//				__( 'Cancel Request', 'um-friends' ),
//				array(
//					'type'          => 'raw',
//					'size'          => 's',
//					'design'        => 'primary',
//					'icon_position' => '',
//					'title'         => esc_attr__( 'Cancel Request', 'um-friends' ),
//					'classes'       => array(
//						'um-friends-cancel-request',
//					),
//					'data'          => array(
//						'user_id' => $user_id1,
//						'nonce'   => wp_create_nonce( 'um_friends_cancel_request' . $user_id1 ),
//					),
//				)
//			);
//
//			$content = UM()->frontend()::layouts()::dropdown_menu(
//				'um-friend-request-btn',
//				array(
//					$cancel_link,
//				),
//				array(
//					'type'         => 'button',
//					'button_label' => __( 'Friend Request Sent', 'um-friends' ),
//					'width'        => 'toggle-button-width',
//				)
//			);

			$content = UM()->frontend()::layouts()::button(
				__( 'Cancel Friend Request', 'um-friends' ),
				array(
					'type'    => 'button',
					'size'          => 's',
					'classes' => array( 'um-friends-cancel-request' ),
					'title'   => __( 'Cancel Friend Request', 'um-friends' ),
					'data'    => array(
						'user_id' => $user_id1,
						'nonce'   => wp_create_nonce( 'um_friends_cancel_request' . $user_id1 ),
					),
				)
			);
		}
	} elseif ( $can_friend_add ) {
		$content = UM()->frontend()::layouts()::button(
			__( 'Add Friend', 'um-friends' ),
			array(
				'type'    => 'button',
				'size'    => 's',
				'classes' => array( 'um-friend-btn' ),
				'title'   => __( 'Add Friend', 'um-friends' ),
				'data'    => array(
					'user_id' => $user_id1,
					'nonce'   => wp_create_nonce( 'um_friends_add' . $user_id1 ),
				),
			)
		);
	}
} else {
//	$content = UM()->frontend()::layouts()::button(
//		__( 'Friends', 'um-friends' ),
//		array(
//			'type'    => 'button',
//			'classes' => array( 'um-unfriend-btn' ),
//			'title'   => __( 'Friends', 'um-friends' ),
//			'data'    => array(
//				'user_id'  => $user_id1,
//				'friends'  => __( 'Friends', 'um-friends' ),
//				'unfriend' => __( 'Unfriend', 'um-friends' ),
//			),
//		)
//	);

	$unfriend_link = UM()->frontend()::layouts()::link(
		__( 'Unfriend', 'um-friends' ),
		array(
			'type'          => 'raw',
			'size'          => 's',
			'design'        => 'primary',
			'icon_position' => '',
			'title'         => esc_attr__( 'Unfriend', 'um-friends' ),
			'classes'       => array(
				'um-unfriend-btn',
			),
			'data'          => array(
				'user_id' => $user_id1,
				'nonce'   => wp_create_nonce( 'um_friends_unfriend' . $user_id1 ),
			),
		)
	);

	$content = UM()->frontend()::layouts()::dropdown_menu(
		'um-friend-request-btn',
		array(
			$unfriend_link,
		),
		array(
			'type'         => 'button',
			'button_label' => __( 'Friends', 'um-friends' ),
			'width'        => 'toggle-button-width',
		)
	);
}

echo wp_kses( $content, UM()->get_allowed_html( 'templates' ) );
