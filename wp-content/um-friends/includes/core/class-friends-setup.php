<?php
namespace um_ext\um_friends\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Friends_Setup
 * @package um_ext\um_friends\core
 */
class Friends_Setup {


	/**
	 * @var array
	 */
	var $settings_defaults;


	/**
	 * Friends_Setup constructor.
	 */
	function __construct() {
		//settings defaults
		$this->settings_defaults = array(
			'profile_tab_friends'           => 1,
			'profile_tab_friends_privacy'   => 0,
			'friends_show_stats'            => 1,
			'friends_show_button'           => 1,
			'new_friend_request_on'         => 1,
			'new_friend_request_sub'        => '{friend} wants to be friends with you on {site_name}',
			'new_friend_request'            => 'Hi {receiver},<br /><br />' .
			                                   '{friend} has just sent you a friend request on {site_name}.<br /><br />' .
			                                   'View their profile to accept/reject this friendship request:<br />' .
			                                   '{friend_profile}<br /><br />' .
			                                   'This is an automated notification from {site_name}. You do not need to reply.',
			'new_friend_on'                 => 1,
			'new_friend_sub'                => '{friend} has accepted your friend request',
			'new_friend'                    => 'Hi {receiver},<br /><br />' .
			                                   'You are now friends with {friend} on {site_name}.<br /><br />' .
			                                   'View their profile:<br />' .
			                                   '{friend_profile}<br /><br />' .
			                                   'This is an automated notification from {site_name}. You do not need to reply.',
		);

		$notification_types_templates = array(
			'new_friend_request'    => __( '<strong>{member}</strong> has sent you a friendship request', 'um-friends' ),
			'new_friend'            => __( '<strong>{member}</strong> has accepted your friendship request', 'um-friends' ),
		);

		foreach( $notification_types_templates as $k => $template ) {
			$this->settings_defaults[ 'log_' . $k ] = 1;
			$this->settings_defaults[ 'log_' . $k . '_template' ] = $template;
		}
	}


	/**
	 *
	 */
	function set_default_settings() {
		$options = get_option( 'um_options', array() );

		foreach ( $this->settings_defaults as $key => $value ) {
			//set new options to default
			if ( ! isset( $options[ $key ] ) ) {
				$options[ $key ] = $value;
			}

		}

		update_option( 'um_options', $options );
	}


	/**
	 * sql setup
	 */
	function sql_setup() {
		global $wpdb;

		if ( get_option( 'ultimatemember_friends_db' ) == um_friends_version ) {
			return;
		}

		$charset_collate = $wpdb->get_charset_collate();

		/*
		 * Pay an attention if the customer has active strict mode in the database
		 * the table cannot be created. The reason for this not supporting strict mode by WordPress by default.
		 * see ticket https://core.trac.wordpress.org/ticket/8857#comment:19
		 *
		 * For strict mode the datetime value can be in the range from 1000-01-01 00:00:00 to 9999-12-31 23:59:59
		 * ref.: https://www.mysqltutorial.org/mysql-datetime/
		 *
		 * We support wp.org logic because it's plugin for WordPress. So please temporarily disable strict-mode or create DB table
		 * manually via hosting CPanel
		 */
		$sql = "CREATE TABLE {$wpdb->prefix}um_friends (
id int(11) unsigned NOT NULL auto_increment,
time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
user_id1 int(11) unsigned NOT NULL,
user_id2 int(11) unsigned NOT NULL,
status tinyint(1) unsigned NOT NULL DEFAULT 0,
PRIMARY KEY  (id)
) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		update_option('ultimatemember_friends_db', um_friends_version );
	}


	/**
	 *
	 */
	function run_setup() {
		$this->sql_setup();
		$this->set_default_settings();
	}
}