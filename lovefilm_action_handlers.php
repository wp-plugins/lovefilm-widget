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
require_once( 'lovefilm_xmlrpc.php' );
require_once( 'lovefilm_ws.php' );
require_once( 'lovefilm_widget.php' );

if ( !defined( 'WP_PLUGIN_URL' ))
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );
define('MYWP_PLUGIN_URL', WP_PLUGIN_URL . '/lovefilm');

define('LOVEFILM_WS_RESOURCES_URL', LOVEFILM_WS_URL.'widget');

/**
 * Informs the LOVEFiLM Web Service of a new plug-in
 * activation, registers the UID of the plug-in and
 * posts useage data to the Web Service.
 * Called via register_activation_hook.
 */
function lovefilm_activate() {

    lovefilm_parse_configfile();

    lovefilm_install_tables();

    lovefilm_init_options();
    
    delete_option('lovefilm');
    
    error_log("Love film Activated");
    
    //Activates the Scheduler for lovefilm cron job 
    wp_schedule_event(mktime(date('H'),0,0,date('m'),date('d'),date('Y')), 'lf_cron_day', 'lovefilm_cron');
    
    try {
    	lovefilm_ws_service_end_points();
    
    	if(!get_option('lovefilm_domain'))
    	{
	        add_option('lovefilm_domain', '');
    	}
    
		// Generate UID and store UID in WordPress database
    	$uid = lovefilm_ws_register_uid();
		// Collect usage data about blog
		$usageData = lovefilm_admin_collect_useage_data();
		// Send usage data to web service using UID
		lovefilm_ws_usage_data($uid, $usageData);
    } catch(Exception $e) {
    	_log($e);
    }
}

function lovefilm_parse_configfile()
{
    
    $path = dirname(__FILE__) . '/config';

    $h = fopen($path, 'r');

    while($line = fgets($h))
    {
        $ops = explode(':', $line);

        if(count($ops) == 2)
        {
            update_option($ops[0], $ops[1]);
        }
        else
        {
            // NOOP
        }
    }

    fclose($h);
}

function lovefilm_install_tables()
{
    global $wpdb;
    $wpdb->show_errors();
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
    $wpdb->show_errors();
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
	if(get_option('lovefilm_context')===FALSE)
		update_option('lovefilm_context', LOVEFILM_DEFAULT_CONTEXT);
		
	if(get_option('lovefilm-marketing-message')===FALSE)
		update_option('lovefilm-marketing-message', NULL);

	if(get_option('lovefilm-promo-code')===FALSE)
		update_option('lovefilm-promo-code', NULL);
		
	if(($res = get_option('lovefilm-settings'))===FALSE)
	{
		update_option('lovefilm-settings', array(
											"context"             => LOVEFILM_DEFAULT_CONTEXT, 
											"type"                => LOVEFILM_DEFAULT_MODE, 
											"theme"               => LOVEFILM_DEFAULT_THEME, 
											"lovefilm_width_type" => LOVEFILM_DEFAULT_WIDTH_TYPE, 
											"lovefilm_aff"        => LOVEFILM_DEFAULT_AFF));
		
	} else {
		$defaultSettings = array();
		
		if(!array_key_exists("context", $res))
			$defaultSettings["context"] = LOVEFILM_DEFAULT_CONTEXT;
			
		if(!array_key_exists("type", $res))
			$defaultSettings["type"] = LOVEFILM_DEFAULT_MODE;
			
		if(!array_key_exists("theme", $res))
			$defaultSettings["theme"] = LOVEFILM_DEFAULT_THEME;
			
		if(!array_key_exists("lovefilm_width_type", $res))
			$defaultSettings["lovefilm_width_type"] = LOVEFILM_DEFAULT_WIDTH_TYPE;
			
		if(!array_key_exists("lovefilm_aff", $res))
			$defaultSettings["lovefilm_aff"] = LOVEFILM_DEFAULT_AFF;
		
		update_option('lovefilm-settings', $defaultSettings);
	}
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
	register_setting(    'lovefilm-settings',          'lovefilm-settings',     'lovefilm_validate_settings');
	add_settings_section('lovefilm_main',              'Main Settings',         'lovefilm_section_main',         'lovefilm-settings-main');
	add_settings_field(  'lovefilm_width',             'Width',                 'lovefilm_input_width',          'lovefilm-settings-main', 'lovefilm_main');
	add_settings_field(  'lovefilm_width_type',        'Widget Layout Type',    'lovefilm_width_type_input',     'lovefilm-settings-main', 'lovefilm_main');
	add_settings_field(  'lovefilm_theme',             'Widget Theme',          'lovefilm_input_widget_theme',   'lovefilm-settings-main', 'lovefilm_main');
	add_settings_field(  'lovefilm_context',           'Widget Context',        'lovefilm_input_widget_context', 'lovefilm-settings-main', 'lovefilm_main');
	add_settings_field(  'lovefilm_aff',               'Widget Affiliate Code', 'lovefilm_input_widget_aff',     'lovefilm-settings-main', 'lovefilm_main');
    //add_settings_field('lovefilm_type', 'Widget Type', 'lovefilm_input_widget_type', 'lovefilm-settings-main', 'lovefilm_main');
}

/**
 * Registers new lovefilm specific methods on the
 * WordPress XML-RPC Service.
 * 
 * @param array $methods The XML-RPC methods.
 * @return array The array of XML-RPC methods.
 */
function lovefilm_xmlrpc_methods( $methods ) {
	$methods['lovefilm.setPromoCode']    = 'lovefilm_set_promo_code';
	$methods['lovefilm.clearMessage']    = 'lovefilm_cache_clear_marketing_message';
	$methods['lovefilm.clearPageCache']  = 'lovefilm_cache_clear_page';
	$methods['lovefilm.clearAllCaches']  = 'lovefilm_cache_clear_all';
	$methods['lovefilm.fetchUsageStats'] = 'lovefilm_fetch_usage_stats';
	return $methods;
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
	echo '<link rel="stylesheet" type="text/css" href="' . LOVEFILM_WS_RESOURCES_URL . '/css/widgets.css" />'."\r\n";
	echo '<script src="' . LOVEFILM_WS_RESOURCES_URL . '/js/jquery-1.4.4.min.js" type="text/javascript"></script>'."\r\n";
	echo '<script src="' . LOVEFILM_WS_RESOURCES_URL . '/js/jquery-ui-1.8.7.custom.min.js" type="text/javascript"></script>'."\r\n";
	echo '<script src="' . LOVEFILM_WS_RESOURCES_URL . '/js/json2.js" type="text/javascript"></script>'."\r\n";
    
    echo '	<!--[if lt IE 7]>
	<script src="' . LOVEFILM_WS_RESOURCES_URL . '/js/belated-0.0.8a.min.js" type="text/javascript"></script>
	<script>
	jQuery(document).ready(function() {
    });
	</script>
	<![endif]-->'."\r\n";

	echo '<script src="' . LOVEFILM_WS_RESOURCES_URL . '/js/widgets.js" type="text/javascript"></script>'."\r\n";
	echo '<!--[if lt IE 9]><script src="' . LOVEFILM_WS_RESOURCES_URL . '/js/html5.js"></script><![endif]-->'."\r\n";
}	
/**
 * Used to unregister a setting.
 */
function lovefilm_clear_setting()
{
	echo NULL;
}