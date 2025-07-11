<?php
namespace um_ext\um_user_locations\core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Member_Directory
 *
 * @package um_ext\um_user_locations\core
 */
class Member_Directory {

	/**
	 * @var string
	 */
	public $custom_order = '';

	/**
	 * @var int
	 */
	public $new_pagination = 0;

	/**
	 * Member_Directory constructor.
	 */
	public function __construct() {
		add_action( 'um_before_member_directory_save', array( &$this, 'delete_map_fields' ) );
		add_action( 'um_members_directory_before_head', array( &$this, 'header_add_map' ), 10, 3 );

		add_filter( 'um_prepare_user_query_args', array( &$this, 'search_by_coord' ), 40, 2 );
		add_action( 'um_pre_users_query', array( &$this, 'search_by_coord_meta' ), 10, 2 );

		add_filter( 'um_ajax_get_members_data', array( &$this, 'add_maps_args' ), 10, 3 );
		add_filter( 'um_ajax_get_members_response', array( &$this, 'add_maps_fields' ), 10, 2 );

		add_action( 'um_members_directory_footer', array( &$this, 'add_current_location_fields' ), 10, 2 );

		add_filter( 'um_ajax_get_members_data', array( &$this, 'extends_member_data' ), 10, 3 );

		add_filter( 'um_members_directory_sort_fields', array( &$this, 'add_sort_distance' ) );

		add_filter( 'um_modify_sortby_parameter', array( &$this, 'sortby_distance' ), 100, 2 );

		add_filter( 'get_meta_sql', array( &$this, 'change_meta_sql_distance' ), 10, 6 );
		add_filter( 'pre_user_query', array( &$this, 'wp_user_filter_by_distance' ), 100 );

		add_action( 'um_pre_users_query', array( &$this, 'distance_handler_meta' ), 10, 3 );

		add_filter( 'um_members_directory_custom_field_types_supported_filter', array( &$this, 'custom_field_types_supported_filter' ) );
		add_filter( 'um_members_directory_filter_types', array( &$this, 'directory_filter_types' ) );
		add_filter( 'um_members_directory_filter_text', array( &$this, 'filter_text' ), 10, 2 );
		add_filter( 'um_members_directory_filter_text_meta_value', array( &$this, 'filter_text_meta_value' ), 10, 2 );

		add_filter( 'um_prepare_user_results_array', array( &$this, 'closest_users_array' ), 10, 2 );
		add_filter( 'um_prepare_user_results_array_meta', array( &$this, 'closest_users_array_meta' ), 10, 2 );
	}


	/**
	 * @param $user_ids
	 * @param $query
	 *
	 * @return array
	 */
	public function closest_users_array( $user_ids, $query ) {
		if ( ! empty( $user_ids ) ) {
			return $user_ids;
		}

		// phpcs:ignore WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`
		$directory_id = UM()->member_directory()->get_directory_by_hash( sanitize_key( $_POST['directory_id'] ) );
		$closest      = get_post_meta( $directory_id, '_um_search_distance_closest', true );

		if ( empty( $closest ) ) {
			return $user_ids;
		}

		// phpcs:ignore WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`
		if ( empty( $_POST['current_user_location'] ) || ! is_array( $_POST['current_user_location'] ) ) {
			return $user_ids;
		}

		unset( $query['meta_query'] );
		$query['meta_query']['relation'] = 'AND';

		foreach ( UM()->member_directory()->query_args['meta_query'] as $value ) {
			if ( isset( $value['key'] ) && 'um_member_directory_data' === $value['key'] ) {
				$query['meta_query'][] = $value;
			}
		}

		if ( empty( UM()->builtin()->all_user_fields ) ) {
			UM()->builtin()->set_custom_fields();
		}

		foreach ( UM()->builtin()->all_user_fields as $all_user_field ) {
			if ( 'distance' === $all_user_field['type'] && ! empty( $all_user_field['location_source'] ) ) {
				$query['meta_query'][] = array(
					'relation'                         => 'OR',
					array(
						'relation' => 'AND',
						array(
							'key'     => $all_user_field['location_source'] . '_lat',
							'compare' => 'EXISTS',
							'type'    => 'NUMERIC',
						),
						array(
							'key'     => $all_user_field['location_source'] . '_lng',
							'compare' => 'EXISTS',
							'type'    => 'NUMERIC',
						),
					),
					$all_user_field['location_source'] => array(
						'relation' => 'AND',
						array(
							'key'     => $all_user_field['location_source'] . '_lat',
							'compare' => 'NOT EXISTS',
							'type'    => 'NUMERIC',
						),
						array(
							'key'     => $all_user_field['location_source'] . '_lng',
							'compare' => 'NOT EXISTS',
							'type'    => 'NUMERIC',
						),
					),
				);

				$query['orderby']              = 'distance';
				$query['orderby_location_key'] = $all_user_field['location_source'];
				$query['order']                = 'ASC';

				break;
			}
		}

		$user_query = new \WP_User_Query( $query );

		$user_ids             = ! empty( $user_query->results ) ? array_unique( $user_query->results ) : array();
		$this->new_pagination = $user_query->total_users;

		return $user_ids;
	}

	/**
	 * @param $user_ids
	 * @param $query
	 *
	 * @return array
	 */
	public function closest_users_array_meta( $user_ids, $query ) {
		if ( ! empty( $user_ids ) ) {
			return $user_ids;
		}

		// phpcs:ignore WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`
		$directory_id = UM()->member_directory()->get_directory_by_hash( sanitize_key( $_POST['directory_id'] ) );
		$closest      = get_post_meta( $directory_id, '_um_search_distance_closest', true );

		if ( empty( $closest ) ) {
			return $user_ids;
		}

		// phpcs:ignore WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`
		if ( empty( $_POST['current_user_location'] ) || ! is_array( $_POST['current_user_location'] ) ) {
			return $user_ids;
		}
		// phpcs:ignore WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`
		$current_location = array_map( 'floatval', $_POST['current_user_location'] );

		global $wpdb;

		$sql_order = '';
		$join      = "LEFT JOIN {$wpdb->prefix}um_metadata AS umm_general ON ( u.ID = umm_general.user_id ) ";
		if ( empty( UM()->builtin()->all_user_fields ) ) {
			UM()->builtin()->set_custom_fields();
		}

		foreach ( UM()->builtin()->all_user_fields as $all_user_field ) {
			if ( 'distance' === $all_user_field['type'] && ! empty( $all_user_field['location_source'] ) ) {
				list( $current_lat, $current_lng ) = $current_location;

				$join .= $wpdb->prepare( "LEFT JOIN {$wpdb->prefix}um_metadata umm_locations_lat_distance ON ( umm_locations_lat_distance.user_id = u.ID AND umm_locations_lat_distance.um_key = %s ) ", $all_user_field['location_source'] . '_lat' );
				$join .= $wpdb->prepare( "LEFT JOIN {$wpdb->prefix}um_metadata umm_locations_lng_distance ON ( umm_locations_lng_distance.user_id = u.ID AND umm_locations_lng_distance.um_key = %s ) ", $all_user_field['location_source'] . '_lng' );

				$sql_order = 'ORDER BY ISNULL(NULLIF(umm_locations_lng_distance.um_value, "")) ASC, DEGREES(ACOS(LEAST(1.0, COS(RADIANS(umm_locations_lat_distance.um_value)) * COS(RADIANS(' . $current_lat . ')) * COS(RADIANS(umm_locations_lng_distance.um_value - ' . $current_lng . ')) + SIN(RADIANS(umm_locations_lat_distance.um_value)) * SIN(RADIANS(' . $current_lat . ')))))';
				break;
			}
		}

		$where = explode( 'AND ( umm_search', $query['sql_where'] );

		/*
		 *
		 * SQL_CALC_FOUND_ROWS is deprecated as of MySQL 8.0.17
		 * https://core.trac.wordpress.org/ticket/47280
		 *
		 * */
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- variables are pre-escaped.
		$user_ids = $wpdb->get_col(
			"SELECT SQL_CALC_FOUND_ROWS DISTINCT u.ID
			FROM {$wpdb->users} as u
			{$join}
			WHERE 1=1 {$where[0]}
			{$query['having']}
			{$sql_order}
			{$query['sql_limit']}"
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- variables are pre-escaped.

		$this->new_pagination = (int) $wpdb->get_var( 'SELECT FOUND_ROWS()' );
		return $user_ids;
	}

	/**
	 * @param $query
	 * @param $directory_data
	 * @param $sortby
	 */
	public function distance_handler_meta( $query, $directory_data, $sortby ) {
		// phpcs:ignore WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`
		if ( empty( $_POST['current_user_location'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`
		$current_location = array_map( 'floatval', $_POST['current_user_location'] );

		if ( empty( UM()->builtin()->all_user_fields ) ) {
			UM()->builtin()->set_custom_fields();
		}

		foreach ( UM()->builtin()->all_user_fields as $all_user_field ) {
			if ( 'distance' === $all_user_field['type'] && ! empty( $all_user_field['location_source'] ) ) {
				if ( $all_user_field['metakey'] === $sortby ) {
					global $wpdb;

					list( $current_lat, $current_lng ) = $current_location;

					$query->joins[] = $wpdb->prepare( "LEFT JOIN {$wpdb->prefix}um_metadata umm_locations_lat_distance ON ( umm_locations_lat_distance.user_id = u.ID AND umm_locations_lat_distance.um_key = %s )", $all_user_field['location_source'] . '_lat' );
					$query->joins[] = $wpdb->prepare( "LEFT JOIN {$wpdb->prefix}um_metadata umm_locations_lng_distance ON ( umm_locations_lng_distance.user_id = u.ID AND umm_locations_lng_distance.um_key = %s )", $all_user_field['location_source'] . '_lng' );

					$query->sql_order = $wpdb->prepare( 'ORDER BY ISNULL(NULLIF(umm_locations_lng_distance.um_value, "")) ASC, DEGREES(ACOS(LEAST(1.0, COS(RADIANS(umm_locations_lat_distance.um_value)) * COS(RADIANS(%f)) * COS(RADIANS(umm_locations_lng_distance.um_value - %f)) + SIN(RADIANS(umm_locations_lat_distance.um_value)) * SIN(RADIANS(%f)))))', $current_lat, $current_lng, $current_lat );
					break;
				}
			}
		}
	}

	/**
	 * @param $sql
	 * @param $queries
	 * @param $type
	 * @param $primary_table
	 * @param $primary_id_column
	 * @param $context
	 *
	 * @return mixed
	 */
	public function change_meta_sql_distance( $sql, $queries, $type, $primary_table, $primary_id_column, $context ) {
		// phpcs:ignore WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`
		if ( ! empty( $context->query_vars['orderby_location_key'] ) && ! empty( $_POST['current_user_location'] ) ) {
			global $wpdb;

			// phpcs:ignore WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`
			list( $current_lat, $current_lng ) = $_POST['current_user_location'];

			$meta_lat   = '';
			$meta_lng   = '';
			$empty_meta = '';

			preg_match(
				'/^(.*).meta_key = \'' . $context->query_vars['orderby_location_key'] . '_lat\'[^\)]/im',
				$sql['where'],
				$join_matches
			);

			if ( isset( $join_matches[1] ) ) {
				$meta_lat = esc_sql( trim( $join_matches[1] ) );
			}

			preg_match(
				'/^(.*).meta_key = \'' . $context->query_vars['orderby_location_key'] . '_lng\'[^\)]/im',
				$sql['where'],
				$join_matches
			);

			if ( isset( $join_matches[1] ) ) {
				$meta_lng = esc_sql( trim( $join_matches[1] ) );
			}

			preg_match(
				'/.user_id AND (.*).meta_key = \'' . $context->query_vars['orderby_location_key'] . '_lat\'[^\)]/im',
				$sql['join'],
				$join_matches
			);

			if ( isset( $join_matches[1] ) ) {
				$empty_meta = esc_sql( trim( $join_matches[1] ) );
			}

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $empty_meta, $meta_lat, $meta_lng are early escaped
			$this->custom_order = $wpdb->prepare( "ORDER BY ISNULL(NULLIF({$empty_meta}.meta_value, '')) ASC, DEGREES(ACOS(LEAST(1.0, COS(RADIANS({$meta_lat}.meta_value)) * COS(RADIANS(%f)) * COS(RADIANS({$meta_lng}.meta_value - %f)) + SIN(RADIANS({$meta_lat}.meta_value)) * SIN(RADIANS(%f)))))", $current_lat, $current_lng, $current_lat );
		}

		return $sql;
	}

	public function wp_user_filter_by_distance( $query ) {
		if ( ! empty( $this->custom_order ) ) {
			$query->query_orderby = $this->custom_order;
		}

		return $query;
	}

	/**
	 * Adding sort directories by distance
	 *
	 * @param array  $query_args
	 * @param string $sortby
	 *
	 * @return array
	 */
	public function sortby_distance( $query_args, $sortby ) {
		// phpcs:ignore WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`
		if ( empty( $_POST['current_user_location'] ) ) {
			return $query_args;
		}

		if ( empty( UM()->builtin()->all_user_fields ) ) {
			UM()->builtin()->set_custom_fields();
		}

		foreach ( UM()->builtin()->all_user_fields as $all_user_field ) {
			if ( 'distance' === $all_user_field['type'] && ! empty( $all_user_field['location_source'] ) ) {
				if ( $all_user_field['metakey'] === $sortby ) {
					$query_args['meta_query'][] = array(
						'relation'                         => 'OR',
						array(
							'relation' => 'AND',
							array(
								'key'     => $all_user_field['location_source'] . '_lat',
								'compare' => 'EXISTS',
								'type'    => 'NUMERIC',
							),
							array(
								'key'     => $all_user_field['location_source'] . '_lng',
								'compare' => 'EXISTS',
								'type'    => 'NUMERIC',
							),
						),
						$all_user_field['location_source'] => array(
							'relation' => 'AND',
							array(
								'key'     => $all_user_field['location_source'] . '_lat',
								'compare' => 'NOT EXISTS',
								'type'    => 'NUMERIC',
							),
							array(
								'key'     => $all_user_field['location_source'] . '_lng',
								'compare' => 'NOT EXISTS',
								'type'    => 'NUMERIC',
							),
						),
					);

					$query_args['orderby']              = 'distance';
					$query_args['orderby_location_key'] = $all_user_field['location_source'];
					$query_args['order']                = 'ASC';

					break;
				}
			}
		}

		return $query_args;
	}

	/**
	 * @param $options
	 *
	 * @return mixed
	 */
	public function add_sort_distance( $options ) {
		if ( empty( UM()->builtin()->all_user_fields ) ) {
			UM()->builtin()->set_custom_fields();
		}

		foreach ( UM()->builtin()->all_user_fields as $all_user_field ) {
			if ( isset( $all_user_field['type'] ) && 'distance' === $all_user_field['type'] ) {
				$options[ $all_user_field['metakey'] ] = $all_user_field['title'];
			}
		}

		return $options;
	}

	/**
	 * @param $data_array
	 * @param $user_id
	 * @param $directory_data
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function extends_member_data( $data_array, $user_id, $directory_data ) {
		// phpcs:ignore WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`
		if ( empty( $_POST['current_user_location'] ) || ! is_array( $_POST['current_user_location'] ) ) {
			return $data_array;
		}

		// phpcs:ignore WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`
		$current_location = array_map( 'floatval', $_POST['current_user_location'] );

		if ( ! empty( $directory_data['show_tagline'] ) ) {
			if ( ! empty( $directory_data['tagline_fields'] ) ) {
				$directory_data['tagline_fields'] = maybe_unserialize( $directory_data['tagline_fields'] );

				if ( is_array( $directory_data['tagline_fields'] ) ) {
					foreach ( $directory_data['tagline_fields'] as $key ) {
						if ( ! $key ) {
							continue;
						}

						$field_data = UM()->fields()->get_field( $key );
						if ( ! isset( $field_data['type'] ) || 'distance' !== $field_data['type'] ) {
							continue;
						}

						if ( ! isset( $field_data['location_unit'], $field_data['location_source'] ) ) {
							continue;
						}

						$user_lat = get_user_meta( $user_id, $field_data['location_source'] . '_lat', true );
						$user_lng = get_user_meta( $user_id, $field_data['location_source'] . '_lng', true );

						if ( '' === $user_lat || '' === $user_lng ) {
							continue;
						}

						$user_coords = array( $user_lat, $user_lng );

						$distance = UM()->User_Locations()->calculate_distance( $current_location, $user_coords, $field_data['location_unit'] );

						if ( empty( $distance ) ) {
							continue;
						}

						$data_array[ $key ] = $distance;
					}
				}
			}
		}

		if ( ! empty( $directory_data['show_userinfo'] ) ) {

			if ( ! empty( $directory_data['reveal_fields'] ) ) {

				$directory_data['reveal_fields'] = maybe_unserialize( $directory_data['reveal_fields'] );

				if ( is_array( $directory_data['reveal_fields'] ) ) {
					foreach ( $directory_data['reveal_fields'] as $key ) {
						if ( ! $key ) {
							continue;
						}

						$field_data = UM()->fields()->get_field( $key );
						if ( ! isset( $field_data['type'] ) || 'distance' !== $field_data['type'] ) {
							continue;
						}

						if ( ! isset( $field_data['location_unit'], $field_data['location_source'] ) ) {
							continue;
						}

						$user_lat = get_user_meta( $user_id, $field_data['location_source'] . '_lat', true );
						$user_lng = get_user_meta( $user_id, $field_data['location_source'] . '_lng', true );

						if ( '' === $user_lat || '' === $user_lng ) {
							continue;
						}

						$user_coords = array( $user_lat, $user_lng );

						$distance = UM()->User_Locations()->calculate_distance( $current_location, $user_coords, $field_data['location_unit'] );

						if ( empty( $distance ) ) {
							continue;
						}

						$label = UM()->fields()->get_label( $key );

						$data_array[ "label_{$key}" ] = $label;
						$data_array[ $key ]           = $distance;
					}
				}
			}
		}

		return $data_array;
	}

	/**
	 * Add hidden field for getting current user location
	 *
	 * @param array $args
	 * @param int   $form_id
	 *
	 * @throws \Exception
	 */
	public function add_current_location_fields( $args, $form_id ) {
		$show_fields = false;
		if ( ! empty( $args['show_tagline'] ) ) {
			if ( ! empty( $args['tagline_fields'] ) ) {
				foreach ( $args['tagline_fields'] as $key ) {
					$field_data = UM()->fields()->get_field( $key );
					if ( isset( $field_data['type'] ) && 'distance' === $field_data['type'] ) {
						$show_fields = true;
						break;
					}
				}
			}
		}

		if ( ! $show_fields ) {
			if ( ! empty( $args['show_userinfo'] ) ) {
				if ( ! empty( $args['reveal_fields'] ) ) {
					foreach ( $args['reveal_fields'] as $key ) {
						$field_data = UM()->fields()->get_field( $key );
						if ( isset( $field_data['type'] ) && 'distance' === $field_data['type'] ) {
							$show_fields = true;
							break;
						}
					}
				}
			}
		}

		if ( ! $show_fields ) {
			$distance_keys = array();
			foreach ( UM()->builtin()->all_user_fields as $all_user_field ) {
				if ( isset( $all_user_field['type'] ) && 'distance' === $all_user_field['type'] ) {
					$distance_keys[] = $all_user_field['metakey'];
				}
			}

			if ( ! empty( $distance_keys ) && ! empty( $args['enable_sorting'] ) ) {
				$default_sorting = ! empty( $args['sortby'] ) ? $args['sortby'] : 'user_registered_desc';
				if ( 'other' === $default_sorting && ! empty( $args['sortby_custom'] ) ) {
					$default_sorting = $args['sortby_custom'];
				}

				$sorting_options = empty( $args['sorting_fields'] ) ? array() : $args['sorting_fields'];

				$sorting_options_prepared = array();
				$custom_sorting_titles    = array();

				if ( ! empty( $sorting_options ) ) {
					foreach ( $sorting_options as $option ) {
						if ( is_array( $option ) ) {
							$option_keys                = array_keys( $option );
							$sorting_options_prepared[] = $option_keys[0];

							$custom_sorting_titles[ $option_keys[0] ] = $option[ $option_keys[0] ];
						} else {
							$sorting_options_prepared[] = $option;
						}
					}
				}

				$all_sorting_options = UM()->member_directory()->sort_fields;

				if ( ! in_array( $default_sorting, $sorting_options_prepared, true ) ) {
					$sorting_options_prepared[] = $default_sorting;

					$label = $default_sorting;
					if ( ! empty( $args['sortby_custom_label'] ) && 'other' === $args['sortby'] ) {
						$label = $args['sortby_custom_label'];
					} elseif ( ! empty( $all_sorting_options[ $default_sorting ] ) ) {
						$label = $all_sorting_options[ $default_sorting ];
					}
					$custom_sorting_titles[ $default_sorting ] = $label;
				}

				if ( ! empty( $sorting_options_prepared ) ) {
					$sorting_options = array_intersect_key( array_merge( $all_sorting_options, $custom_sorting_titles ), array_flip( $sorting_options_prepared ) );
				}

				$sorting_options = apply_filters( 'um_member_directory_pre_display_sorting', $sorting_options, $args );
			}

			if ( ! empty( $sorting_options ) && count( array_intersect( array_keys( $sorting_options ), $distance_keys ) ) ) {
				$show_fields = true;
			}
		}

		$closest = get_post_meta( $form_id, '_um_search_distance_closest', true );

		if ( ! $show_fields && empty( $closest ) ) {
			return;
		}

		wp_enqueue_script( 'um-user-location-distance' );

		ob_start();
		?>
		<input id="um-user-location-current-denied" type="hidden" value="" />
		<input id="um-user-location-current-lat" type="hidden" value="" />
		<input id="um-user-location-current-lng" type="hidden" value="" />
		<?php
		ob_get_flush();
	}

	/**
	 * @param $options
	 *
	 * @return mixed
	 */
	public function custom_field_types_supported_filter( $options ) {
		$options[] = 'user_location';
		return $options;
	}

	/**
	 * @param $types
	 *
	 * @return mixed
	 */
	public function directory_filter_types( $types ) {
		$map_fields = get_option( 'um_map_user_fields', array() );
		foreach ( $map_fields as $field ) {
			$types[ $field ] = 'text';
		}

		return $types;
	}

	/**
	 * @param $compare
	 * @param $field
	 *
	 * @return string
	 */
	public function filter_text( $compare, $field ) {
		if ( 'user_location' === UM()->fields()->get_field_type( $field ) ) {
			$compare = 'LIKE';
		}

		return $compare;
	}

	/**
	 * @param $value
	 * @param $field
	 *
	 * @return string
	 */
	public function filter_text_meta_value( $value, $field ) {
		if ( 'user_location' === UM()->fields()->get_field_type( $field ) ) {
			global $wpdb;
			$value = '%' . $wpdb->esc_like( $value ) . '%';
		}

		return $value;
	}

	/**
	 * @param $post_id
	 */
	public function delete_map_fields( $post_id ) {
		delete_post_meta( $post_id, '_um_user_location_infowindow_data' );
	}

	/**
	 * @param $args
	 * @param $form_id
	 * @param bool $not_searched
	 */
	public function header_add_map( $args, $form_id, $not_searched ) {
		$key = UM()->options()->get( 'um_google_maps_js_api_key' );
		if ( empty( $key ) ) {
			return;
		}

		if ( empty( $args['show_map'] ) ) {
			return;
		}

		if ( empty( $args['user_location_fields'] ) ) {
			return;
		}

		wp_enqueue_script( 'um-maps' );

		$hash = UM()->member_directory()->get_directory_hash( $form_id );

		$height = ! empty( $args['map_height'] ) ? absint( $args['map_height'] ) : UM()->options()->get( 'user_location_map_height' );
		$height = empty( $height ) ? '300px' : absint( $height ) . 'px';

		$zoom = ! empty( $args['map_starting_zoom'] ) ? absint( $args['map_starting_zoom'] ) : 1;
		$lat  = ! empty( $args['map_starting_coord_lat'] ) ? $args['map_starting_coord_lat'] : 0;
		$lng  = ! empty( $args['map_starting_coord_lng'] ) ? $args['map_starting_coord_lng'] : 0;

		$map_sw     = '';
		$map_ne     = '';
		$search_lat = '';
		$search_lng = '';

		$search         = false;
		$dynamic_search = false;
		if ( ! empty( $args['map_search_by_moving'] ) ) {
			// phpcs:disable WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`
			if ( ! isset( $args['map_search_type'] ) || 'visible_bounds' === $args['map_search_type'] ) {
				$dynamic_search = true;

				$map_sw_key = 'map_sw_' . $hash;
				if ( isset( $_GET[ $map_sw_key ] ) ) {
					$sw_array = explode( ',', $_GET[ $map_sw_key ] );
					$sw_array = array_map( 'floatval', $sw_array );
					$map_sw   = implode( ',', $sw_array );
				}

				$map_ne_key = 'map_ne_' . $hash;
				if ( isset( $_GET[ $map_ne_key ] ) ) {
					$ne_array = explode( ',', $_GET[ $map_ne_key ] );
					$ne_array = array_map( 'floatval', $ne_array );
					$map_ne   = implode( ',', $ne_array );
				}
			} elseif ( 'radius' === $args['map_search_type'] ) {
				$search = true;

				$search_lat_key = 'search_lat_' . $hash;
				if ( isset( $_GET[ $search_lat_key ] ) ) {
					$search_lat = (float) $_GET[ $search_lat_key ];
				}

				$search_lng_key = 'search_lng_' . $hash;
				if ( isset( $_GET[ $search_lng_key ] ) ) {
					$search_lng = (float) $_GET[ $search_lng_key ];
				}
			}
			// phpcs:enable WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`
		}

		if ( ! empty( $args['user_location_infowindow_data'] ) ) {
			$args['user_location_infowindow_data'] = maybe_unserialize( $args['user_location_infowindow_data'] );
		} else {
			$args['user_location_infowindow_data'] = array();
		}

		UM()->get_template( 'infowindow.php', um_user_locations_plugin, array( 'infowindow_fields' => $args['user_location_infowindow_data'] ), true );

		$t_args = array(
			'args'              => $args,
			'not_searched'      => $not_searched,
			'zoom'              => $zoom,
			'lat'               => $lat,
			'lng'               => $lng,
			'dynamic_search'    => $dynamic_search,
			'map_sw'            => $map_sw,
			'map_ne'            => $map_ne,
			'height'            => $height,
			'search'            => $search,
			'search_lat'        => $search_lat,
			'search_lng'        => $search_lng,
			'predefined_radius' => ! empty( $args['map_search_predefined_radius'] ) ? $args['map_search_predefined_radius'] : 0,
			'distance_unit'     => ! empty( $args['map_search_distance_unit'] ) ? $args['map_search_distance_unit'] : 'km',
		);

		UM()->get_template( 'map.php', um_user_locations_plugin, $t_args, true );
	}

	/**
	 * @param array $query_args
	 * @param array $directory_data
	 *
	 * @return array
	 */
	public function search_by_coord( $query_args, $directory_data ) {
		$key = UM()->options()->get( 'um_google_maps_js_api_key' );
		if ( empty( $key ) ) {
			return $query_args;
		}

		if ( empty( $directory_data['show_map'] ) ) {
			return $query_args;
		}

		if ( ! empty( $directory_data['map_search_by_moving'] ) ) {
			$directory_data['user_location_fields'] = maybe_unserialize( $directory_data['user_location_fields'] );
			if ( is_string( $directory_data['user_location_fields'] ) ) {
				$directory_data['user_location_fields'] = array( $directory_data['user_location_fields'] );
			}

			if ( ! isset( $directory_data['map_search_type'] ) || 'visible_bounds' === $directory_data['map_search_type'] ) {
				// phpcs:disable WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`
				if ( ! isset( $_POST['map_ne'] ) ) {
					$ne_lat = 90;
					$ne_lng = 180;
				} else {
					$ne_array = explode( ',', $_POST['map_ne'] );
					$ne_array = array_map( 'floatval', $ne_array );

					list( $ne_lat, $ne_lng ) = $ne_array;
				}

				if ( ! isset( $_POST['map_sw'] ) ) {
					$sw_lat = -90;
					$sw_lng = -180;
				} else {
					$sw_array = explode( ',', $_POST['map_sw'] );
					$sw_array = array_map( 'floatval', $sw_array );

					list( $sw_lat, $sw_lng ) = $sw_array;
				}
				// phpcs:enable WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`

				// for some zooms when bounds already full-world
				if ( $sw_lng >= $ne_lng ) {
					$ne_lng = 180;
					$sw_lng = -180;
				}

				if ( ! empty( $directory_data['user_location_fields'] ) ) {
					if ( empty( $query_args['meta_query'] ) ) {
						$query_args['meta_query'] = array();
					}

					if ( count( $directory_data['user_location_fields'] ) > 1 ) {
						$location_query = array( 'relation' => 'OR' );
					}
					foreach ( $directory_data['user_location_fields'] as $field_key ) {
						$subquery = array(
							'relation' => 'AND',
							array(
								'key'     => $field_key . '_lat',
								'value'   => array( $sw_lat, $ne_lat ),
								'compare' => 'BETWEEN',
								'type'    => 'DECIMAL(16,14)',
							),
							array(
								'key'     => $field_key . '_lng',
								'value'   => array( $sw_lng, $ne_lng ),
								'compare' => 'BETWEEN',
								'type'    => 'DECIMAL(17,14)',
							),
						);

						if ( count( $directory_data['user_location_fields'] ) > 1 ) {
							$location_query[] = $subquery;
						} else {
							$query_args['meta_query'][] = $subquery;
						}
					}

					if ( count( $directory_data['user_location_fields'] ) > 1 ) {
						$query_args['meta_query'][] = $location_query;
					}
				}
			} elseif ( 'radius' === $directory_data['map_search_type'] ) {
				// phpcs:ignore WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`
				if ( isset( $_POST['search_lat'] ) && $_POST['search_lng'] ) {
					$radius          = ! empty( $directory_data['map_search_predefined_radius'] ) ? $directory_data['map_search_predefined_radius'] : 1;
					$distance_metric = 'km' === $directory_data['map_search_distance_unit'] ? 6371 : 3959;

					// phpcs:disable WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`
					$pos_lat = ! empty( $_POST['search_lat'] ) ? (float) $_POST['search_lat'] : 0;
					$pos_lng = ! empty( $_POST['search_lng'] ) ? (float) $_POST['search_lng'] : 0;
					// phpcs:enable WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`

					if ( ! empty( $directory_data['user_location_fields'] ) ) {

						global $wpdb;

						$query_args['um_location_search'] = array(
							'select' => $wpdb->prepare( ', ( %d * ACOS( COS( RADIANS( {lat_value} ) ) * COS( RADIANS( %f ) ) * COS( RADIANS( %f ) - RADIANS( {lng_value} ) ) + SIN( RADIANS( {lat_value} ) ) * SIN( RADIANS( %f ) ) ) ) AS {key}_distance', $distance_metric, $pos_lat, $pos_lng, $pos_lat ),
							'having' => $wpdb->prepare( '{key}_distance <= %d', $radius ),
							'fields' => $directory_data['user_location_fields'],
						);

						add_filter( 'pre_user_query', array( &$this, 'change_wp_native_users_query' ) );

						if ( count( $directory_data['user_location_fields'] ) > 1 ) {
							$location_query = array( 'relation' => 'OR' );
						}
						foreach ( $directory_data['user_location_fields'] as $field_key ) {
							$subquery = array(
								'relation'          => 'AND',
								$field_key . '_lat' => array(
									'key'     => $field_key . '_lat',
									'compare' => 'EXISTS',
								),
								$field_key . '_lng' => array(
									'key'     => $field_key . '_lng',
									'compare' => 'EXISTS',
								),
							);

							if ( count( $directory_data['user_location_fields'] ) > 1 ) {
								$location_query[] = $subquery;
							} else {
								$query_args['meta_query'][] = $subquery;
							}
						}

						if ( count( $directory_data['user_location_fields'] ) > 1 ) {
							$query_args['meta_query'][] = $location_query;
						}
					}
				}
			}
		}

		return $query_args;
	}

	/**
	 * Replace WP native users query variables to search in radius
	 *
	 * @param $query
	 */
	public function change_wp_native_users_query( $query ) {
		if ( ! empty( $query->query_vars['um_location_search'] ) ) {

			$clauses      = $query->meta_query->get_clauses();
			$having       = ' HAVING ';
			$having_array = array();
			foreach ( $query->query_vars['um_location_search']['fields'] as $field_key ) {
				$query->query_fields .= str_replace( '{lat_value}', $clauses[ $field_key . '_lat' ]['alias'] . '.meta_value', str_replace( '{lng_value}', $clauses[ $field_key . '_lng' ]['alias'] . '.meta_value', str_replace( '{key}', $field_key, $query->query_vars['um_location_search']['select'] ) ) );
				$having_array[]       = str_replace( '{key}', $field_key, $query->query_vars['um_location_search']['having'] );
			}

			$having .= '( ' . implode( ' OR ', $having_array ) . ' )';

			$query->query_where .= $having;
		}
	}

	/**
	 * @param $query
	 * @param $directory_data
	 */
	public function search_by_coord_meta( $query, $directory_data ) {
		global $wpdb;

		$key = UM()->options()->get( 'um_google_maps_js_api_key' );
		if ( empty( $key ) ) {
			return;
		}

		if ( empty( $directory_data['show_map'] ) ) {
			return;
		}

		if ( ! empty( $directory_data['map_search_by_moving'] ) ) {

			$directory_data['user_location_fields'] = maybe_unserialize( $directory_data['user_location_fields'] );
			if ( is_string( $directory_data['user_location_fields'] ) ) {
				$directory_data['user_location_fields'] = array( $directory_data['user_location_fields'] );
			}

			if ( ! isset( $directory_data['map_search_type'] ) || 'visible_bounds' === $directory_data['map_search_type'] ) {
				// phpcs:disable WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`
				if ( ! isset( $_POST['map_ne'] ) ) {
					$ne_lat = 90;
					$ne_lng = 180;
				} else {
					$ne_array = explode( ',', $_POST['map_ne'] );
					$ne_array = array_map( 'floatval', $ne_array );

					list( $ne_lat, $ne_lng ) = $ne_array;
				}

				if ( ! isset( $_POST['map_sw'] ) ) {
					$sw_lat = -90;
					$sw_lng = -180;
				} else {
					$sw_array = explode( ',', $_POST['map_sw'] );
					$sw_array = array_map( 'floatval', $sw_array );

					list( $sw_lat, $sw_lng ) = $sw_array;
				}
				// phpcs:enable WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`

				// for some zooms when bounds already full-world
				if ( $sw_lng >= $ne_lng ) {
					$ne_lng = 180;
					$sw_lng = -180;
				}

				if ( ! empty( $directory_data['user_location_fields'] ) ) {
					$where = array();
					foreach ( $directory_data['user_location_fields'] as $i => $field_key ) {
						$i = esc_sql( $i );
						// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $i is early escaped
						if ( $sw_lng > $ne_lng ) {
							$where[] = $wpdb->prepare( "( CAST( umm_locations_lat{$i}.um_value AS DECIMAL(16,14) ) BETWEEN %f AND %f AND ( CAST( umm_locations_lng{$i}.um_value AS DECIMAL(17,14) ) BETWEEN %f AND %f OR CAST( umm_locations_lng{$i}.um_value AS DECIMAL(17,14) ) BETWEEN %f AND %f ) AND umm_locations_lat{$i}.um_value != '' AND umm_locations_lng{$i}.um_value != '' )", $sw_lat, $ne_lat, $sw_lng, 180, -180, $ne_lng );
						} else {
							$where[] = $wpdb->prepare( "( CAST( umm_locations_lat{$i}.um_value AS DECIMAL(16,14) ) BETWEEN %f AND %f AND CAST( umm_locations_lng{$i}.um_value AS DECIMAL(17,14) ) BETWEEN %f AND %f AND umm_locations_lat{$i}.um_value != '' AND umm_locations_lng{$i}.um_value != '' )", $sw_lat, $ne_lat, $sw_lng, $ne_lng );
						}

						$query->joins[] = $wpdb->prepare( "LEFT JOIN {$wpdb->prefix}um_metadata umm_locations_lat{$i} ON ( umm_locations_lat{$i}.user_id = u.ID AND umm_locations_lat{$i}.um_key = %s )", $field_key . '_lat' );
						$query->joins[] = $wpdb->prepare( "LEFT JOIN {$wpdb->prefix}um_metadata umm_locations_lng{$i} ON ( umm_locations_lng{$i}.user_id = u.ID AND umm_locations_lng{$i}.um_key = %s )", $field_key . '_lng' );
						// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $i is early escaped
					}

					if ( ! empty( $where ) ) {
						$query->where_clauses[] = '(' . implode( ' OR ', $where ) . ')';
					}
				}
			} elseif ( 'radius' === $directory_data['map_search_type'] ) {
				// phpcs:disable WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`
				if ( isset( $_POST['search_lat'] ) && $_POST['search_lng'] ) {

					$radius          = ! empty( $directory_data['map_search_predefined_radius'] ) ? $directory_data['map_search_predefined_radius'] : 1;
					$distance_metric = 'km' === $directory_data['map_search_distance_unit'] ? 6371 : 3959;

					$pos_lat = ! empty( $_POST['search_lat'] ) ? (float) $_POST['search_lat'] : 0;
					$pos_lng = ! empty( $_POST['search_lng'] ) ? (float) $_POST['search_lng'] : 0;
					// phpcs:enable WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`

					if ( ! empty( $directory_data['user_location_fields'] ) ) {
						$where = array();

						foreach ( $directory_data['user_location_fields'] as $i => $field_key ) {
							$i = esc_sql( $i );
							// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $i is early escaped
							$where[]        = $wpdb->prepare( "( %d * ACOS( COS( RADIANS( umm_locations_lat{$i}.um_value ) ) * COS( RADIANS( %f ) ) * COS( RADIANS( %f ) - RADIANS( umm_locations_lng{$i}.um_value ) ) + SIN( RADIANS( umm_locations_lat{$i}.um_value ) ) * SIN( RADIANS( %f ) ) ) ) < %d", $distance_metric, $pos_lat, $pos_lng, $pos_lat, $radius );
							$query->joins[] = $wpdb->prepare( "LEFT JOIN {$wpdb->prefix}um_metadata umm_locations_lat{$i} ON ( umm_locations_lat{$i}.user_id = u.ID AND umm_locations_lat{$i}.um_key = %s )", $field_key . '_lat' );
							$query->joins[] = $wpdb->prepare( "LEFT JOIN {$wpdb->prefix}um_metadata umm_locations_lng{$i} ON ( umm_locations_lng{$i}.user_id = u.ID AND umm_locations_lng{$i}.um_key = %s )", $field_key . '_lng' );
							// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $i is early escaped
						}

						if ( ! empty( $where ) ) {
							$query->where_clauses[] = '(' . implode( ' OR ', $where ) . ')';
						}
					}
				}
			}
		}
	}

	/**
	 * @param array $data_array
	 * @param int $user_id
	 * @param array $directory_data
	 *
	 * @return array
	 */
	public function add_maps_args( $data_array, $user_id, $directory_data ) {
		$key = UM()->options()->get( 'um_google_maps_js_api_key' );
		if ( empty( $key ) ) {
			return $data_array;
		}

		if ( empty( $directory_data['user_location_fields'] ) ) {
			return $data_array;
		}

		$directory_data['user_location_fields'] = maybe_unserialize( $directory_data['user_location_fields'] );
		if ( is_string( $directory_data['user_location_fields'] ) ) {
			$directory_data['user_location_fields'] = array( $directory_data['user_location_fields'] );
		}

		// phpcs:disable WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`
		if ( ! empty( $directory_data['map_search_by_moving'] ) && ! empty( $_POST['map_ne'] ) && ! empty( $_POST['map_sw'] ) ) {
			$ne_array = explode( ',', $_POST['map_ne'] );
			$ne_array = array_map( 'floatval', $ne_array );

			$sw_array = explode( ',', $_POST['map_sw'] );
			$sw_array = array_map( 'floatval', $sw_array );

			list( $ne_lat, $ne_lng ) = $ne_array;
			list( $sw_lat, $sw_lng ) = $sw_array;
		}
		// phpcs:enable WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`

		if ( ! empty( $directory_data['user_location_infowindow_data'] ) ) {
			$directory_data['user_location_infowindow_data'] = maybe_unserialize( $directory_data['user_location_infowindow_data'] );

			if ( is_array( $directory_data['user_location_infowindow_data'] ) ) {
				foreach ( $directory_data['user_location_infowindow_data'] as $key ) {
					if ( ! $key ) {
						continue;
					}

					$value = um_filtered_value( $key );

					if ( ! $value ) {
						continue;
					}

					$data_array[ $key ] = $value;
				}
			}
		}

		$marker_type = get_post_meta( $directory_data['form_id'], '_um_user_location_marker_type', true );
		if ( 'role' === $marker_type ) {
			$role_key  = UM()->roles()->get_priority_user_role( $user_id );
			$role_meta = UM()->roles()->role_data( $role_key );

			if ( isset( $role_meta['locations_media_icon']['url'] ) && '' !== $role_meta['locations_media_icon']['url'] ) {
				$marker_image_url = $role_meta['locations_media_icon']['url'];
			} else {
				$marker_image_url = um_get_user_avatar_url( $user_id );
			}
		} elseif ( 'classic' === $marker_type ) {
			$marker_image_url = '';
		} else {
			$marker_image_url = um_get_user_avatar_url( $user_id );
		}

		foreach ( $directory_data['user_location_fields'] as $metakey ) {
			$user_lat = get_user_meta( $user_id, $metakey . '_lat', true );
			$user_lng = get_user_meta( $user_id, $metakey . '_lng', true );

			if ( '' !== $user_lat && '' !== $user_lng ) {
				// phpcs:disable WordPress.Security.NonceVerification -- early nonce verification in `ajax_get_members()`
				if ( ! empty( $directory_data['map_search_by_moving'] ) && ! empty( $_POST['map_ne'] ) && ! empty( $_POST['map_sw'] ) ) {
					if ( (float) $user_lat >= $sw_lat && (float) $user_lat <= $ne_lat ) {

						if ( $sw_lng > $ne_lng ) {
							if ( ( (float) $user_lng >= $sw_lng && (float) $user_lng <= 180 ) || ( (float) $user_lng >= -180 && (float) $user_lng <= $ne_lng ) ) {
								$data_array[ $metakey . '_lat' ] = $user_lat;
								$data_array[ $metakey . '_lng' ] = $user_lng;

								$data_array['avatar_url'] = $marker_image_url;
							}
						} else {
							if ( $user_lng >= $sw_lng && $user_lng <= $ne_lng ) {
								$data_array[ $metakey . '_lat' ] = $user_lat;
								$data_array[ $metakey . '_lng' ] = $user_lng;

								$data_array['avatar_url'] = $marker_image_url;
							}
						}
					}
				} else {
					$data_array[ $metakey . '_lat' ] = $user_lat;
					$data_array[ $metakey . '_lng' ] = $user_lng;

					$data_array['avatar_url'] = $marker_image_url;
				}
			}
		}

		return $data_array;
	}

	/**
	 * @param array $response
	 * @param array $directory_data
	 *
	 * @return array
	 */
	public function add_maps_fields( $response, $directory_data ) {
		$key = UM()->options()->get( 'um_google_maps_js_api_key' );
		if ( empty( $key ) ) {
			return $response;
		}

		if ( empty( $directory_data['user_location_fields'] ) ) {
			return $response;
		}

		$directory_data['user_location_fields'] = maybe_unserialize( $directory_data['user_location_fields'] );
		if ( is_string( $directory_data['user_location_fields'] ) ) {
			$directory_data['user_location_fields'] = array( $directory_data['user_location_fields'] );
		}

		$map_fields = $directory_data['user_location_fields'];
		$map_fields = array_map(
			function( $item ) use ( $map_fields ) {
				$field_title = UM()->fields()->get_field_title( $map_fields[ $item ] );

				if ( ! empty( $field_title ) ) {
					$item = $field_title;
				} else {
					$item = $map_fields[ $item ];
				}
				return $item;
			},
			array_flip( $map_fields )
		);

		$response['map_fields'] = $map_fields;

		if ( ! empty( $this->new_pagination ) ) {
			$response['pagination'] = UM()->member_directory()->calculate_pagination( $directory_data, $this->new_pagination );
			// translators: %s is the users count.
			$response['pagination']['header']        = $directory_data['no_users'] . ' ' . sprintf( __( 'But we found %s nearest members.', 'um-user-locations' ), $this->new_pagination );
			$response['pagination']['header_single'] = $directory_data['no_users'] . ' ' . __( 'But we found the nearest member.', 'um-user-locations' );
		}

		return $response;
	}
}
