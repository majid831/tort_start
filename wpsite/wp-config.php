<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wpsite' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'EDu{OI;5V:)q@-_{my2u`M@ [HbW$row-E2rgT g@]{@hkR<^Cw;w.>Q~.C8^*vc' );
define( 'SECURE_AUTH_KEY',  '2G9F_U]_(z?<4B<}ehw]+0~UE}Tp65XZdIuG^KzDC.gf&2}U<ZbVO#*>>S]OR6)%' );
define( 'LOGGED_IN_KEY',    '|q`6co-#|1ch`{50ISzOEflyyF0;Kkg,: DO-q}8;:oc(^qTK-uqa6j8#44J+ Hg' );
define( 'NONCE_KEY',        'g]z0LAR5DkHqcd%R:ex$]p3;k%~X6jT<NBFYLAnz}@}#CIPa`MUBjQgHSg=E44MH' );
define( 'AUTH_SALT',        'j:~%S@Tpk*QraZ)APnO2c|s[}~{|asBdbT^Ox|%xDT-UgKS~i7/+tkB3fi%W]I~D' );
define( 'SECURE_AUTH_SALT', 'B0Y-fRW|*sasV#4:^e1<Np7/>K-,GjE`Um~Lc.sfpRz+ZN$$_AmMCmt|zuwTKaMp' );
define( 'LOGGED_IN_SALT',   'Q`S93WP.Ij=Nm(sTv8:A-]H|{ER#9EwTbTD6Y,a[4T4F}$>##W4XfRrXX@UR>0TQ' );
define( 'NONCE_SALT',       'IC1JmvwBt QFoy+Wzj?zG})4/zH2UncJ&I%3M@c+T4A`DdY~;+95 C7wTQgZiF{=' );

/**#@-*/

/**
 * WordPress Database Table prefix.
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
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
