<?php
/**
 * LOVEFiLM WordPress Widget
 * http://lovefilm.com/widget
 * 
 * Part of the LOVEFiLM WordPress Widget Plug-in.
 * Contains web service constants.
 */

/**
 * PHP Version constant, made available for
 * version less than 5.2.7 
 */
if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}
/**
 * PHP Version constants, made available for
 * version less than 5.2.7
 */
if (PHP_VERSION_ID < 50207) {
    define('PHP_MAJOR_VERSION',   $version[0]);
    define('PHP_MINOR_VERSION',   $version[1]);
    define('PHP_RELEASE_VERSION', $version[2]);
}
/**
 * Minimum version of PHP supported by the Plug-in.
 */
if(!defined('LOVEFILM_PLUGIN_MIN_PHP_MAJOR_VERSION'))
	define('LOVEFILM_PLUGIN_MIN_PHP_MAJOR_VERSION', 5);
if(!defined('LOVEFILM_PLUGIN_MIN_PHP_MINOR_VERSION'))
	define('LOVEFILM_PLUGIN_MIN_PHP_MINOR_VERSION', 1);
if(!defined('LOVEFILM_PLUGIN_MIN_PHP_RELEASE_VERSION'))
	define('LOVEFILM_PLUGIN_MIN_PHP_RELEASE_VERSION', 0);
if(!defined('LOVEFILM_PLUGIN_MIN_PHP_VERSION_ID'))
	define('LOVEFILM_PLUGIN_MIN_PHP_VERSION_ID', LOVEFILM_PLUGIN_MIN_PHP_MAJOR_VERSION * 10000 + LOVEFILM_PLUGIN_MIN_PHP_MINOR_VERSION * 100 + LOVEFILM_PLUGIN_MIN_PHP_RELEASE_VERSION);
/**
 * Webservice name
 */
if(defined('WP_LOVEFILM_DEBUG') && WP_LOVEFILM_DEBUG == true)
{
    $urlst = "http://webservice.lovefilm.staging.stickyeyes.com/";
} else {
    $servername = $_SERVER['SERVER_NAME'];
    switch($servername)
    {
        case 'wp.local':
            $urlst = "http://lovefilm-ws.dev/";
            break;
        case 'lovefilm.staging.stickyeyes.com':
            $urlst = "http://webservice.lovefilm.staging.stickyeyes.com/";
            break;
        case 'lovefilm-int.staging.stickyeyes.com':
            $urlst = "http://webservice.lovefilm-int.staging.stickyeyes.com/";
            break;
        default:
            $urlst = "http://widget.lovefilm.com/";
            break;
    }
}

if (!defined('LOVEFILM_WS_URL'))
	define('LOVEFILM_WS_URL', $urlst);

if (!defined('LOVEFILM_WS_API_URL'))
	define('LOVEFILM_WS_API_URL', LOVEFILM_WS_URL."api");
	
if(!defined('LOVEFILM_HTTP_TIMEOUT'))
	define('LOVEFILM_HTTP_TIMEOUT', 5);
/**
 * Widget modes
 */
if (!defined('LOVEFILM_WIDGET_MODE_AFFILIATE'))
	define('LOVEFILM_WIDGET_MODE_AFFILIATE', 'affiliate');
	
if (!defined('LOVEFILM_WIDGET_MODE_VANITY'))
	define('LOVEFILM_WIDGET_MODE_VANITY',    'vanity');
	
if (!defined('LOVEFILM_WIDGET_MODE_CONTEXT'))
	define('LOVEFILM_WIDGET_MODE_CONTEXT',   'contextual');
/**
 * Widget Themes
 */
if (!defined('LOVEFILM_THEME_LIGHT'))
	define('LOVEFILM_THEME_LIGHT', 'light');

if (!defined('LOVEFILM_THEME_DARK'))
	define('LOVEFILM_THEME_DARK',  'dark');
/**
 * Widget width types
 */
if (!defined('LOVEFILM_WIDTH_TYPE_FLUID'))
	define('LOVEFILM_WIDTH_TYPE_FLUID', 'fluid');

if (!defined('LOVEFILM_WIDTH_TYPE_FIXED'))
	define('LOVEFILM_WIDTH_TYPE_FIXED', 'fixed');
/**
 * Widget Min and Max values
 */
if (!defined('LOVEFILM_WIDTH_MAX'))
	define('LOVEFILM_WIDTH_MAX', 350);

if (!defined('LOVEFILM_WIDTH_MIN'))
	define('LOVEFILM_WIDTH_MIN', 200);
/**
 * Widget Contexts
 */
if (!defined('LOVEFILM_CONTEXT_GAME'))
	define('LOVEFILM_CONTEXT_GAME', 'games');

if (!defined('LOVEFILM_CONTEXT_FILM'))
	define('LOVEFILM_CONTEXT_FILM', 'films');
/**
 * Default settings.
 */
if (!defined('LOVEFILM_DEFAULT_CONTEXT'))
	define('LOVEFILM_DEFAULT_CONTEXT', LOVEFILM_CONTEXT_FILM);

if (!defined('LOVEFILM_DEFAULT_MESSAGE'))
	define('LOVEFILM_DEFAULT_MESSAGE', NULL);

if (!defined('LOVEFILM_DEFAULT_MODE'))
	define('LOVEFILM_DEFAULT_MODE', LOVEFILM_WIDGET_MODE_AFFILIATE);

if (!defined('LOVEFILM_DEFAULT_THEME'))
	define('LOVEFILM_DEFAULT_THEME', LOVEFILM_THEME_LIGHT);

if (!defined('LOVEFILM_DEFAULT_WIDTH_TYPE'))
	define('LOVEFILM_DEFAULT_WIDTH_TYPE', LOVEFILM_WIDTH_TYPE_FLUID);

if (!defined('LOVEFILM_DEFAULT_WIDTH'))
	define('LOVEFILM_DEFAULT_WIDTH', LOVEFILM_WIDTH_MIN);

if (!defined('LOVEFILM_DEFAULT_AFF'))
	define('LOVEFILM_DEFAULT_AFF', NULL);

if (!defined('LOVEFILM_DEFAULT_PLUGIN_DIR'))
	define('LOVEFILM_DEFAULT_PLUGIN_DIR', 'lovefilm-widget');