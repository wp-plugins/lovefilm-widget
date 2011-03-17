<?php
/**
 * Plugin Name: LOVEFiLM Widget
 * Plugin URI: http://www.lovefilm.com/partnership/widgets
 * Description: This plugin allows you to add the official LOVEFiLM widget to the sidebar of your Wordpress blog. Monetise your blog by promoting the latest and most popular movies or games with LOVEFiLM's affiliate program.
 * Version: 1.0.11
 * Author: LOVEFiLM-widgets
 * Author URI: http://profiles.wordpress.org/users/LOVEFiLM-widgets/
 * License: GPL2
 * 
 * Copyright 2010  LOVEFiLM  (email : widget@lovefilm.com)
 * 
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License, version 2, as 
 *  published by the Free Software Foundation.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * The following constant is set by the build process
 */
if(!defined('LOVEFILM_WIDGET_VERSION')) {
	define('LOVEFILM _WIDGET_VERSION', "1.0.11");
}
/*
 * Checkes the version of the Wordpress. if the version is less then 3.0 then it shows the the error message.
 */
global $wp_version;

if ( !version_compare($wp_version,"2.8",">=") ) {
    
	$errormsg = '<p>You need at least version 3.0 of WordPress to use this plugin.<br>' ;
    $errormsg .= '<a href = '. get_option(siteurl).'/wp-admin/update-core.php target="_parent">Click here to update latest version.</a></p>';
    die($errormsg);
}

require_once( 'lovefilm_ws_constants.php' );
require_once( 'lovefilm_action_handlers.php' );

/**
 * Widget activation and deactivation hook registration.
 */
register_activation_hook( __FILE__, 'lovefilm_activate' );
register_deactivation_hook( __FILE__, 'lovefilm_uninstall' );

if(function_exists('register_uninstall_hook')) {
    register_uninstall_hook(__FILE__, 'lovefilm_uninstall');
}

/**
 * Admin system hook registration.
 */
if ( is_admin() ) {
	add_action( 'admin_menu', 'lovefilm_admin_menu' );
	add_action( 'admin_init', 'lovefilm_admin_register_settings' );
} else {
	/** We are the widget **/
	add_action("wp_head", "lovefilm_widget_header");
}

/**
 * Register new XML-RPC functions to WordPress.
 */
add_filter( 'xmlrpc_methods', 'lovefilm_xmlrpc_methods' );

/**
 * Widget hook registration.
 */
add_action( 'widgets_init', 'lovefilm_widgets_init' );
/**
 * Debug log method
 */
if(!function_exists('_log')){
  function _log( $message ) {
    if( WP_DEBUG === true ){
      $prefix = "LOVEFiLM Plugin: ";
      if( is_array( $message ) || is_object( $message ) ){
      	if(is_object( $message ) && is_a($message, 'Exception'))
      		$message = get_class($message).": ".$message->getMessage()."\n".$message->getTraceAsString(); 
        error_log( print_r( $prefix.$message, true ) );
      } else {
        error_log( $prefix.$message );
      }
    }
  }
}
/**
 * Ensures the InvalidArgumentException class exists.
 */
if(!class_exists('InvalidArgumentException')){
	class LogicException extends Exception {};
	class InvalidArgumentException extends LogicException {};
}

/*
 * The cron job that runs according to specified time
 * the interval is 86400 = 60S*60M*24H
 */

if(!function_exists('lovefilm_cronjob')) {
	function lovefilm_cronjob( $schedules ) {
	
	    $interval = get_option('lf_cron_increment', 86400);
	    // add a schedule to the existing set
	    error_log('lovefilm cron interval is registered');
		$schedules['lf_cron_day'] = array(
			'interval' => $interval,
			'display' => __('one minute')
		);
	    
		return $schedules;
	}

	// Adds filter for the cron sheduler for
	add_filter( 'cron_schedules', 'lovefilm_cronjob' );
}
