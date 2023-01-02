<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'durgalaxmi' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         'La(y2/+4JuGvzwkx^%/h&{S$4UcHHe:>+.?xX~bB:_z@}jD?LshRW%,+,E/G7;^1' );
define( 'SECURE_AUTH_KEY',  'GlIYq$jHixpqT-Y]rhLucMcUku#,SY#x2i-U-7+Ybp(s=K@,22U,<=2WMa&l;?kZ' );
define( 'LOGGED_IN_KEY',    'XFJ/T=F24!4v~){/Ni,)Hb(zSYQDs=XvK4S}?2trt/kozMY4G#(GlB,Sp37)|e%e' );
define( 'NONCE_KEY',        'jH$.-/WL&B#4^-y>`wjt`}<;tq|1I:*H1W}[6Ai2Pb*.N3?.g5xnv?Vf]Jm*jkr9' );
define( 'AUTH_SALT',        'J.jzw B_1?%7DhD7ZB[M}yOH>m]p;>u3.Yo/j^+m$ml0zR+ZnbsoiACg5HnDg/d$' );
define( 'SECURE_AUTH_SALT', '[=uR6`Q(.i`$,9f!W_?d8/DaQFCP+3kri )A+F@^I0m0Zq1W|d8}n~Ik7eE&zk0X' );
define( 'LOGGED_IN_SALT',   'MBf,t9eJPR ]]+B gz8g*V0E)]<kQ{Lv!(bJdgXbMN_{=1ip<--.6W1Gm@E6Xmuv' );
define( 'NONCE_SALT',       'LYVu0)f[`+,NvH!zgQJv(.J9hBn#35Yjr!<_3{OhQwDb.x=Z>L]yP=H7$x$|#NXM' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false);

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

// define( 'SAVEQUERIES', true );
// define( 'SCRIPT_DEBUG', true );
// define( 'WP_DEBUG_DISPLAY', true );
@ini_set( 'display_errors', 0 );
