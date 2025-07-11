=== Ultimate Member - Friends ===
Author URI: https://ultimatemember.com/
Plugin URI: https://ultimatemember.com/extensions/friends/
Contributors: ultimatemember, champsupertramp, nsinelnikov
Tags: friends, community, discussion
Requires at least: 5.5
Tested up to: 6.7
Stable tag: 2.3.3
License: GNU Version 2 or Any Later Version
License URI: http://www.gnu.org/licenses/gpl-3.0.txt
Requires UM core at least: 2.9.0

With the friends extension you can increase user interaction on your site by allowing users to become friends by sending & accepting/rejecting friend requests.

== Description ==

With the friends extension you can increase user interaction on your site by allowing users to become friends by sending & accepting/rejecting friend requests.

= Key Features: =

* Adds a friendship system to your website
* Displays followers/following stats on their profile
* Adds a send friendship request button to profile cards on member directories
* Adds a send friendship request button to every user’s profile
* Adds a tab to profiles which shows a list of user’s friends
* Adds a tab to profiles which shows a list of user’s friend requests.
* Allow users to restrict their profile to their friends only
* Users receive an email notification when someone sends them a friend request
* Users receive an email notification when someone accepts their friend request

= Integrations with other extensions: =

* Notifications – Users can receive a notification when someone sends them a friend request and when someone accepts their friend request
* Private Messages – Limit pivate messaging to friends so only friends can message each other
* Social Activity – Limit activity displayed in the feed to activity from friends only

Read about all of the plugin's features at [Ultimate Member - Friends](https://ultimatemember.com/extensions/friends/)

= Development * Translations =

Want to add a new language to Ultimate Member? Great! You can contribute via [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/ultimate-member).

If you are a developer and you need to know the list of UM Hooks, make this via our [Hooks Documentation](https://docs.ultimatemember.com/article/1324-hooks-list).

= Documentation & Support =

Got a problem or need help with Ultimate Member? Head over to our [documentation](http://docs.ultimatemember.com/) and perform a search of the knowledge base. If you can’t find a solution to your issue then you can create a topic on the [support forum](https://wordpress.org/support/plugin/ultimate-member).

== Installation ==

1. Activate the plugin
2. That's it. Go to Ultimate Member > Settings > Extensions > Friends to customize plugin options
3. For more details, please visit the official [Documentation](http://docs.ultimatemember.com/article/249-friends-setup) page.

== Changelog ==

= 2.3.3: November 14, 2024 =

* Added: Compatibility with Ultimate Member 2.9.0 and Action Scheduler for email sending
* Fixed: "Load textdomain just in time" issue

= 2.3.2: May 22, 2024 =

* Fixed: Loading assets in block builder
* Tweak: Added Ultimate Member as required plugins

= 2.3.1: February 21, 2024 =

* Updated: User Photos access to actions for visible for friends albums and photos in them

= 2.3.0: December 20, 2023 =

* Fixed: Note and Album privacy settings. Performance for Activity wall loading.

= 2.2.9: December 11, 2023 =

* Tweak: Using enqueue scripts suffix from UM core class. Dependency from UM core 2.7.0
* Fixed: 'um_user_permissions_filter' hook attributes

= 2.2.8: October 11, 2023 =

* Added: Shortcode `[ultimatemember_friends_button]`
* Fixed: Displaying "Notifications Account Tab" setting
* Fixed: User mentions suggestions and parse while posting
* Fixed: Case when extension isn't active based on dependency, but we can provide the license key field

= 2.2.7: August 25, 2023 =

* Added: JS confirm when unfriend the user
* Added: JS confirm when cancel friend request the user
* Added: JS confirm when reject friend request the user
* Fixed: Reject friend request
* Fixed: Update notification count in user profile after friends actions (friend/un-friend/reject/cancel/accept)

= 2.2.6: August 8, 2023 =

* Added: Extra-validation in AJAX actions
* Fixed: Removed `extract()`
* Fixed: Hide "Hide friends stats" and "Hide friend button" settings on the "Edit Member Directory" page

= 2.2.5: May 30, 2023 =

* Tweak: Template overwrite versioning

= 2.2.4: August 17, 2022 =

* Added: Integration with UM: User Photos and album visibility

= 2.2.3: February 9, 2022 =

* Fixed: Counters number of users - friends, friends requests, friends requests sent
* Fixed: Extension settings structure

= 2.2.2: July 20, 2021 =

* Tweak: WP5.8 widgets screen compatibility

= 2.2.1: May 7, 2021 =

* Added: Profile tab privacy for friends and followers only

= 2.2.0: March 29, 2021 =

* Added: Dependencies with Online extension and Online Friends widget
* Fixed: PHP notices when a user is mentioned in the Social Wall
* Tweak: Updated the using dropdown.js library functions

= 2.1.9: August 24, 2020 =

* Fixed: Friend request deletion

= 2.1.8: August 11, 2020 =

* Added: Fields privacy setting 'Only friends'.
* Added: *.pot file for the translations
* Tweak: apply_shortcodes() function support
* Fixed: Friends requests note when there are the requests
* Fixed: Security issue with user IDs in data attribute

= 2.1.7: April 1, 2020 =

* Added: User role setting "Friends limit" - the maximum number of friends.
* Tweak: Optimized UM:Notifications integration

= 2.1.6: January 13, 2020 =

* Tweak: Integration with Ultimate Member 2.1.3 and UM metadata table
* Changed: Account notifications layout

= 2.1.5: November 13, 2019 =

* Fixed: Loading member directories with friends button

= 2.1.4: November 11, 2019 =

* Added: Sanitize functions for request variables
* Added: esc_attr functions to avoid XSS vulnerabilities
* Added: ability to change templates in theme via universal method UM()->get_template()
* Fixed: Style dependencies
* Fixed: Replace placeholders
* Fixed: Online friends widget
* Tweak: Replaced menu UI function
* Optimized usermeta for Account submit security

= 2.1.3: July 16, 2019 =

* Added: Ability to rewrite templates via themes
* Added: Widget with online friends
* Fixed: Profile Tabs
* Fixed: Uninstall process

= 2.1.2: May 22, 2019 =

* Tweak: Future released extensions compatibility

= 2.1.1: May 14, 2019 =

* Tweak: Future released extensions compatibility
* Fixed biography description in layout

= 2.1.0: May 14, 2019 =

* Tweak: Wordpress.com compatibility

= 2.0.9: March 29, 2019 =

* Added: Friends mentions in social activity
* Tweak: correct dbDelta syntax

= 2.0.8: March 29, 2019 =

* Added: Hook for displaying "Friends" button

= 2.0.7: November 27, 2018 =

* Fixed: AJAX vulnerabilities
* Optimized: JS/CSS enqueue

= 2.0.6: October 2, 2018 =

* Added: User Groups Integration

= 2.0.5: August 13, 2018 =

* Fixed: WP native AJAX handlers

= 2.0.4: August 10, 2018 =

* Fixed: Backward compatibility for Privacy Settings

= 2.0.3: August 10, 2018 =

* Fixed: Privacy account settings for sites with different languages

= 2.0.2: June 18, 2018 =

* GDPR compatibility on delete users

= 2.0.1: October 17, 2017 =

* Fixed sorting members directory by “most friends” and “least friends”
* Fixed allow admin to edit friends only profile
* Tweak: UM2.0 compatibility

= 1.0.1: December 8, 2016 =

* Added: English Translation files
* Fixed: Remove notices from ajax requests
* Fixed: Plugin updater
* Fixed: Friend request count

= 1.0.0: October 4, 2016 =

* Initial release
