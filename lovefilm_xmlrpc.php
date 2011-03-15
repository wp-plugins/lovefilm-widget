<?php
/**
 * LOVEFiLM WordPress Widget
 * http://lovefilm.com/widget
 * 
 * Part of the LOVEFiLM WordPress Widget Plug-in.
 * Contains all the functions called as part of the
 * plug-in WordPress XML-RPC service.
 */
require_once("lovefilm_cache.php");

define("ERR_BAD_ARG", 1);
define("ERR_BAD_UID", 2);

/**
 * Does a cache wipe and then re-fetches the marketing message.
 * 
 * @param string $msgUid The widget UID
 * 
 * @return string
 */
function lovefilm_cache_clear_marketing_message( $msgUid ) {
	try {
		lovefilm_confirm_uid($msgUid);
	} catch (Exception $e) {
		return $e->getMessage();
	}
	update_option('lovefilm-marketing-message', NULL);
	$m = lovefilm_ws_get_marketing_msg();
	return "Success";
}

function lovefilm_cache_clear_all( $msgUid ) {
	try {
		lovefilm_confirm_uid($msgUid);
	} catch (Exception $e) {
		return $e->getMessage();
	}
		
	// Clear named cache
	$urls = lovefilm_cache_get_plugin_page_urls();
	foreach( $urls as $url ) {
		lovefilm_cache_flush_cache( $url );
	}
	return "Success";
}

function lovefilm_cache_clear_page( $msg ) {
	
	if(count($msg)!=2)
		return "Bad Argument Count";
		
	try {
		lovefilm_confirm_uid($msg[0]);
	} catch (Exception $e) {
		return $e->getMessage();
	}
	
	if(!is_string($msg[1]))
		return "Bad argument: URL";
	
	lovefilm_cache_flush_cache( $msg[1] );
	return "Success";
}

function lovefilm_fetch_usage_stats() {
	try {
		lovefilm_confirm_uid($msgUid);
	} catch (Exception $e) {
		return $e->getMessage();
	}
	
	// Get usage stats
	
	return "Success";
}
 
function lovefilm_confirm_uid( $msgUid ) {
	if (!is_string($msgUid))
		throw new Exception("Bad argument: UID", ERR_BAD_ARG);
	
	// Check that UID passed to function matches
	// the UID of this plug-in.
	$uid = get_option( 'lovefilm-uid' );
	if ($uid != $msgUid) {
		throw new Exception("Bad UID", ERR_BAD_UID);
	}
	return true; 	
}

function lovefilm_set_promo_code( $msg ) {
	if(count($msg)!=2)
		return "Bad Argument Count";
		
	try {
		lovefilm_confirm_uid($msg[0]);
	} catch (Exception $e) {
		return $e->getMessage();
	}
	
	if(!is_string($msg[1]))
		return "Bad argument: Promo Code";
	
	update_option('lovefilm-promo-code', $msg[1]);
		
	return "Success";
}