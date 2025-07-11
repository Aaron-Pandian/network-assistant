<?php
namespace um_ext\um_user_locations\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Shortcodes
 *
 * @package um_ext\um_user_locations\core
 */
class Shortcodes {


	/**
	 * Default avatar size.
	 *
	 * @var int
	 */
	public $avatar_size = 40;


	/**
	 * Shortcodes constructor.
	 */
	function __construct() {
		add_shortcode( 'um_user_locations_map', [ $this, 'locations_map' ] );

		add_action( 'wp_ajax_nopriv_um_get_map_members_data', array( &$this, 'ajax_get_map_members' ) );
		add_action( 'wp_ajax_um_get_map_members_data', array( &$this, 'ajax_get_map_members' ) );
	}


	/**
	 * Shortcode: User locations map
	 *
	 * @example [um_user_locations_map]
	 *
	 * @param array $atts
	 * 'limit' => 10,
	 * 'height' => 300,
	 * 'zoom' => 10,
	 * 'lat' => 10,
	 * 'lan' => 10
	 *
	 * @return string html
	 */
	function locations_map( $atts = [] ) {
		$map_options = get_option( 'um_map_user_fields', [] );

		$field = '';
		if ( ! empty( $map_options ) ) {
			$field = $map_options[0];
		}

		$args = shortcode_atts( [
			'zoom'          => 1,
			'lat'           => 0,
			'lng'           => 0,
			'map_sw'        => 0,
			'map_ne'        => 0,
			'height'        => 300,
			'show_map'      => true,
			'field'         => $field,
			'marker_type'   => '', // empty = Avatar marker
		], $atts );

		wp_enqueue_script( 'um-map-shortcode' );
		wp_enqueue_style( 'um_user_location_map_shortcode' );

		$user_fields = UM()->builtin()->all_user_fields();
		if ( ! array_key_exists( $args['field'], $user_fields ) ) {
			return '';
		}

		$args['field_title'] = isset( $user_fields[ $args['field'] ]['title'] ) ? $user_fields[ $args['field'] ]['title'] : '';

		$output = UM()->get_template( 'map-shortcode.php', um_user_locations_plugin, $args );

		return $output;
	}


	/**
	 * Main Query function for getting members via AJAX
	 */
	function ajax_get_map_members() {
		UM()->check_ajax_nonce();

		if ( empty( $_POST['field'] ) ) {
			wp_send_json_error( __( 'Wrong data', 'um-user-locations' ) );
		}

		$get_users_args = array(
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'     => $_POST['field'] . '_lat',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => $_POST['field'] . '_lng',
					'compare' => 'EXISTS',
				),
			),
			'fields'     => 'ids',
		);

		if ( ! UM()->roles()->um_user_can( 'can_edit_everyone' )  ) {
			$get_users_args['meta_query'] = array_merge(
				$get_users_args['meta_query'],
				array(
					array(
						'key'     => 'um_member_directory_data',
						'value'   => 's:14:"account_status";s:8:"approved";',
						'compare' => 'LIKE',
					),
				)
			);
		}

		$get_users_args = apply_filters( 'um_user_locations_ajax_get_map_members_args', $get_users_args );

		$user_ids = get_users( $get_users_args );

		$marker_type = isset( $_POST['marker_type'] ) && in_array( $_POST['marker_type'], [ '', 'role', 'classic'] ) ? $_POST['marker_type'] : '';

		$users = array();
		foreach ( $user_ids as $user_id ) {
			if ( ! $data = $this->build_user_card_data_map( $user_id, $marker_type ) ) {
				continue;
			}
			$users[] = $data;
		}

		um_reset_user();

		wp_send_json_success( array(
			'users' => $users,
		) );
	}


	/**
	 * @param int $user_id
	 * @param string $marker_type
	 *
	 * @return bool|array
	 */
	function build_user_card_data_map( $user_id, $marker_type ) {

		if ( isset( $_POST['field'] ) ) {
			$metakey = $_POST['field'];
			$user_lat = get_user_meta( $user_id, $metakey . '_lat', true );
			$user_lng = get_user_meta( $user_id, $metakey . '_lng', true );

			if ( $user_lat == '' || $user_lng == '' ) {
				return false;
			}

			if ( $user_lat ) {
				$data_array[ $metakey . '_lat' ] = $user_lat;
			}
			if ( $user_lng ) {
				$data_array[ $metakey . '_lng' ] = $user_lng;
			}
		}

		um_fetch_user( $user_id );

		if ( $marker_type == 'role' ) {
			$role_key = UM()->roles()->get_priority_user_role( $user_id );
			$role_meta = UM()->roles()->role_data( $role_key );

			if ( isset( $role_meta['locations_media_icon']['url'] ) && $role_meta['locations_media_icon']['url'] != '' ) {
				$marker_image_url = $role_meta['locations_media_icon']['url'];
			} else {
				$marker_image_url = um_get_user_avatar_url( $user_id );
			}
		} elseif ( $marker_type == 'classic' ) {
			$marker_image_url = '';
		} else {
			$marker_image_url = um_get_user_avatar_url( $user_id );
		}


		$data_array = array(
			'id'                    => $user_id,
			'display_name'          => um_user( 'display_name' ),
			'profile_url'           => um_user_profile_url(),
			'avatar'                => get_avatar( $user_id, $this->avatar_size ),
			'display_name_html'     => um_user( 'display_name', 'html' ),
			'avatar_url'            => $marker_image_url,
		);

		if ( isset( $_POST['field'] ) ) {
			$metakey = $_POST['field'];
			$user_lat = get_user_meta( $user_id, $metakey . '_lat', true );
			$user_lng = get_user_meta( $user_id, $metakey . '_lng', true );
			if ( $user_lat ) {
				$data_array[ $metakey . '_lat' ] = $user_lat;
			}
			if ( $user_lng ) {
				$data_array[ $metakey . '_lng' ] = $user_lng;
			}
		}

		um_reset_user_clean();

		return $data_array;
	}
}