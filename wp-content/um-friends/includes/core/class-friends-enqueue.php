<?php
namespace um_ext\um_friends\core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Friends_Enqueue
 * @package um_ext\um_friends\core
 */
class Friends_Enqueue {

	/**
	 * Friends_Enqueue constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( &$this, 'wp_enqueue_scripts' ), 9999 );
		add_action( 'enqueue_block_assets', array( &$this, 'block_editor' ), 11 );
	}

	/**
	 * Register custom friends scripts
	 */
	public function wp_enqueue_scripts() {
		$suffix = UM()->frontend()->enqueue()::get_suffix();

		if ( defined( 'UM_DEV_MODE' ) && UM_DEV_MODE && UM()->options()->get( 'enable_new_ui' ) ) {
			wp_register_script( 'um_friends', um_friends_url . 'assets/js/v3/friends' . $suffix . '.js', array( 'um_new_design' ), um_friends_version, true );
			wp_set_script_translations( 'um_friends', um_friends_textdomain, um_friends_path . 'languages' );
			wp_register_style( 'um_friends', um_friends_url . 'assets/css/v3/friends' . $suffix . '.css', array( 'um_new_design' ), um_friends_version );
		} else {
			wp_register_script( 'um_friends', um_friends_url . 'assets/js/um-friends' . $suffix . '.js', array( 'jquery', 'wp-util', 'wp-i18n', 'um_scripts' ), um_friends_version, true );
			wp_set_script_translations( 'um_friends', um_friends_textdomain, um_friends_path . 'languages' );
			wp_register_style( 'um_friends', um_friends_url . 'assets/css/um-friends' . $suffix . '.css', array( 'um_styles', 'um_fonticons_ii', 'um_fonticons_fa' ), um_friends_version );
		}
	}

	public function block_editor() {
		$suffix = UM()->frontend()->enqueue()::get_suffix();
		if ( defined( 'UM_DEV_MODE' ) && UM_DEV_MODE && UM()->options()->get( 'enable_new_ui' ) ) {
			wp_register_style( 'um_friends', um_friends_url . 'assets/css/v3/friends' . $suffix . '.css', array( 'um_new_design' ), um_friends_version );
			wp_enqueue_style( 'um_friends' );
		} else {
			wp_register_style( 'um_friends', um_friends_url . 'assets/css/um-friends' . $suffix . '.css', array( 'um_styles', 'um_fonticons_ii', 'um_fonticons_fa' ), um_friends_version );
			wp_enqueue_style( 'um_friends' );
		}
	}
}
