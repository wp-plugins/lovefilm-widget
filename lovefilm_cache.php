<?php
/**
 * LOVEFiLM WordPress Widget
 * http://lovefilm.com/widget
 * 
 * Part of the LOVEFiLM WordPress Widget Plug-in.
 * Contains all the functions called as part of the
 * plug-in WordPress caching system.
 */

/**
 * Returns an array of URLs that the plugin is active on.
 * 
 * @return array() An array or URLS that the plugin is active on.
 */
function lovefilm_cache_get_plugin_page_urls() {
	return array();	
}

function lovefilm_cache_set( $pageURL, $contentType, $meta, $content ) {
	
}

function lovefilm_cache_get( $pageURL, $contentType, $meta ) {
	
}

function lovefilm_cache_flush_cache( $pageURL ) {
	global $wpdb;
	$wpdb->query( $wpdb->prepare("DELETE FROM LFW_Page WHERE page_uri = %s", $pageURL));
	return true;
}
