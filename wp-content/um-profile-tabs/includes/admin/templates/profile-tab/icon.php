<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$tab_id = $post->ID;
if ( UM()->external_integrations()->is_wpml_active() ) {
	global $sitepress;

	$default_lang_tab_id = $sitepress->get_object_id( $tab_id, 'um_profile_tabs', true, $sitepress->get_default_language() );
	if ( $default_lang_tab_id && $default_lang_tab_id !== $tab_id ) {
		$tab_id = $default_lang_tab_id;
		?>
		<p><?php esc_html_e( 'These settings are obtained from a Profile tab in the default language', 'um-profile-tabs' ); ?></p>
		<?php
	}
}

$link_type = get_post_meta( $tab_id, 'um_link_type', true );
$link_type = ! empty( $link_type ) ? $link_type : 0;

$tab_slug = '';
$tab_link = '';
if ( empty( $link_type ) ) {
	$tab_slug = get_post_meta( $tab_id, 'um_tab_slug', true );
	$tab_slug = ! empty( $tab_slug ) ? $tab_slug : '';
} else {
	$tab_link = get_post_meta( $tab_id, 'um_tab_link', true );
	$tab_link = ! empty( $tab_link ) ? $tab_link : '';
}

UM()->admin_forms(
	array(
		'class'     => 'um-profile-tab-icon um-top-label',
		'prefix_id' => 'um_profile_tab',
		'fields'    => array(
			array(
				'id'    => '_icon',
				'type'  => 'icon',
				'label' => __( 'Icon', 'um-profile-tabs' ),
				'value' => get_post_meta( $tab_id, 'um_icon', true ),
			),
			array(
				'id'      => '_link_type',
				'type'    => 'select',
				'label'   => __( 'Link type', 'um-profile-tabs' ),
				'value'   => $link_type,
				'options' => array(
					0 => __( 'Internal', 'um-profile-tabs' ),
					1 => __( 'Remote', 'um-profile-tabs' ),
				),
			),
			array(
				'id'          => '_tab_slug',
				'type'        => 'text',
				'label'       => __( 'Slug', 'um-profile-tabs' ),
				'value'       => $tab_slug,
				'conditional' => array( '_link_type', '=', 0 ),
			),
			array(
				'id'          => '_tab_link',
				'type'        => 'text',
				'label'       => __( 'Remote Link', 'um-profile-tabs' ),
				'value'       => $tab_link,
				'conditional' => array( '_link_type', '=', 1 ),
			),
		),
	)
)->render_form();
