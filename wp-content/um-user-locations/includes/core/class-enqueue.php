<?php
namespace um_ext\um_user_locations\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Enqueue
 *
 * @package um_ext\um_user_locations\core
 */
class Enqueue {


	/**
	 * @var string
	 */
	var $suffix;


	/**
	 * @var array
	 */
	var $locale;


	/**
	 * Enqueue constructor.
	 */
	function __construct() {

		$this->suffix = UM()->frontend()->enqueue()::get_suffix();
		$this->locale = UM()->User_Locations()->get_locale();

		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_gmap' ) );
		add_action( 'um_profile_completeness_shortcode_loaded', array( &$this, 'enqueue_field_script' ) );
	}


	/**
	 *
	 */
	function enqueue_field_script() {
		$key = UM()->options()->get( 'um_google_maps_js_api_key' );
		if ( empty( $key ) ) {
			return;
		}

		wp_enqueue_script('um-user-location-field');
	}


	/**
	 *
	 */
	function enqueue_gmap() {
		$key = UM()->options()->get( 'um_google_maps_js_api_key' );
		if ( empty( $key ) ) {
			return;
		}

		wp_register_script(
			'um-user-location-field',
			um_user_locations_url . 'assets/js/user_location_field' . $this->suffix . '.js',
			array( 'jquery', 'wp-hooks', 'wp-i18n' ),
			um_user_locations_version,
			true
		);

		$start_zoom = UM()->options()->get( 'um_google_maps_starting_zoom' );
		$start_zoom = empty( $start_zoom ) ? 12 : $start_zoom;
		$start_lat = UM()->options()->get( 'um_google_maps_starting_coord_lat' );
		$start_lat = empty( $start_lat ) ? 0 : $start_lat;
		$start_lng = UM()->options()->get( 'um_google_maps_starting_coord_lng' );
		$start_lng = empty( $start_lng ) ? 0 : $start_lng;

		wp_localize_script('um-user-location-field', 'um_user_location_var', array(
			'api_key'       => UM()->options()->get( 'um_google_maps_js_api_key' ),
			'is_ssl'        => is_ssl(),
			'region'        => $this->locale,
			'start_coords'  => array(
				'lat'   => $start_lat,
				'lng'   => $start_lng,
				'zoom'  => $start_zoom,
			),
		));

		wp_register_style( 'um_user_location_members', um_user_locations_url . 'assets/css/members' . $this->suffix . '.css' );
		wp_enqueue_style( 'um_user_location_members' );

		wp_register_style( 'um_user_location_map_shortcode', um_user_locations_url . 'assets/css/map-shortcode' . $this->suffix . '.css' );

		wp_register_script(
			'um-user-location-clustering',
			um_user_locations_url . 'assets/js/libs/markerclustererplus.min.js',
			array( 'jquery' ),
			um_user_locations_version,
			true
		);

		wp_register_script(
			'um-user-location-oms',
			um_user_locations_url . 'assets/js/libs/oms.min.js',
			array( 'jquery' ),
			um_user_locations_version,
			true
		);

		wp_register_script( 'um-maps', um_user_locations_url . 'assets/js/map' . $this->suffix . '.js', array( 'wp-util', 'wp-hooks', 'wp-i18n', 'um_members', 'um-user-location-clustering', 'um-user-location-oms' ), um_user_locations_version, true );
		wp_localize_script('um-maps', 'um_user_location_map', array(
			'cluster_url'   => um_user_locations_url . 'assets/img/clusters/m',
			'api_key'       => UM()->options()->get( 'um_google_maps_js_api_key' ),
			'is_ssl'        => is_ssl(),
			'region'        => $this->locale,
		));

		wp_register_script( 'um-map-shortcode', um_user_locations_url . 'assets/js/map-shortcode' . $this->suffix . '.js', array( 'wp-util', 'wp-hooks', 'wp-i18n', 'um-user-location-clustering', 'um-user-location-oms' ), um_user_locations_version, true );
		wp_localize_script('um-map-shortcode', 'um_user_location_map_shortcode', array(
			'cluster_url'   => um_user_locations_url . 'assets/img/clusters/m',
			'api_key'       => UM()->options()->get( 'um_google_maps_js_api_key' ),
			'is_ssl'        => is_ssl(),
			'region'        => $this->locale,
		));

		wp_register_script( 'um-user-location-distance', um_user_locations_url . 'assets/js/distance' . $this->suffix . '.js', array( 'jquery' ), um_user_locations_version, true );
	}

}
