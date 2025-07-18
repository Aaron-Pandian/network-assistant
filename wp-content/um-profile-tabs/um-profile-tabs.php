<?php
/**
 * Plugin Name: Ultimate Member - Profile tabs
 * Plugin URI: http://ultimatemember.com/extensions/profile-tabs
 * Description: Adds custom tabs to user profile.
 * Version: 1.1.3
 * Author: Ultimate Member
 * Author URI: http://ultimatemember.com/
 * Text Domain: um-profile-tabs
 * Domain Path: /languages
 * Requires at least: 5.5
 * Requires PHP: 5.6
 * UM version: 2.8.6
 * CF7 version: 5.0
 * Requires Plugins: ultimate-member
 *
 * @package UM_Profile_Tabs
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ABSPATH . 'wp-admin/includes/plugin.php';

$plugin_data = get_plugin_data( __FILE__, true, false );

define( 'um_profile_tabs_url', plugin_dir_url( __FILE__ ) );
define( 'um_profile_tabs_path', plugin_dir_path( __FILE__ ) );
define( 'um_profile_tabs_plugin', plugin_basename( __FILE__  ) );
define( 'um_profile_tabs_extension', $plugin_data['Name'] );
define( 'um_profile_tabs_version', $plugin_data['Version'] );
define( 'um_profile_tabs_textdomain', 'um-profile-tabs' );
define( 'um_profile_tabs_requires', '2.8.6' );


if ( ! function_exists( 'um_profile_tabs_plugins_loaded' ) ) {
	function um_profile_tabs_plugins_loaded() {
		$locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
		load_textdomain( um_profile_tabs_textdomain, WP_LANG_DIR . '/plugins/' . um_profile_tabs_textdomain . '-' . $locale . '.mo' );
		load_plugin_textdomain( um_profile_tabs_textdomain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}
add_action( 'plugins_loaded', 'um_profile_tabs_plugins_loaded', 0 );


add_action( 'plugins_loaded', 'um_profile_tabs_check_dependencies', -20 );

if ( ! function_exists( 'um_profile_tabs_check_dependencies' ) ) {
	/**
	 *
	 */
	function um_profile_tabs_check_dependencies() {
		if ( ! defined( 'um_path' ) || ! file_exists( um_path  . 'includes/class-dependencies.php' ) ) {
			//UM is not installed
			function um_profile_tabs_dependencies() {
				// translators: %s is the Profile Tabs extension name.
				echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-user-photos' ), um_profile_tabs_extension ) . '</p></div>';
			}

			add_action( 'admin_notices', 'um_profile_tabs_dependencies' );

		} else {

			if ( ! function_exists( 'UM' ) ) {
				require_once um_path . 'includes/class-dependencies.php';
				$is_um_active = um\is_um_active();
			} else {
				$is_um_active = UM()->dependencies()->ultimatemember_active_check();
			}

			if ( ! $is_um_active ) {
				//UM is not active
				function um_profile_tabs_dependencies() {
					// translators: %s is the Profile Tabs extension name.
					echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-user-photos' ), um_profile_tabs_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_profile_tabs_dependencies' );

			} elseif ( true !== UM()->dependencies()->compare_versions( um_profile_tabs_requires, um_profile_tabs_version, 'profile-tabs', um_profile_tabs_extension ) ) {
				//UM old version is active
				function um_profile_tabs_dependencies() {
					echo '<div class="error"><p>' . UM()->dependencies()->compare_versions( um_profile_tabs_requires, um_profile_tabs_version, 'profile-tabs', um_profile_tabs_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_profile_tabs_dependencies' );

				function um_profile_tabs_extend_license_settings( $settings ) {
					$settings['licenses']['fields'][] = array(
						'id'        => 'um_profile_tabs_license_key',
						'label'     => __( 'Profile tabs License Key', 'um-profile-tabs' ),
						'item_name' => 'Profile tabs',
						'author'    => 'Ultimate Member',
						'version'   => um_profile_tabs_version,
					);

					return $settings;
				}
				add_filter( 'um_settings_structure', 'um_profile_tabs_extend_license_settings' );
			} else {
				require_once um_profile_tabs_path . 'includes/core/um-profile-tabs-init.php';
			}
		}
	}
}


register_activation_hook( um_profile_tabs_plugin, 'um_profile_tabs_activation_hook' );
if ( ! function_exists( 'um_profile_tabs_activation_hook' ) ) {
	function um_profile_tabs_activation_hook() {
		//first install
		$version = get_option( 'um_profile_tabs_version' );
		if ( ! $version ) {
			update_option( 'um_profile_tabs_last_version_upgrade', um_profile_tabs_version );
		}

		if ( $version != um_profile_tabs_version ) {
			update_option( 'um_profile_tabs_version', um_profile_tabs_version );
		}

		//run setup
		if ( ! class_exists( 'um_ext\um_profile_tabs\core\Setup' ) ) {
			require_once um_profile_tabs_path . 'includes/core/class-setup.php';
		}

		$setup = new um_ext\um_profile_tabs\core\Setup();
		$setup->run_setup();
	}
}
