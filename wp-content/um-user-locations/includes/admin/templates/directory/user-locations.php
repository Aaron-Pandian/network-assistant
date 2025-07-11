<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post_id;

$user_fields = array();
foreach ( UM()->builtin()->all_user_fields() as $key => $arr ) {
	$user_fields[ $key ] = isset( $arr['title'] ) ? $arr['title'] : '';
}
$_um_infowindow_fields = get_post_meta( $post_id, '_um_user_location_infowindow_data', true );

$_um_user_location_fields = get_post_meta( $post_id, '_um_user_location_fields', true );
$_um_user_location_fields = empty( $_um_user_location_fields ) ? '' : $_um_user_location_fields;

$marker_types = array(
	''        => __( 'Avatar marker', 'um-user-locations' ), // empty key for making old member directories with this empty value by default
	'role'    => __( 'User role marker', 'um-user-locations' ),
	'classic' => __( 'Classic Google marker', 'um-user-locations' ),
);
$marker_types = apply_filters( 'um_user_locations_markers_types', $marker_types );

$fields = array(
	array(
		'id'          => '_um_search_distance_closest',
		'type'        => 'checkbox',
		'label'       => __( 'Show nearest users when search results are empty', 'um-user-locations' ),
		'value'       => UM()->query()->get_meta_value( '_um_search_distance_closest' ),
		'conditional' => array( '_um_search', '=', 1 ),
	),
	array(
		'id'    => '_um_show_map',
		'type'  => 'checkbox',
		'label' => __( 'Show Map', 'um-user-locations' ),
		'value' => UM()->query()->get_meta_value( '_um_show_map' ),
	),
	array(
		'id'          => '_um_user_location_fields',
		'type'        => 'select',
		'label'       => __( 'Show this user location field at the map', 'um-user-locations' ),
		'tooltip'     => __( 'If empty, map will be hidden', 'um-user-locations' ),
		'value'       => $_um_user_location_fields,
		'options'     => UM()->User_Locations()->fields()->location_fields,
		'conditional' => array( '_um_show_map', '=', 1 ),
	),
	array(
		'id'          => '_um_map_height',
		'type'        => 'number',
		'label'       => __( 'Map height', 'um-user-locations' ),
		'value'       => UM()->query()->get_meta_value( '_um_map_height', null, UM()->options()->get( 'user_location_map_height' ) ),
		'size'        => 'small',
		'conditional' => array( '_um_show_map', '=', 1 ),
	),
	array(
		'id'          => '_um_map_starting_zoom',
		'type'        => 'number',
		'label'       => __( 'Starting map zoom level', 'um-user-locations' ),
		'tooltip'     => __( 'Pick a starting zoom level for the map. Eg: 12', 'um-user-locations' ),
		'size'        => 'small',
		'value'       => UM()->query()->get_meta_value( '_um_map_starting_zoom', null, 1 ),
		'conditional' => array( '_um_show_map', '=', 1 ),
	),
	array(
		'id'          => '_um_map_starting_coord_lat',
		'type'        => 'text',
		'label'       => __( 'Starting address latitude', 'um-user-locations' ),
		'tooltip'     => __( 'Pick a starting position for the map', 'um-user-locations' ),
		'size'        => 'small',
		'value'       => UM()->query()->get_meta_value( '_um_map_starting_coord_lat', null, '0' ),
		'conditional' => array( '_um_show_map', '=', 1 ),
	),
	array(
		'id'          => '_um_map_starting_coord_lng',
		'type'        => 'text',
		'label'       => __( 'Starting address longitude', 'um-user-locations' ),
		'tooltip'     => __( 'Pick a starting position for the map', 'um-user-locations' ),
		'size'        => 'small',
		'value'       => UM()->query()->get_meta_value( '_um_map_starting_coord_lng', null, '0' ),
		'conditional' => array( '_um_show_map', '=', 1 ),
	),
	array(
		'id'          => '_um_map_search_by_moving',
		'type'        => 'checkbox',
		'label'       => __( 'Enable user location search field', 'um-user-locations' ),
		'tooltip'     => __( 'Make it possible to search members via the location search field', 'um-user-locations' ),
		'conditional' => array( '_um_show_map', '=', 1 ),
		'value'       => UM()->query()->get_meta_value( '_um_map_search_by_moving' ),
	),
	array(
		'id'          => '_um_map_search_type',
		'type'        => 'select',
		'label'       => __( 'Choose a type of searching', 'um-user-location' ),
		'tooltip'     => __( 'Make it possible to search nearby members in selected radius or in visible bounds when dragging a map.', 'um-user-location' ),
		'conditional' => array( '_um_map_search_by_moving', '=', 1 ),
		'value'       => UM()->query()->get_meta_value( '_um_map_search_type', null, 'visible_bounds' ),
		'options'     => array(
			'visible_bounds' => __( 'Search by visible bounds', 'um-user-location' ),
			'radius'         => __( 'Search by radius', 'um-user-location' ),
		),
	),
	array(
		'id'          => '_um_map_search_predefined_radius',
		'type'        => 'number',
		'label'       => __( 'Nearby members searching radius', 'um-user-location' ),
		'tooltip'     => __( 'Set the radius from position for getting nearby members', 'um-user-location' ),
		'value'       => UM()->query()->get_meta_value( '_um_map_search_predefined_radius' ),
		'conditional' => array( '_um_map_search_type', '=', 'radius' ),
		'size'        => 'small',
	),
	array(
		'id'          => '_um_map_search_distance_unit',
		'type'        => 'select',
		'label'       => __( 'Distance unit of measurement', 'um-user-location' ),
		'options'     => array(
			'km'   => __( 'Kilometers', 'um-user-location' ),
			'mile' => __( 'Miles', 'um-user-location' ),
		),
		'size'        => 'small',
		'conditional' => array( '_um_map_search_type', '=', 'radius' ),
		'value'       => UM()->query()->get_meta_value( '_um_map_search_distance_unit' ),
	),

	array(
		'id'                  => '_um_user_location_infowindow_data',
		'type'                => 'multi_selects',
		'label'               => __( 'Choose field(s) to display in marker\'s infowindow', 'um-user-locations' ),
		'value'               => $_um_infowindow_fields,
		'conditional'         => array( '_um_show_map', '=', 1 ),
		'add_text'            => __( 'Add New Field', 'um-user-locations' ),
		'options'             => $user_fields,
		'show_default_number' => 0,
		'sorting'             => true,
	),
	array(
		'id'          => '_um_user_location_marker_type',
		'type'        => 'select',
		'label'       => __( 'Marker type', 'um-user-locations' ),
		'tooltip'     => __( 'Pick a marker\'s type', 'um-user-locations' ),
		'value'       => get_post_meta( $post_id, '_um_user_location_marker_type', true ),
		'options'     => $marker_types,
		'conditional' => array( '_um_show_map', '=', 1 ),
	),
);
?>

<div class="um-admin-metabox">
	<?php
	UM()->admin_forms(
		array(
			'class'     => 'um-member-directory-user-locations um-half-column',
			'prefix_id' => 'um_metadata',
			'fields'    => $fields,
		)
	)->render_form();
	?>
	<div class="clear"></div>
</div>
