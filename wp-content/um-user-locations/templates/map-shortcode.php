<?php
/**
 * Template for the Location map shortcode
 *
 * Used:  Shortcode
 * Call:  locations_map();
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-locations/map-shortcode.php
 *
 * @version  1.0.8
 * @var int    $zoom
 * @var string $lat
 * @var string $lng
 * @var string $map_sw
 * @var string $map_ne
 * @var string $field
 * @var string $field_title
 * @var string $marker_type
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<script type="text/template" id="tmpl-um-user-location-map-shortcode-marker-infowindow">

	<div class="um_user_location_gmap_infowindow">
		<div class="um_user_location_infowindow_avatar">
			<a href="{{{data.userdata.profile_url}}}" target="_blank">{{{data.userdata.avatar}}}</a>
		</div>
		<div class="um_user_location_infowindow_content_wrapper">
			<div class="um_user_location_infowindow_title">
				{{{data.userdata.display_name}}}
			</div>
			<div class="um_user_location_infowindow_content">
				<a href="{{{data.userdata.profile_url}}}" class="um_user_location_infowindow_more_link" target="_blank">
					<?php _e( 'View profile', 'um-user-locations' ) ?>
				</a>
			</div>
		</div>
	</div>

</script>

<div class="um-member-map-shortcode um-user-location-hidden-map"
	 data-zoom="<?php echo esc_attr( $zoom ); ?>"
	 data-lat="<?php echo esc_attr( $lat ); ?>"
	 data-lng="<?php echo esc_attr( $lng ); ?>"
	 data-sw="<?php echo esc_attr( $map_sw ); ?>"
	 data-ne="<?php echo esc_attr( $map_ne ); ?>"
	 data-field="<?php echo esc_attr( $field ); ?>"
	 data-field_title="<?php echo esc_attr( $field_title ); ?>"
	 data-marker_type="<?php echo esc_attr( $marker_type ); ?>"
	 style="height: <?php echo esc_attr( $height ); ?>px"></div>