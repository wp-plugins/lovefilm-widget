<?php
/**
 * LOVEFiLM WordPress Widget
 * http://lovefilm.com/widget
 * 
 * Part of the LOVEFiLM WordPress Widget Plug-in.
 * Contains all the action handlers registered by
 * the plug-in.
 */
require_once( 'lovefilm_ws_constants.php' );
require_once( 'lovefilm_admin.php' );
require_once( 'lovefilm_ws.php' );
require_once( 'lovefilm_widget.php' );
if ( version_compare($wp_version,"2.9",">=") ) {
require_once( 'lovefilm_contextual_widget.php');
}

if ( !defined( 'WP_PLUGIN_URL' ))
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );

define('LOVEFILM_WS_RESOURCES_URL', LOVEFILM_WS_URL.'widget');

/**
 * Informs the LOVEFiLM Web Service of a new plug-in
 * activation, registers the UID of the plug-in and
 * posts useage data to the Web Service.
 * Called via register_activation_hook.
 */
function lovefilm_activate() {

	// Check that we are running on supported version of PHP
	if(PHP_VERSION_ID < LOVEFILM_PLUGIN_MIN_PHP_VERSION_ID){
		$minVersion = implode('.', array(LOVEFILM_PLUGIN_MIN_PHP_MAJOR_VERSION,LOVEFILM_PLUGIN_MIN_PHP_MINOR_VERSION,LOVEFILM_PLUGIN_MIN_PHP_RELEASE_VERSION));
		$message = "<h1>The LOVEFiLM Widget currently requires at least PHP".$minVersion."</h1><p>Your current version of PHP is ".phpversion().". Please talk to your hosting provider or server administrator about upgrading your current version of PHP to at least version ".$minVersion." or higher.</p>"; 
		if(function_exists('deactivate_plugins') )
			deactivate_plugins(dirname(__FILE__).DIRECTORY_SEPARATOR."lovefilm.php");
		else
			$message .= '<p><strong>Please deactivate this plugin Immediately</strong></p>'; //We couldnt automatically deactivate without messing with array, for Wordpress < 2.4
		wp_die($message);
	}
	
	// Check that we can make HTTP requests
	$response = wp_remote_get(LOVEFILM_WS_API_URL, "GET");
	if(is_wp_error($response)) {
		$message = "<h1>The LOVEFiLM Widget cannot connect to the LOVEFiLM Webservice.</h1><p>The LOVEFiLM Widget is having problems activating itself with the LOVEFiLM Webservice. Please try activating the plug-in again. If this problem persists, please check your WordPress, PHP and Web Server configuration to ensure that WordPress plug-ins have access to the web.</p>";
		$message .= "<p><pre>".implode("<br>\n", $response->get_error_messages())."</pre></p>";
		if(function_exists('deactivate_plugins') )
			deactivate_plugins(dirname(__FILE__).DIRECTORY_SEPARATOR."lovefilm.php");
		wp_die($message);
	}
	
    lovefilm_install_tables();
    lovefilm_init_options();
    delete_option('lovefilm');
    error_log("Love film Activated");
    
    $success = lovefilm_ws_service_end_points();
    if($success === FALSE) {
		$message = "<h1>The LOVEFiLM Widget was unable to retrieve service details from the LOVEFiLM Webservice.</h1><p>The LOVEFiLM Widget is having problems updating itself with the latest details of the LOVEFiLM Webservice. Please try activating the plug-in again. If this problem persists, please check your WordPress, PHP and Web Server configuration to ensure that WordPress plug-ins have access to the web.</p>";
		if(function_exists('deactivate_plugins') )
			deactivate_plugins(dirname(__FILE__).DIRECTORY_SEPARATOR."lovefilm.php");
		wp_die($message);
    }
    
    //Activates the Scheduler for lovefilm cron job
    if(defined('WP_LOVEFILM_DEBUG') && WP_LOVEFILM_DEBUG == true)
    	wp_schedule_event(mktime(), 'cron_action_time', 'lovefilm_cron');
    else
    	wp_schedule_event(mktime(), 'daily', 'lovefilm_cron');
    	
    try {
    	if(!get_option('lovefilm_domain'))
	        add_option('lovefilm_domain', '');
		
    	$uid = lovefilm_ws_register_uid(); // Generate UID and store UID in WordPress database
	$usageData = lovefilm_admin_collect_useage_data(); // Collect usage data about blog
	lovefilm_ws_usage_data($uid, $usageData); // Send usage data to web service using UID
                
		//This functions get the marketing message, fetch the titles and get the promocode during the activation of the widget.
        _log("---starting to pre-populate cache");
        lovefilm_ws_get_marketing_msg();
	lovefilm_admin_clearDbCache();
        lovefilm_ws_get_promo_code();
        _log("---done pre-populate cache");
    } catch(Exception $e) {
    	_log($e);
    }
}

function lovefilm_install_tables()
{
    global $wpdb;
    $sqldir = dirname(__FILE__) . '/sql/tables.sql';
    $installSql = file_get_contents($sqldir);
    $queries = explode('|', $installSql);
    foreach($queries as $q)
    {
        $wpdb->query($q);
    }
}

function lovefilm_uninstall_tables()
{
    global $wpdb;
    $sqldir = dirname(__FILE__) . '/sql/uninstall.sql';
    $installSql = file_get_contents($sqldir);
    $queries = explode('|', $installSql);
    foreach($queries as $q)
    {
        $wpdb->query($q);
    }
}

/**
 * Sets the Widget options to their default values.
 */
function lovefilm_init_options()
{
	update_option('lovefilm_share_love', NULL);
        update_option('lovefilm_aff_widget', NULL);
        update_option('lovefilm_earn_type', 'none');
        update_option('lovefilm_contextual_display_article_link', 0);
        update_option('lovefilm_context', LOVEFILM_DEFAULT_CONTEXT);
	update_option('lovefilm-marketing-message', NULL);
	update_option('lovefilm-promo-code', NULL);
	update_option('lovefilm-settings', array(
										"context"             => LOVEFILM_DEFAULT_CONTEXT, 
										"type"                => LOVEFILM_DEFAULT_MODE, 
										"theme"               => LOVEFILM_DEFAULT_THEME, 
										"lovefilm_width_type" => LOVEFILM_DEFAULT_WIDTH_TYPE, 
										"lovefilm_aff"        => LOVEFILM_DEFAULT_AFF)
									   );
}

/**
 * Clears any LOVEFiLM references added to the WP_Options table.
 * Used when deactivating the widget.
 */
function lovefilm_clear_wp_options()
{
	unregister_setting('lovefilm-settings',          'lovefilm-settings',     'lovefilm_clear_setting');
	unregister_setting('lovefilm_main',              'Main Settings',         'lovefilm_clear_setting');
	unregister_setting('lovefilm_width',             'Width',                 'lovefilm_clear_setting');
	unregister_setting('lovefilm_width_type',        'Widget Layout Type',    'lovefilm_clear_setting');
	unregister_setting('lovefilm_theme',             'Widget Theme',          'lovefilm_clear_setting');
	unregister_setting('lovefilm_context',           'Widget Context',        'lovefilm_clear_setting');
	unregister_setting('lovefilm_aff',               'Widget Affiliate Code', 'lovefilm_clear_setting');
}
/**
 * Deletes any LOVEFiLM options set during the lifetime of
 * the widget. Used when deactivating the widget.
 */
function lovefilm_delete_options()
{
    delete_option('lovefilm-settings');
    delete_option('lovefilm_domain');
    delete_option('lovefilm-ws-endpoints');
    delete_option('lovefilm-uid');
    delete_option('lovefilm-widget-mode');
    delete_option('lovefilm-affiliate-id');
    delete_option('lovefilm-widget-content');
    delete_option('lovefilm-widget-theme');
    delete_option('widget_lovefilm_widget');
    delete_option('lovefilm-marketing-message');
    delete_option('lovefilm-promo-code');
    delete_option('lovefilm_context');
    delete_option('lovefilm_contextual_display_article_link');
    delete_option('lovefilm_share_love');
    delete_option('lovefilm_aff_widget');
    delete_option('lovefilm_earn_type');
    
}
/**
 * Informs the LOVEFiLM Web Service that the plug-in for this
 * UID has been deactivated.
 * Called via register_deactivation_hook and the register_uninstall_hook.
 */
function lovefilm_uninstall() {
    wp_clear_scheduled_hook('lovefilm_cron');
	try {
		$uid = get_option( 'lovefilm-uid' );
		lovefilm_ws_unregister_uid($uid);
	}
	catch(Exception $e) {
		/* no-op */
	}
	// Clear caches
	lovefilm_uninstall_tables();
	lovefilm_clear_wp_options();
	lovefilm_delete_options();
	unregister_widget('Lovefilm_Widget');
        _log('Lovefilm widget deactivated');
}

/**
 * Generates the admin option under 'Settings' for
 * the LOVEFiLM Widget Configuration Panel.
 * Called via the action hook 'admin_menu'. 
 */
function lovefilm_admin_menu() {
    add_menu_page(
        LOVEFILM_OPTIONS_PAGE_TITLE,
        LOVEFILM_OPTIONS_MENU_GLOBAL_TITLE,
        'administrator',
        LOVEFILM_OPTIONS_MENU_GLOBAL_SLUG,
		'lovefilm_admin_show_options_panel'
    );
}
/**
 * Registers the various options available to the
 * WordPress administrator from the LOVEFiLM Widget
 * Configuration Panel.
 * Called via the action hook 'admin_init'.
 */
function lovefilm_admin_register_settings() {
    global $wp_version;
    register_setting('lovefilm-settings', 'lovefilm-settings', 'lovefilm_validate_settings');
    add_settings_section('lovefilm_apperance', 'Appearance', 'lovefilm_section_apperance', 'lovefilm-settings-main');
    add_settings_section('lovefilm_earn', 'Earn Money (optional)', 'lovefilm_section_earn', 'lovefilm-settings-earn-type');
    //Aperance section
    add_settings_field('lovefilm_context', 'Content', 'lovefilm_input_widget_context', 'lovefilm-settings-main', 'lovefilm_apperance');
    add_settings_field('lovefilm_theme', 'Colour Theme', 'lovefilm_input_widget_theme', 'lovefilm-settings-main', 'lovefilm_apperance');
    add_settings_field('lovefilm_width', 'Width', 'lovefilm_input_width', 'lovefilm-settings-main', 'lovefilm_apperance');
    //add_settings_field(  'lovefilm_width_type',        'Widget Layout Type',    'lovefilm_width_type_input',     'lovefilm-settings-main', 'lovefilm_main');
    if ( version_compare($wp_version,"2.9",">=") ) {
    add_settings_field('lovefilm_cw_display_link', 'Display article links?', 'lovefilm_input_contextual_links', 'lovefilm-settings-main', 'lovefilm_apperance');
    }
    // Earn section 
    add_settings_field('lovefilm_none', 'No Thanks', 'lovefilm_input_widget_none', 'lovefilm-settings-earn-type', 'lovefilm_earn');
    add_settings_field('lovefilm_aff', 'Affiliate Window', 'lovefilm_input_widget_aff', 'lovefilm-settings-earn-type', 'lovefilm_earn');
    add_settings_field('lovefilm_share_love', 'Share the LOVE', 'lovefilm_input_widget_share_love', 'lovefilm-settings-earn-type', 'lovefilm_earn');
        
}

/**
 * Registers widgets.
 * Called via the action hook 'widgets_init'.
 */
function lovefilm_widgets_init() {
	register_widget( 'Lovefilm_Widget' );
}
/**
 * Adds a header handler for the public site to 
 * add link elements and JS includes.
 *  
*/
function lovefilm_widget_header() {
    echo '<link rel="stylesheet" type="text/css" href="' . LOVEFILM_WS_RESOURCES_URL . '/css/widgets-1.1.css" />'."\r\n";
	echo '<script src="' . LOVEFILM_WS_RESOURCES_URL . '/js/jquery-1.4.4.min.js" type="text/javascript"></script>'."\r\n";
	echo '<script src="' . LOVEFILM_WS_RESOURCES_URL . '/js/jquery-ui-1.8.7.custom.min.js" type="text/javascript"></script>'."\r\n";
       // echo '<script src="' . LOVEFILM_WS_RESOURCES_URL . '/js/jquery.hoverbox.min" type="text/javascript"></script>'."\r\n";
    
        echo '	<!--[if lt IE 7]>
	<script src="' . LOVEFILM_WS_RESOURCES_URL . '/js/belated-0.0.8a.min.js" type="text/javascript"></script>
	<script>
	LFWidget$(document).ready(function() {
        DD_belatedPNG.fix("#lf-widget a .wrap");
        DD_belatedPNG.fix("#lf-widget a .wrap .mask");
        DD_belatedPNG.fix("#lf-widget .ratings");
        DD_belatedPNG.fix("#lf-widget .rental");
        DD_belatedPNG.fix("#lf-widget .featured-title-review");
    });
	</script>
	<![endif]-->'."\r\n";

	echo '<script src="' . LOVEFILM_WS_RESOURCES_URL . '/js/widgets.js" type="text/javascript"></script>'."\r\n";
	echo '<script src="' . LOVEFILM_WS_RESOURCES_URL . '/js/contextual.js" type="text/javascript"></script>'."\r\n";
	echo '<script src="' . LOVEFILM_WS_RESOURCES_URL . '/js/json2.js" type="text/javascript"></script>'."\r\n";
	echo '<!--[if lt IE 9]><script src="' . LOVEFILM_WS_RESOURCES_URL . '/js/html5.js"></script><![endif]-->'."\r\n";
}	
/**
 * Used to unregister a setting.
 */
function lovefilm_clear_setting()
{
	echo NULL;
}

add_action('admin_head', 'lovefilm_tool_tip_css'); 

/**
 * All the Ajax and the jquery functionality goes here
 */
function lovefilm_tool_tip_css() {
    
?>
<style type="text/css">
.tooltip {text-decoration:none; padding:3px;border:1px solid #2683AE; margin-right: 5px; font-weight: bold; color:#2683AE;}
.tooltip:hover {background:#ffffff; text-decoration:none;} /*BG color is a must for IE6*/
.tooltip span {display:none; padding:2px 3px; margin-left:8px; width:400px;}
.tooltip:hover span{display:inline; position:absolute; border:1px solid #cccccc; background:#ffffff; color:#6c6c6c;line-height: 1.2; font-size: 10pt; font-weight: normal;}
</style>
<?php } ?>