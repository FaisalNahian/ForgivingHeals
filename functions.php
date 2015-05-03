<?php
define("ET_UPDATE_PATH",    "http://www.maxweb.us/");
define("ET_VERSION", '1.0');

if(!defined('ET_URL'))
 define('ET_URL', 'http://www.maxweb.us/');

if(!defined('ET_CONTENT_DIR'))
 define('ET_CONTENT_DIR', WP_CONTENT_DIR.'/et-content/');

define( 'TEMPLATEURL', get_template_directory_uri() );
define( 'THEME_NAME' , 'ForgivingHeals');
define( 'ET_DOMAIN'  , 'maxweb');

if(!defined('THEME_CONTENT_DIR ')) 	define('THEME_CONTENT_DIR', WP_CONTENT_DIR . '/et-content' . '/ForgivingHeals' );
if(!defined('THEME_CONTENT_URL'))	define('THEME_CONTENT_URL', content_url() . '/et-content' . '/ForgivingHeals' );

// theme language path
if(!defined('THEME_LANGUAGE_PATH') ) define('THEME_LANGUAGE_PATH', THEME_CONTENT_DIR.'/lang/');

if(!defined('ET_LANGUAGE_PATH') )
 define('ET_LANGUAGE_PATH', THEME_CONTENT_DIR . '/lang');

if(!defined('ET_CSS_PATH') )
 define('ET_CSS_PATH', THEME_CONTENT_DIR . '/css');

require_once TEMPLATEPATH.'/includes/index.php';
require_once TEMPLATEPATH.'/mobile/functions.php';

try {
	if ( is_admin() ){
		new ForgivingHeals_Admin();
	} else {
		new ForgivingHeals_Front();
	}
} catch (Exception $e) {
	echo $e->getMessage();
}
?>