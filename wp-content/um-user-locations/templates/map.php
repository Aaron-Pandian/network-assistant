<?php
/**
 * Template for the Location map
 *
 * Used:  Member Directory or Shortcode
 * Call:  header_add_map();
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-locations/map.php
 *
 * @version  1.0.8
 * @var array  $args
 * @var bool   $not_searched
 * @var int    $zoom
 * @var string $lat
 * @var string $lng
 * @var string $search_lat
 * @var string $search_lng
 * @var string $dynamic_search
 * @var string $distance_unit
 * @var string $height
 * @var string $predefined_radius
 * @var string $map_sw
 * @var string $map_ne
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="um-member-directory-map<?php if ( $args['must_search'] && $not_searched ) { ?> um-member-directory-hidden-map<?php } ?>"
     data-zoom="<?php echo esc_attr( $zoom ) ?>" data-lat="<?php echo esc_attr( $lat ) ?>" data-lng="<?php echo esc_attr( $lng ) ?>"
     data-distance-unit="<?php echo esc_attr( $distance_unit ) ?>" data-predefined-radius="<?php echo esc_attr( $predefined_radius ) ?>"
     data-dynamic-search="<?php echo esc_attr( $dynamic_search ) ?>" data-search="<?php echo esc_attr( $search ) ?>"<?php if ( $dynamic_search ) { ?> data-sw="<?php echo esc_attr( $map_sw ) ?>" data-ne="<?php echo esc_attr( $map_ne ) ?>"<?php } ?>
     <?php if ( $search ) { ?>data-search-lat="<?php echo esc_attr( $search_lat ) ?>" data-search-lng="<?php echo esc_attr( $search_lng ) ?>"<?php } ?>
     style="height: <?php echo esc_attr( $height ) ?>"></div>

<?php if ( ! empty( $args['map_search_by_moving'] ) ) { ?>

	<div class="um-member-directory-map-controls um-member-directory-map-moving-search">
		<div class="um-member-directory-map-controls-half">
			<div class="um-field um-field-location um-field-user-location" data-key="location">
				<div class="um-field-area">
					<input class="um_user_location_g_autocomplete" type="text"
					       name="location" id="location" value="" data-key="location" />
					<a href="javascript:void(0);" class="um_current_user_location"><i class="um-faicon-map-marker" aria-hidden="true"></i></a>
				</div>
			</div>
		</div>
	</div>

<?php }
