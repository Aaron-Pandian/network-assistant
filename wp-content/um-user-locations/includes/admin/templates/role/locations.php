<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$role = $object['data'];
?>

<div class="um-admin-metabox">
	<?php
	UM()->admin_forms(
		array(
			'class'     => 'um-role-locations um-half-column',
			'prefix_id' => 'role',
			'fields'    => array(
				array(
					'id'    => '_um_locations_media_icon',
					'type'  => 'media',
					'label' => __( 'Role icon', 'um-user-locations' ),
					'value' => ! empty( $role['_um_locations_media_icon'] ) ? $role['_um_locations_media_icon'] : '',
				),
			),
		)
	)->render_form();
	?>
	<div class="clear"></div>
</div>
