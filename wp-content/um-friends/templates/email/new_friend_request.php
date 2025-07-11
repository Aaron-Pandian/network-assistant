<?php
/**
 * Template for the "New Friend Request Notification".
 * Send a notification to user when they receive a new friend request.
 *
 * This template can be overridden by copying it to {your-theme}/ultimate-member/email/new_friend_request.php
 *
 * @version 2.2.5
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div style="max-width: 560px;padding: 20px;background: #ffffff;border-radius: 5px;margin:40px auto;font-family: Open Sans,Helvetica,Arial;font-size: 15px;color: #666;">

	<div style="color: #444444;font-weight: normal;">
		<div style="text-align: center;font-weight:600;font-size:26px;padding: 10px 0;border-bottom: solid 3px #eeeeee;">{site_name}</div>

		<div style="clear:both"></div>
	</div>

	<div style="padding: 0 30px 30px 30px;border-bottom: 3px solid #eeeeee;">

		<div style="padding: 30px 0;font-size: 24px;text-align: center;line-height: 30px;">Hi {receiver}, {friend} has just sent you a friend request on {site_name}.</div>

		<div style="padding: 0 0;font-size: 16px;text-align: center;line-height: 30px;">View their profile to accept/reject this friendship request: <a href="{friend_profile}" style="color: #3ba1da;text-decoration: none;">{friend_profile}</a></div>
	</div>

	<div style="color: #999;padding: 20px 30px">

		<div style="">Thank you!</div>
		<div style="">The <a href="{site_url}" style="color: #3ba1da;text-decoration: none;">{site_name}</a> Team</div>

	</div>

</div>
