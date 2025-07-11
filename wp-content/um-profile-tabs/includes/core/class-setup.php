<?php
namespace um_ext\um_profile_tabs\core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Setup
 *
 * @package um_ext\um_profile_tabs\core
 */
class Setup {

	/**
	 * @var array
	 */
	public $settings_defaults;

	/**
	 * Setup constructor.
	 */
	public function __construct() {
		//settings defaults
		$this->settings_defaults = array(
			'custom_profiletab_increment' => 1,
		);
	}

	/**
	 *
	 */
	public function set_default_settings() {
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
	 *
	 */
	public function run_setup() {
		$this->set_default_settings();
	}
}
