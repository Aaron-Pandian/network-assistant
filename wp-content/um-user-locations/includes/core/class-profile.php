<?php
namespace um_ext\um_user_locations\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Profile
 *
 * @package um_ext\um_user_locations\core
 */
class Profile {


	/**
	 * Profile constructor.
	 */
	function __construct() {
		add_filter( 'um_change_usermeta_for_update', array( &$this, 'extends_location' ), 10, 4 );
		add_filter( 'um_whitelisted_metakeys', array( &$this, 'extends_whitelisted_location' ), 10, 2 );

		add_action( 'wp_ajax_nopriv_um_get_user_distance', array( &$this, 'get_user_distance' ) );
		add_action( 'wp_ajax_um_get_user_distance', array( &$this, 'get_user_distance' ) );
	}

	public function extends_whitelisted_location( $cf_metakeys, $form_data ) {
		$custom_fields = maybe_unserialize( $form_data['custom_fields'] );

		foreach ( $custom_fields as $cf_k => $cf_data ) {
			if ( array_key_exists( 'type', $cf_data ) && 'user_location' === $cf_data['type'] ) {
				$cf_metakeys[] = $cf_k . '_lat';
				$cf_metakeys[] = $cf_k . '_lng';
				$cf_metakeys[] = $cf_k . '_url';
			}
		}

		return $cf_metakeys;
	}

	/**
	 * Save user_location lat/lng on profile mode
	 * all works properly on registration without this hook
	 *
	 * @param array $to_update
	 * @param array $args
	 * @param array $fields
	 * @param string $key
	 *
	 * @return array
	 */
	function extends_location( $to_update, $args, $fields, $key ) {
		if ( isset( $fields[ $key ]['type'] ) && 'user_location' == $fields[ $key ]['type'] ) {
			if ( isset( $args['submitted'][ $key . '_lat' ] ) ) {
				$to_update[ $key . '_lat' ] = $args['submitted'][ $key . '_lat' ];
			}

			if ( isset( $args['submitted'][ $key . '_lng' ] ) ) {
				$to_update[ $key . '_lng' ] = $args['submitted'][ $key . '_lng' ];
			}

			if ( isset( $args['submitted'][ $key . '_url' ] ) ) {
				$to_update[ $key . '_url' ] = $args['submitted'][ $key . '_url' ];
			}
		}

		return $to_update;
	}


	/**
	 * Ajax get user distance
	 */
	function get_user_distance() {
		if ( empty( $_POST['location'] ) || empty( $_POST['user_coords'] ) || ! is_array( $_POST['location'] ) || ! is_array( $_POST['user_coords'] ) ) {
			wp_send_json_error( __( 'Invalid coordinates', 'um-user-locations' ) );
		}

		if ( empty( $_POST['unit'] ) || ! in_array( $_POST['unit'], [ 'km', 'miles' ] ) ) {
			wp_send_json_error( __( 'Invalid data', 'um-user-locations' ) );
		}

		$location = array_map( 'floatval', $_POST['location'] );
		$user_coords = array_map( 'floatval', $_POST['user_coords'] );

		$distance = UM()->User_Locations()->calculate_distance( $location, $user_coords, sanitize_text_field( $_POST['unit'] ) );
		wp_send_json_success( $distance );
	}
}
