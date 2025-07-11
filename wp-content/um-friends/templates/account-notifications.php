<?php
/**
 * Template for the UM Friends. The list of user friends
 *
 * Page: Account > Notifications
 * Call: um_friends_account_tab()
 * Hook: 'um_account_content_hook_notifications'
 *
 * @version 2.2.5
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-friends/account-notifications.php
 * @var bool $show_new_friend
 * @var bool $show_new_friend_request
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div class="um-field" data-key="">
	<div class="um-field-label"><strong><?php _e( 'Friends', 'um-friends' ); ?></strong></div>

	<?php if ( $show_new_friend ) { ?>
		<div class="um-field-area">
			<label class="um-field-checkbox<?php if ( ! empty( $_enable_new_friend ) ) { ?> active<?php } ?>">
				<input type="checkbox" name="_enable_new_friend" value="1" <?php checked( ! empty( $_enable_new_friend ) ) ?> />
				<span class="um-field-checkbox-state">
					<i class="um-icon-android-checkbox-<?php echo ! empty( $_enable_new_friend ) ? 'outline' : 'outline-blank'; ?>"></i>
				</span>
				<span class="um-field-checkbox-option"><?php _e( 'I have got a new friend', 'um-friends' ); ?></span>
			</label>

			<div class="um-clear"></div>
		</div>
	<?php }

	if ( $show_new_friend_request ) { ?>
		<div class="um-field-area">
			<label class="um-field-checkbox<?php if ( ! empty( $_enable_new_friend_request ) ) { ?> active<?php } ?>">
				<input type="checkbox" name="_enable_new_friend_request" value="1" <?php checked( ! empty( $_enable_new_friend_request ) ) ?> />
				<span class="um-field-checkbox-state">
					<i class="um-icon-android-checkbox-<?php echo ! empty( $_enable_new_friend_request ) ? 'outline' : 'outline-blank'; ?>"></i>
				</span>
				<span class="um-field-checkbox-option"><?php _e( 'I have got a new friend request', 'um-friends' ); ?></span>
			</label>

			<div class="um-clear"></div>
		</div>
	<?php } ?>
</div>
