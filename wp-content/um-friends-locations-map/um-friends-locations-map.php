<?php
/*
Plugin Name: UM Friends Locations Map
Description: Adds a shortcode to display a map of the logged-in user's approved friends using Ultimate Member.
Version: 2.2
Author: WhatsMyCity
*/

defined('ABSPATH') || exit;

function um_friends_locations_enqueue_scripts() {
    global $post;
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'friends_locations_map')) {
        wp_register_script('google-maps-api', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyBHq3VO05frnb9NyETMNCKcpScYC5a8tEM&callback=initFriendsMap', [], null, true);
        wp_register_script('markerclusterer', 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js', [], null, true);
    }
}
add_action('wp_enqueue_scripts', 'um_friends_locations_enqueue_scripts');

add_shortcode('friends_locations_map', 'whatsmycity_friends_locations_map');
function whatsmycity_friends_locations_map() {
    if (!is_user_logged_in()) return '<p>Please log in to see your friends' locations.</p>';

    $user_id = get_current_user_id();
    global $wpdb;

    // Check who lists current user as a friend
    $incoming = $wpdb->get_col($wpdb->prepare("
        SELECT user_id FROM {$wpdb->usermeta}
        WHERE meta_key = %s AND meta_value = 'approved'
    ", '_um_friend_' . $user_id));

    // Also check who the current user lists as friends
    $outgoing = get_users([
        'meta_query' => [[
            'key' => '_um_friend_' . $user_id,
            'value' => 'approved',
            'compare' => '='
        ]],
        'fields' => 'ID'
    ]);

    $friend_ids = array_unique(array_merge($incoming, $outgoing));

    if (empty($friend_ids)) return '<p>No friends with approved connections found.</p>';

    $markers = [];
    foreach ($friend_ids as $fid) {
        $lat = get_user_meta($fid, 'user_address_lat', true);
        $lng = get_user_meta($fid, 'user_address_lng', true);
        if (!empty($lat) && !empty($lng)) {
            $markers[] = [
                'lat' => $lat,
                'lng' => $lng,
                'name' => get_userdata($fid)->display_name,
                'avatar' => get_avatar_url($fid),
                'profile' => get_author_posts_url($fid),
            ];
        }
    }

    if (empty($markers)) return '<p>Friends have no location data.</p>';

    ob_start(); ?>
    <div id="friends-map" style="height: 400px; width: 100%; margin-bottom: 1rem;"></div>
    <script>
        const markers = <?php echo json_encode($markers); ?>;

        function initFriendsMap() {
            const map = new google.maps.Map(document.getElementById('friends-map'), {
                zoom: 3,
                center: { lat: 39.8283, lng: -98.5795 },
                styles: [],
                zoomControl: true,
                streetViewControl: false,
                mapTypeControl: false,
                fullscreenControl: true,
            });
            window.map = map;

            let isDark = localStorage.getItem("um_map_theme") !== "light";
            const darkThemeStyles = [
                { elementType: "geometry", stylers: [{ color: "#212121" }] },
                { elementType: "labels.text.fill", stylers: [{ color: "#757575" }] },
                { elementType: "labels.text.stroke", stylers: [{ color: "#212121" }] },
                { featureType: "water", elementType: "geometry", stylers: [{ color: "#000000" }] },
                { featureType: "water", elementType: "labels.text.fill", stylers: [{ color: "#3d3d3d" }] }
            ];
            if (isDark) {
                map.setOptions({ styles: darkThemeStyles });
            }

            const friendMarkers = [];
            markers.forEach(friend => {
                const marker = new google.maps.Marker({
                    position: { lat: parseFloat(friend.lat), lng: parseFloat(friend.lng) },
                    map: map,
                    icon: friend.avatar ? {
                        url: friend.avatar,
                        scaledSize: new google.maps.Size(40, 40),
                        origin: new google.maps.Point(0, 0),
                        anchor: new google.maps.Point(20, 20)
                    } : null
                });

                const infowindow = new google.maps.InfoWindow({
                    content: `<div style='max-width:200px; padding:10px; background:#2c2c2c; color:white; border-radius:10px;'>
                        <img src='${friend.avatar}' style='width:40px;height:40px;border-radius:50%;'><br/>
                        <strong>${friend.name}</strong><br/>
                        <a href='${friend.profile}' style='color:#3ba1da;'>View Profile</a>
                    </div>`
                });

                marker.addListener('click', () => infowindow.open(map, marker));
                friendMarkers.push(marker);
            });

            new MarkerClusterer(map, friendMarkers, {
                imagePath: "https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m"
            });

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(pos => {
                    const userLocation = {
                        lat: pos.coords.latitude,
                        lng: pos.coords.longitude
                    };
                    new google.maps.Marker({
                        position: userLocation,
                        map: map,
                        title: "You are here",
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 8,
                            fillColor: "#4285F4",
                            fillOpacity: 1,
                            strokeWeight: 2,
                            strokeColor: "#ffffff"
                        }
                    });
                    map.setCenter(userLocation);
                });
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            const script = document.createElement('script');
            script.src = "https://maps.googleapis.com/maps/api/js?key=AIzaSyBHq3VO05frnb9NyETMNCKcpScYC5a8tEM&callback=initFriendsMap";
            script.async = true;
            script.defer = true;
            document.body.appendChild(script);

            const clusterScript = document.createElement("script");
            clusterScript.src = "https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js";
            clusterScript.defer = true;
            document.body.appendChild(clusterScript);
        });
    </script>
    <?php
    return ob_get_clean();
}
