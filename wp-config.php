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

if(!getenv( 'APPLICATION_ENV' )) {
	putenv( 'APPLICATION_ENV='.$_SERVER['SERVER_NAME'] );
}

if(!defined('APPLICATION_ENV')) {
	define('APPLICATION_ENV', getenv('APPLICATION_ENV'));
}

if ( file_exists( dirname( __FILE__ ) . '/local-config.php' ) ) {
	include( dirname( __FILE__ ) . '/local-config.php' );  // Your own settings, allows it all to be outside of SVN
}

$siteHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'marinenotice.net';

if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
	$protocol = 'https';
} else {
	$protocol = 'http';
}

$domain = $protocol . '://' . $siteHost;

/** MySQL settings - as set above */
define( 'DB_NAME',     $database );
define( 'DB_USER',     $user );
define( 'DB_PASSWORD', $password );
define( 'DB_HOST',     $host );

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'k 4RfQ)]?{1I4SKA)k~T|][+I}Jxm45Rua3jlPj|GN`,<}MpFl _8gD~tBagIDG:');
define('SECURE_AUTH_KEY',  'hM.Wq(6mq-<aPxg;Oja=5)vi_R_8ukS9&P}_|dI;4:C|O+{4]$FpE:7!HB0!pp+s');
define('LOGGED_IN_KEY',    '|Gk4c +gE--)<{+9(|qvK3VV+JaHOuRKA{R.<R=,{weQ# p+S8h=7;2J(8#Fj[K,');
define('NONCE_KEY',        'f<WM0>#Tp_|or|sA2FCL]ul[ETMbw|d%Fvwp~R]y59i=Cu?`MIxk<~cjA=|p:M`|');
define('AUTH_SALT',        '$60o<7BPa*l!?WHgpmmXFa{HZ_VXaZd9|x-WR~yttHl+ar%fd0tqR4A>Zt/|5fB-');
define('SECURE_AUTH_SALT', 'X Pt[0]l4rG[^ByKv@w,Y_(Qg~R0G/K5>kQP!RE4f&eUY=1sp.^*v2i)col$ |B!');
define('LOGGED_IN_SALT',   '73fO{1M,0Vm!kF?Zhkpa>RL8%+c7kKiNkDXd>4p=hSXW.WD  H]hS7~-BGyhAST/');
define('NONCE_SALT',       'i2b(U{z4M[j<<-w Q H}Rh=nfze?7I1PpXP32vT6{>k {[<`guEaZ@LY#Er`t9qK');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
define('WP_DEBUG', false);

// Hardcoded WordPress URLs, overrides database see http://codex.wordpress.org/Editing_wp-config.php#WordPress_address_.28URL.29
$domain = rtrim( $domain, '/');                     // *Never* have the trailing slash
define('WP_SITEURL',                $domain . '/wp');       // No overriding within Admin...
define('WP_HOME',                   $domain);       // ...of either setting

define( 'PATH_CURRENT_SITE', '/' );
define('WP_CONTENT_DIR',            dirname( __FILE__ ) . '/custom');   // Move all content (themes, plugins, uploads etc) to reside in a custom directory...
define('WP_CONTENT_URL',            $domain . '/custom');   // ...with matching custom content URL

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
