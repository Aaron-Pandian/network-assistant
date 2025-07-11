<?php
namespace um_ext\um_user_locations\core;


if ( ! defined( 'ABSPATH' ) ) exit;


class Setup {

	/**
	 * @var
	 */
	var $settings_defaults;

	function __construct() {
		//settings defaults
		$this->settings_defaults = array(
			'um_google_lang_as_default'         => 1,
			'um_google_lang'                    => get_locale(),
			'um_google_maps_js_api_key'         => '',
			'user_location_map_height'          => '300',
			'um_google_maps_starting_zoom'      => '1',
			'um_google_maps_starting_coord_lat' => '0',
			'um_google_maps_starting_coord_lng' => '0',
		);
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
	 *
	 */
	function run_setup() {
		$this->set_default_settings();
	}

}