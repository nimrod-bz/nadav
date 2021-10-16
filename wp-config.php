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
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'root' );

/** MySQL hostname */
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
define( 'AUTH_KEY',         '|d%AHvC)f`p^JHW6G>Da9be9>Z@hQ#M&CQKe(p&zOA}R[!FM3.d*G.J1(j^8? pg' );
define( 'SECURE_AUTH_KEY',  'SyBE:b/{CY})XNpN5s/h,M>PKV2/ups!|?0U%*d9{Q78Amv*)=q fe7JUVRr|SsB' );
define( 'LOGGED_IN_KEY',    '5hZP&(+D=w~lE9GV,lB*bZ|#8iG}Fmk|10et6<@s@;{Lv&7|~8;fEwM1^d/ Fpm=' );
define( 'NONCE_KEY',        '@LJ}?_i73h9)>:U7n;tTAg4pSLKYQ=XC2B8/m70~E Zg-N_VALFJ]^D4k-B%q#!A' );
define( 'AUTH_SALT',        ',/Cj[WXDA]v`M<6SkxMs*-og`XEtc:=&xt!BJQ/r$N8HzQXa4/BbSD<Ia:&#N0$A' );
define( 'SECURE_AUTH_SALT', 'A22an;.8]5[,BE;rZB#g^aX0Ljv<^k(_1@3Jt$SeZIW$u-/ZOX2@ 5OCK=e8(oc)' );
define( 'LOGGED_IN_SALT',   'qAk~Ss=NbSGcZz u0ZNB>LC&5DStPVT!x{ZLy`azSPOt;?Z{<#I||5^9$>odrS=|' );
define( 'NONCE_SALT',       'zM{<B&bVt/ikeKq.WLX-<F}?PdX7-,C`kz~Z}UExKwro:[Gugwj.-zz?a8 Q{,3!' );

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
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
