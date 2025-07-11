=== Ultimate Member - User Locations ===
Author URI: https://ultimatemember.com/
Plugin URI: https://ultimatemember.com/extensions/user-locations
Contributors: ultimatemember, champsupertramp, nsinelnikov
Tags: user location, member, membership, user-profile, user-registration
Requires at least: 5.5
Tested up to: 6.7
Stable tag: 1.1.4
License: GNU Version 2 or Any Later Version
License URI: http://www.gnu.org/licenses/gpl-3.0.txt
Requires UM core at least: 2.7.0

Using the Google Maps API, display users on a map on the member directory page and allow users to add their location via their profile.

== Description ==

= Important =

User Locations extension is integrated with the [Google Maps Platform](https://developers.google.com/maps). The extension requires creating an app within the platform and activating the API. The Google Maps Platform is a paid service that comes with $200 in free usage each month. To view the Google Maps usage pricing please click [here](https://cloud.google.com/maps-platform/pricing/).

= Key Features: =

* Add user location field to registration and profile forms to allow users to add their location.
* Ability to auto-detect user location (requires users to click the pin icon on the location field and give permission to share location)
* Adds a user location search field to member directory to allow people to search for users by location
* Adds a map to member directory
* User Clustering for users in close proximity on member directory map
* User avatars appear on the map to show the location of users
* When avatar is clicked a small profile card will appear on the map which shows the avatar, display name and information you choose in member directory settings
* Allow people to find users by dragging the map

= Development * Translations =

Want to add a new language to Ultimate Member? Great! You can contribute via [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/ultimate-member).

If you are a developer and you need to know the list of UM Hooks, make this via our [Hooks Documentation](https://docs.ultimatemember.com/article/1324-hooks-list).

= Documentation & Support =

Got a problem or need help with Ultimate Member? Head over to our [documentation](http://docs.ultimatemember.com/) and perform a search of the knowledge base. If you can’t find a solution to your issue then you can create a topic on the [support forum](https://wordpress.org/support/plugin/um-forumwp).

== Installation ==

1. Activate the plugin
2. That's it. Go to Ultimate Member > Settings > Extensions > User Locations to customize plugin options
3. For more details, please visit the official [Documentation](https://docs.ultimatemember.com/article/1545-user-location-setup) page.

== Changelog ==

= 1.1.4: November 18, 2024 =

* Fixed: "Load textdomain just in time" issue

= 1.1.3: May 22, 2024 =

* Fixed: PHP errors while switching to custom metatable
* Tweak: Added Ultimate Member as required plugins
* Tweak: Compatibility with new FontAwesome library in Ultimate Member plugin

= 1.1.2: February 21, 2024 =

* Fixed: Integration with Member directory
* Tweak: Enhancements related to WPCS

= 1.1.1: December 11, 2023 =

* Tweak: Using enqueue scripts suffix from UM core class. Dependency from UM core 2.7.0
* Tweak: Enhancements related to WPCS
* Fixed: Member directory map navigator object
* Fixed: Error that occurs if try to init the map in the directory without a map

= 1.1.0: October 11, 2023 =

* Added: `aria-invalid` and `aria-errormessage` attributes to the fields on UM Forms
* Fixed: Case when extension isn't active based on dependency, but we can provide the license key field

= 1.0.9: July 3, 2023 =

* Fixed: Removed `extract()`
* Tweak: Ultimate Member 2.6.7 compatibility

= 1.0.8: June 14, 2023 =

* Added: French translations
* Tweak: Template overwrite versioning

= 1.0.7: December 14, 2022 =

* Fixed: PHP warning on the locations_map shortcode
* Fixed: Users displaying on the locations_map shortcode based on their privacy

= 1.0.6: February 9, 2022 =

* Added: 'um_user_locations_geocode_args_init' hook for ability to customize Geocoding attributes
* Added: 'um_user_locations_field_show_map' hook for ability to hide the field's map
* Fixed: Search by radius and predefined zoom based on object response from Google API
* Fixed: Translations of the labels
* Fixed: Extension settings structure

* Templates required update:
  - map.php

* Cached and optimized/minified assets(JS/CSS) must be flushed/re-generated after upgrade

= 1.0.5: April 28, 2021 =

* Fixed: Registration and Profile forms handler
* Fixed: Integration with Profile Completeness extension

= 1.0.4: April 14, 2021 =

* Added: Search by radius functionality on the member directory
* Added: Ability to turn off clustering and OMS( Overlapping Marker Spiderfier ) by the JS hook
* Added: Ability to show the nearest members if the search results are empty
* Added: Location filter / location admin filter
* Added: Ability to select the location by the clicking on the map
* Fixed: OMS( Overlapping Marker Spiderfier ) library using on the map shortcode
* Fixed: Issue with the first map's idle
* Fixed: Distance sorting calculation

= 1.0.3: December 11, 2020 =

* Added: OMS( Overlapping Marker Spiderfier ) library for getting visible the clustering markers in the same place
* Added: Error/notices blocks for the location field
* Added: Ability to select map marker icon for role
* Added: Ability to select what markers type to use in member directory
* Added: Distance field based on User Location fields
* Added: Ability to add the Distance field to User Profile meta section
* Added: Ability to show the Distance field on the Member Directory
* Added: Ability to sort by nearby members based on distance and current user geolocation
* Added: [um_user_locations_map] shortcode
* Fixed: Getting bounds on the first member directory page loading (not default starting lat/lng/zoom)
* Fixed: Getting bounds on the first member directory page loading (Show results after search and searched by location. Added timeout for setting bound for invisible map)
* Fixed: Uninstall process

= 1.0.2: August 11, 2020 =

* Added: JS filter 'um_user_locations_marker_data' to customize the markers' data
* Added: JS filter 'um_user_locations_marker_clustering_options' to customize the markers' clustering options
* Added: Integration with Profile Completeness
* Added: Starting coordinates/zoom for the member directory
* Fixed: Google Maps init when Internet connection is slow
* Fixed: Keypress "Enter" on user location field autocomplete
* Fixed: Locations fields add/remove and option 'um_map_user_fields'
* Fixed: Map localization
* Fixed: Location field title in the markers' title attribute
* Fixed: Mobile device styles for the member directory map
* Fixed: Getting map bounds
* Fixed: Integration with Social Login form in popup and user Location field

= 1.0.1: April 1, 2020 =

* Added: Ability to edit member directory map via a template in theme
* Added: JS hooks for 3-rd party integrations

= 1.0.0: March 03, 2020 =

- Initial release
