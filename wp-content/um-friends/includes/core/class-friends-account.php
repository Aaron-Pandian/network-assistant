<?php
namespace um_ext\um_friends\core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Friends_Account
 * @package um_ext\um_friends\core
 */
class Friends_Account {

	/**
	 * Friends_Account constructor.
	 */
	public function __construct() {
		add_action( 'um_post_account_update', array( &$this, 'account_update' ) );

		add_filter( 'um_account_page_default_tabs_hook', array( &$this, 'account_notification_tab' ) );
		add_filter( 'um_account_content_hook_notifications', array( &$this, 'account_tab' ), 50, 2 );
		add_filter( 'um_account_notifications_tab_enabled', '__return_true' );

		add_filter( 'um_predefined_fields_hook', array( &$this, 'predefined_fields_hook' ), 11 );
		add_filter( 'um_account_tab_notifications_fields', array( &$this, 'add_notifications_fields' ), 11 );
	}

	/**
	 * Update Account action
	 */
	public function account_update() {
		/**
		 * issue helpscout#31301
		 */
		$current_tab = isset( $_POST['_um_account_tab'] ) ? sanitize_key( $_POST['_um_account_tab'] ) : null;
		if ( 'notifications' !== $current_tab ) {
			return;
		}

		$user_id = um_user( 'ID' );

		if ( defined( 'UM_DEV_MODE' ) && UM_DEV_MODE && UM()->options()->get( 'enable_new_ui' ) ) {
			if ( isset( $_POST['_enable_new_friend'] ) ) {
				update_user_meta( $user_id, '_enable_new_friend', true );
			} else {
				update_user_meta( $user_id, '_enable_new_friend', false );
			}

			if ( isset( $_POST['_enable_new_friend_request'] ) ) {
				update_user_meta( $user_id, '_enable_new_friend_request', true );
			} else {
				update_user_meta( $user_id, '_enable_new_friend_request', false );
			}
		} else {
			if ( isset( $_POST['_enable_new_friend'] ) ) {
				update_user_meta( $user_id, '_enable_new_friend', 'yes' );
			} else {
				update_user_meta( $user_id, '_enable_new_friend', 'no' );
			}

			if ( isset( $_POST['_enable_new_friend_request'] ) ) {
				update_user_meta( $user_id, '_enable_new_friend_request', 'yes' );
			} else {
				update_user_meta( $user_id, '_enable_new_friend_request', 'no' );
			}
		}
	}

	/**
	 * Add Notifications tab to account page
	 *
	 * @param array $tabs
	 * @return array
	 */
	public function account_notification_tab( $tabs ) {
		if ( defined( 'UM_DEV_MODE' ) && UM_DEV_MODE && UM()->options()->get( 'enable_new_ui' ) ) {
			return $tabs;
		}

		if ( empty( $tabs[400]['notifications'] ) ) {
			$tabs[400]['notifications'] = array(
				'icon'         => 'um-faicon-envelope',
				'title'        => __( 'Notifications', 'um-friends' ),
				'submit_title' => __( 'Update Notifications', 'um-friends' ),
			);
		}

		return $tabs;
	}

	public function account_tab( $output, $shortcode_args ) {
		if ( defined( 'UM_DEV_MODE' ) && UM_DEV_MODE && UM()->options()->get( 'enable_new_ui' ) ) {
			return $output;
		}

		if ( ! ( UM()->options()->get( 'new_friend_on' ) || UM()->options()->get( 'new_friend_request_on' ) ) ) {
			return $output;
		}

		if ( isset( $shortcode_args['_enable_new_friend'] ) && ! $shortcode_args['_enable_new_friend'] &&
		     isset( $shortcode_args['_enable_new_friend_request'] ) && ! $shortcode_args['_enable_new_friend_request'] ) {
			return $output;
		}

		$_enable_new_friend         = UM()->Friends_API()->api()->enabled_email( get_current_user_id(), '_enable_new_friend' );
		$_enable_new_friend_request = UM()->Friends_API()->api()->enabled_email( get_current_user_id(), '_enable_new_friend_request' );

		$show_new_friend = false;
		if ( ! isset( $shortcode_args['_enable_new_friend'] ) || $shortcode_args['_enable_new_friend'] ) {
			if ( UM()->options()->get( 'new_friend_on' ) ) {
				UM()->account()->add_displayed_field( '_enable_new_friend', 'notifications' );
				$show_new_friend = true;
			}
		}

		$show_new_friend_request = false;
		if ( ! isset( $shortcode_args['_enable_new_friend_request'] ) || $shortcode_args['_enable_new_friend_request'] ) {
			if ( UM()->options()->get( 'new_friend_request_on' ) ) {
				UM()->account()->add_displayed_field( '_enable_new_friend_request', 'notifications' );
				$show_new_friend_request = true;
			}
		}

		$t_args  = compact( '_enable_new_friend', '_enable_new_friend_request', 'show_new_friend', 'show_new_friend_request' );
		$output .= UM()->get_template( 'account-notifications.php', um_friends_plugin, $t_args );

		return $output;
	}

	public function predefined_fields_hook( $fields ) {
		if ( defined( 'UM_DEV_MODE' ) && UM_DEV_MODE && UM()->options()->get( 'enable_new_ui' ) ) {
			$fields['_enable_new_friend'] = array(
				'title'                    => __( 'I have got a new friend', 'ultimate-member' ),
				'metakey'                  => '_enable_new_friend',
				'type'                     => 'bool',
				'checkbox_label'           => __( 'I have got a new friend', 'um-followers' ),
				'checkbox_label_supported' => __( 'I have got a new friend', 'um-followers' ),
				'required'                 => 0,
				'public'                   => 1,
				'editable'                 => true,
				'default'                  => UM()->options()->get( 'new_friend_on' ) ? true : false,
				'account_only'             => true,
				'required_opt'             => array( 'new_friend_on', '1' ),
			);

			$fields['_enable_new_friend_request'] = array(
				'title'                    => __( 'I have got a new friend request', 'ultimate-member' ),
				'metakey'                  => '_enable_new_friend_request',
				'type'                     => 'bool',
				'checkbox_label'           => __( 'I have got a new friend request', 'um-followers' ),
				'checkbox_label_supported' => __( 'I have got a new friend request', 'um-followers' ),
				'required'                 => 0,
				'public'                   => 1,
				'editable'                 => true,
				'default'                  => UM()->options()->get( 'new_friend_request_on' ) ? true : false,
				'account_only'             => true,
				'required_opt'             => array( 'new_friend_request_on', '1' ),
			);

			if ( UM()->options()->get( 'new_friend_on' ) ) {
				UM()->account()->add_displayed_field( '_enable_new_friend', 'notifications' );
			}

			if ( UM()->options()->get( 'new_friend_request_on' ) ) {
				UM()->account()->add_displayed_field( '_enable_new_friend_request', 'notifications' );
			}
		}
		return $fields;
	}

	public function add_notifications_fields( $fields ) {
		if ( defined( 'UM_DEV_MODE' ) && UM_DEV_MODE && UM()->options()->get( 'enable_new_ui' ) ) {
			$fields .= ',_enable_new_friend,_enable_new_friend_request';
		}

		return $fields;
	}
}
