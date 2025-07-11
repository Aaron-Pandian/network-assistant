<?php
/**
 * Template for the Location map infowindow
 *
 * Used:  Member Directory or Shortcode
 * Call:  header_add_map();
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-locations/infowindow.php
 *
 * @version  1.0.8
 * @var array $infowindow_fields
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<script type="text/template" id="tmpl-um-user-location-map-marker-infowindow">

	<div class="um_user_location_gmap_infowindow">
		<div class="um_user_location_infowindow_avatar">
			<a href="{{{data.userdata.profile_url}}}" target="_blank">{{{data.userdata.avatar}}}</a>
		</div>
		<div class="um_user_location_infowindow_content_wrapper">
			<div class="um_user_location_infowindow_title">
				{{{data.userdata.display_name}}}
			</div>
			<div class="um_user_location_infowindow_content">

				<?php foreach ( $infowindow_fields as $key ) {
					if ( empty( $key ) ) {
						continue;
					} ?>

					<# if ( typeof data.userdata['<?php echo $key; ?>'] !== 'undefined' ) { #>
						<div class="um-member-infowindow-line um-member-infowindow-line-<?php echo esc_attr( $key ); ?>"
						     data-key="<?php echo esc_attr( $key ); ?>">
							{{{data.userdata['<?php echo $key; ?>']}}}
						</div>
					<# } #>

				<?php } ?>

				<a href="{{{data.userdata.profile_url}}}" class="um_user_location_infowindow_more_link" target="_blank">
					<?php _e( 'View profile', 'um-user-locations' ) ?>
				</a>
			</div>
		</div>
	</div>

</script>
