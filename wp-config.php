<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'mhisaorg_WPU0W');

/** Database username */
define('DB_USER', 'mhisaorg_WPU0W');

/** Database password */
define('DB_PASSWORD', 'r=dp^AYAzl9Qz&Nz#');

/** Database hostname */
define('DB_HOST', 'localhost');

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', '4442b174ac4c157b1ddabc11a4f2eecf7bccf1167d2ed9fa6afb1fafb2630bd9');
define('SECURE_AUTH_KEY', '4927dba6dd28abb826ee467e9df58cb36646b5119b60da29ddfc1ad2f76de91b');
define('LOGGED_IN_KEY', '5799eb8f51b6b964329a4048e716f651c2e4573c210298abceceb398889e6593');
define('NONCE_KEY', 'fd20d58b561a2c360d790417cbc463244f3d4fe0c272300f3dd425142b25eaeb');
define('AUTH_SALT', 'dcf068c92bc72bf919461844f9fbd8154ad243a9f29abeaca822f0c54dba24e9');
define('SECURE_AUTH_SALT', '8829f1741afcb7ff62199f2f29a04beb6e0e37212e4ddc084437015053ef9fa3');
define('LOGGED_IN_SALT', '12a068d8943318fcf8c63c2a168e7a11aba6be5738a0a940cda08fa8ed39064e');
define('NONCE_SALT', 'af4a55d9852909612f00d09996d8a43d8ef00be6ce07eb653cee17eccd9540af');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'O63_';
define('WP_CRON_LOCK_TIMEOUT', 120);
define('AUTOSAVE_INTERVAL', 300);
define('WP_POST_REVISIONS', 20);
define('EMPTY_TRASH_DAYS', 7);
define('WP_AUTO_UPDATE_CORE', true);

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
