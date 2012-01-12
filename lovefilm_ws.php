<?php

/**
 * LOVEFiLM WordPress Widget
 * http://lovefilm.com/widget
 * 
 * Part of the LOVEFiLM WordPress Widget Plug-in.
 * Contains all the functions called to interact
 * with the LOVEFiLM Widget Web Service.
 */
require_once("lovefilm_ws_constants.php");
/**
 * Constant definitions.
 */
define('LOVEFILM_WS_REL_REGISTER_PLUGIN',     'lovefilm-register-plugin');
define('LOVEFILM_WS_REL_UNREGISTER_PLUGIN',   'lovefilm-unregister-plugin');
define('LOVEFILM_WS_REL_SUBMIT_USAGE_DATA',   'lovefilm-submit-usage-data');
define('LOVEFILM_WS_REL_GET_ASSIGNED_TITLES', 'lovefilm-assigned-titles');
define('LOVEFILM_WS_REL_GET_MARKETING_MSG',   'lovefilm-message');
define('LOVEFILM_WS_REL_PROMO_CODE',          'lovefilm-promo');

define('LOVEFILM_HTTP_STATUS_OK', 200);
define('LOVEFILM_HTTP_STATUS_BAD', 400);

class LoveFilmWebServiceException extends Exception
{
}

class LoveFilmBadServiceEndpointException extends LoveFilmWebServiceException
{
}

class LoveFilmWebServiceNotFoundException extends LoveFilmWebServiceException
{
}

class LoveFilmMarketingMessageNotFoundException extends LoveFilmWebServiceException
{
}

class LoveFilmPromoCodeNotFoundException extends LoveFilmWebServiceException
{
}

class LoveFilmWebServiceErrorException extends LoveFilmWebServiceException
{
}

class UIDIsNullException extends LoveFilmWebServiceException
{
}

function lovefilm_ws_service_end_points()
{
 	$response = wp_remote_get(LOVEFILM_WS_API_URL, array());

 	if(is_wp_error($response)) {
 		_log("lovefilm_ws_service_end_points: ".$response->get_error_messages());
		return false; 	
	}

	if(wp_remote_retrieve_response_code($response) < 200 || wp_remote_retrieve_response_code($response) >= 300) {
		_log("lovefilm_ws_register_uid: ".wp_remote_retrieve_response_code($response)." ".wp_remote_retrieve_response_message($response)."\n".wp_remote_retrieve_body($response));
		return false;
	}
	
	$headers = wp_remote_retrieve_headers($response);
	
    if(!array_key_exists('link', $headers)) {
    	_log("lovefilm_ws_service_end_points: Could not locate Link header in response\n".var_export($headers, true));
    	return false;
    }
    
	$services = array();
	$links = explode(",", $headers['link']);
    foreach($links as $link)
	{
        $rel = null;
        $href = null;

		if(preg_match("/rel=\"(.+)\"/", $link, $matches))
			$rel = $matches[1];

		if(preg_match("/\<(.+)\>/", $link, $matches))
			$href = $matches[1];

		if($rel && $href)
			$services[$rel] = LOVEFILM_WS_API_URL.$href;
	}
	update_option('lovefilm-ws-endpoints', $services);
    return true;
}

function lovefilm_ws_register_uid()
{
	// Ensure that the UID is wiped first
	//update_option('lovefilm-uid', NULL);
	
	$path     = lovefilm_ws_get_service_endpoint(LOVEFILM_WS_REL_REGISTER_PLUGIN);
    $domain   = get_option('siteurl');
	
	$args = array();
	$args['headers'] = array('Content-Type'=>'application/x-www-form-urlencoded');
	$args['body'] = http_build_query(array('domain' => $domain), '', '&');
    $response = wp_remote_post($path, $args);

    if(is_wp_error($response))
    {
    	_log("lovefilm_ws_register_uid: ".implode("\n", $response->get_error_messages()));
    	return false;
    }

    if(wp_remote_retrieve_response_code($response) >= 200 && wp_remote_retrieve_response_code($response) < 300) {
    	$uid = wp_remote_retrieve_body($response);
    	update_option('lovefilm-uid', $uid);
    	_log("lovefilm_ws_register_uid: UID Recieved from WebService for domain '".$domain."' = ". $uid);
    	return $uid;
    }

    _log("lovefilm_ws_register_uid: ".wp_remote_retrieve_response_code($response)." ".wp_remote_retrieve_response_message($response)."\n".wp_remote_retrieve_body($response));
    return false;
}

function lovefilm_ws_unregister_uid($uid)
{
    if(!is_string($uid))
        throw new InvalidArgumentException("UID must be a string");

    try {
    	$path = lovefilm_ws_get_service_endpoint(LOVEFILM_WS_REL_UNREGISTER_PLUGIN);
    } catch(Exception $e) {
		return false;    	
    }

    $path .= '?'.http_build_query(array('uid' => $uid), '', '&');
    $response = wp_remote_request($path, array('method' => 'DELETE'));
    
    if(is_wp_error($response))
    {
    	_log("lovefilm_ws_unregister_uid: ".implode("\n", $response->get_error_messages()));
    	return false;
    }
    
    if(wp_remote_retrieve_response_code($response) >= 200 && wp_remote_retrieve_response_code($response) < 300) {
    	return (int) wp_remote_retrieve_body($response);
    }
    
    _log("lovefilm_ws_unregister_uid: ".wp_remote_retrieve_response_code($response)." ".wp_remote_retrieve_response_message($response)."\n".wp_remote_retrieve_body($response));
    return false;
}

/**
 * Check the database for embedded titles for current page.
 *
 * @global WPDB $wpdb
 *
 * @return int
 */
function lovefilm_ws_check_embedded_titles()
{
    global $wpdb;

    $page = $wpdb->escape(lovefilm_ws_get_pagehash());

    $sql = "SELECT * FROM `LFW_Page`
            WHERE page_id = '" . $page . "'";

    $result = $wpdb->get_results($sql);

    return $result;
}

/**
 * Queries the web service for titles assigned to this page.
 * Notes that the context query var is not used by the webservice
 * as part of this request, it is merely used to "trip" the HTTP
 * proxy into serving the right cached result.
 * 
 * @throws LoveFilmBadServiceEndpointException
 * @throws LoveFilmWebServiceNotFoundException
 * @return array
 */
function lovefilm_ws_get_embedded_titles_ws()
{
   $path   = lovefilm_ws_get_service_endpoint(LOVEFILM_WS_REL_GET_ASSIGNED_TITLES);
    $uid    = get_option('lovefilm-uid');
    $context = get_option('lovefilm_context');
    if(is_null($uid) || $uid === FALSE)
    {
    	_log("lovefilm_ws_get_embedded_titles_ws: UID is NULL");
    	throw new UIDIsNullException();
    }
    
    $reqUri = lovefilm_ws_get_page();
    
	$path .= "?".http_build_query(array("id" => $uid, "page" => $reqUri, "context" => $context), '', '&');
	$response = wp_remote_get($path, array('timeout'=>LOVEFILM_HTTP_TIMEOUT));
	
   	if(is_wp_error($response))
   	{
   		_log("lovefilm_ws_get_embedded_titles_ws Error: ".implode("\n", $response->get_error_messages()));
   		throw new LoveFilmWebServiceErrorException(implode("\n", $response->get_error_messages()), -1);
   	}
   		
   	if(wp_remote_retrieve_response_code($response) < 200 || wp_remote_retrieve_response_code($response) >=300)
   	{
   		_log("lovefilm_ws_get_embedded_titles_ws Not OK: ".wp_remote_retrieve_response_message($response)." ".wp_remote_retrieve_response_code($response));
   		throw new LoveFilmWebServiceErrorException(wp_remote_retrieve_response_message($response), wp_remote_retrieve_response_code($response));
   	}
   	
   	return lovefilm_ws_parse_titles(wp_remote_retrieve_body($response));
}


/**
 * Parses the XML returned from web service call into an annon object.
 *
 * @return array
 */
function lovefilm_ws_parse_titles($xmlString)
{
	global $wpdb;
	
	_log("Parsing Titles");
	
    $domDoc = new DomDocument();
    if(!@$domDoc->loadXml($xmlString))
    	throw new Exception("Unable to parse titles:\n".$xmlString);
    $catalog     = $domDoc->getElementsByTagName('catalog');
    $assignments = $domDoc->getElementsByTagName('assignment');

    if(!$catalog->length > 0 || !$catalog->item(0)->hasAttribute('domain'))
    	throw new Exception("Catalog element does not have child nodes, or attribute domain is missing");
    
    update_option('lovefilm_domain', $catalog->item(0)->attributes->getNamedItem('domain')->nodeValue);
    
    $items = array();

    if($assignments->length > 0)
    {
        foreach($assignments as $assignment)
        {
            $catOb = new stdClass();
            $catOb->position = $assignment->attributes->getNamedItem('position')->nodeValue;
            $catOb->nofollow = $assignment->attributes->getNamedItem('nofollow')->nodeValue;
			if($catOb->nofollow == 'true' || $catOb->nofollow == '1')
				$catOb->nofollow = 1;
			else
				$catOb->nofollow = 0;
            
            foreach($assignment->childNodes as $cItem)
            {

                foreach($cItem->childNodes as $childNode)
                {
                    switch($childNode->nodeName)
                    {
                        case 'id':
                            $catOb->id = $childNode->nodeValue;
                             break;

                        case 'title':
                            $catOb->title = $childNode->attributes->getNamedItem('clean')->nodeValue;
                            break;

                        case 'link':
                            if($childNode->hasAttributes() && $childNode->attributes->getNamedItem('title')->nodeValue == 'web page')
                            {
                                $catOb->url = $childNode->attributes->getNamedItem('href')->nodeValue;
                            }
                            elseif($childNode->hasAttributes() && $childNode->attributes->getNamedItem('title')->nodeValue == 'artworks')
                            {
                                $artworks = $domDoc->saveXML($childNode);
                                $catOb->images = lovefilm_ws_parse_artworks($artworks);
                            }
                            break;

                        case 'release_date':
                            $catOb->releaseDate = $childNode->nodeValue;
                            break;

                        case 'rating':
                            $catOb->rating = $childNode->nodeValue;
                            break;

                        default:
                            // Not required atrr
                            break;
                    }
                }
            }
            
            if (property_exists($catOb, 'id'))
    		{
    			$catOb->hash = md5($catOb->id, true);
    		} else {
    			$catOb->hash = md5($catOb->title.$catOb->url, true);
    			$catOb->id = $catOb->url;
    		}

            $items[] = $catOb;
        }
    }
    _log("Done parsing titles: ".count($items));
    return $items;
}

/**
 * Parses the artwork xml of a catalog item.
 *
 * @param string $xmlString
 *
 * @return array
 */
function lovefilm_ws_parse_artworks($artWorks)
{
    $doc = new DomDocument();

    $doc->loadXml($artWorks);

    $artworkElements = $doc->getElementsByTagName('artwork');

    $len = $artworkElements->length;

    foreach($artworkElements as $artwork)
    {
        $type = $artwork->attributes->getNamedItem('type');
        if($artwork->attributes->getNamedItem('type')->value == 'title')
        {
            $imageArray = array();

            foreach($artwork->childNodes as $image)
            {
                $im = new stdClass();
                $im->size = $image->attributes->getNamedItem('size')->value;
                $im->href = $image->attributes->getNamedItem('href')->value;
                $im->height = $image->attributes->getNamedItem('height')->value;
                $im->width = $image->attributes->getNamedItem('width')->value;

                $size = $im->size;
                $imageArray[$size] = $im;
            }

            return $imageArray;
        }
    }
}

/**
 * Gets embedded titles from the database.
 *
 * @return void
 */
function lovefilm_ws_get_embedded_titles_db()
{
    global $wpdb;

    $page = $wpdb->escape(lovefilm_ws_get_pagehash());

    $sql = 'SELECT
                 c.`catalogitem_lovefilm_resource_id` as id ,
                 c.`catalogitem_url` as url,
                 c.`catalogitem_title` as title,
                 c.`catalogitem_releasedate` as releaseDate,
                 c.`catalogitem_updated` as updated,
                 c.`catalogitem_rating` as rating,
                 c.`catalogitem_imageurl` as imageUrl,
                 a.`nofollow` as nofollow
           FROM LFW_PageAssignment a
           JOIN  LFW_CatalogItem c ON c.catalogitem_id = a.catalogitem_id
           WHERE a.page_id = \'%s\'
           ORDER BY a.assignment_position ASC;';

    $sql     = sprintf($sql, $page);
    return $wpdb->get_results($sql);
}

/**
 * Persists embedded titiles to the database.
 *
 * @return void
 */
function lovefilm_ws_set_embedded_titles_db($titles)
{
	global $wpdb;
	
    $page     = lovefilm_ws_get_page();
    $pageHash = lovefilm_ws_get_pagehash();

    try {
            lovefilm_ws_insup_catalogitems($titles);
	    lovefilm_ws_insup_assignments($pageHash, $titles);
	    lovefilm_ws_insup_page($pageHash, $page);
    } catch(Exception $e) {
	    _log($e);
	    throw new Exception("Could not insert into cache: ".$e->getMessage(), $e->getCode());
    }
}

function lovefilm_ws_insup_catalogitems($titles)
{
    global $wpdb;

    foreach($titles as $title)
    {
    	if(property_exists($title, 'id') &&
    		property_exists($title, 'hash') &&
    		property_exists($title, 'url') &&
    		property_exists($title, 'title') &&
    		property_exists($title, 'releaseDate'))
    	{
    		$image_url = "";
	   		if(property_exists($title, 'images'))
	   		{
	   			if(is_array($title->images))
	   			{
	   				if(array_key_exists('small', $title->images))
	   				{
	   					$image_url = $title->images['small']->href;
	   				}
	   			}
	   		}
			
	   		$rating = (property_exists($title, 'rating'))?$title->rating:0;		
	   		   		
	   		$selectSql = "SELECT catalogitem_id FROM LFW_CatalogItem WHERE catalogitem_id = UNHEX('<id>');";
	   		
	   		$insertSql = "INSERT INTO LFW_CatalogItem SET
	   					  catalogitem_id = UNHEX('<id>'),
	   					  catalogitem_lovefilm_resource_id = %s,
	   					  catalogitem_url = %s,
	   					  catalogitem_title = %s,
	   					  catalogitem_releasedate = %s,
	   					  catalogitem_updated = %s,
	   					  catalogitem_rating = %d,
	   					  catalogitem_imageurl = %s;";
	   		
	   		$updateSql = "UPDATE LFW_CatalogItem
                          SET
	   					  catalogitem_lovefilm_resource_id = %s,
	   					  catalogitem_url = %s,
	   					  catalogitem_title = %s,
	   					  catalogitem_releasedate = %s,
	   					  catalogitem_updated = %s,
	   					  catalogitem_rating = %s,
	   					  catalogitem_imageurl = %s
	   					  WHERE catalogitem_id = UNHEX('<id>');";
	    	
	   		$selectSql = str_replace('<id>', bin2hex($title->hash), $selectSql);
	   		$insertSql = str_replace('<id>', bin2hex($title->hash), $insertSql);
	   		$updateSql = str_replace('<id>', bin2hex($title->hash), $updateSql);

		    $result = $wpdb->query($selectSql);
		    
		    if($result === FALSE) {
		    	return false;
		    }
		    
			if($result == 0) {
		    	$result = $wpdb->query($wpdb->prepare($insertSql, $title->id, $title->url, $title->title, $title->releaseDate, date('Y-m-d H:i:s'), $rating, $image_url));
			} else {
		   		$result = $wpdb->query($wpdb->prepare($updateSql, $title->id, $title->url, $title->title, $title->releaseDate, date('Y-m-d H:i:s'), $rating, $image_url));
		   		if($result === FALSE) {
		   			return false;
		   		}
		    }
    	} else {
    		_log("Missing properties: id, hash, url, title, releaseDate; images");
    	}
    }
}

function lovefilm_ws_insup_page($pageHash, $pageUri)
{
    global $wpdb;

    $selectSql = "SELECT page_id FROM LFW_Page WHERE page_id = UNHEX('<id>');";
    
    $insertSql = "INSERT INTO LFW_Page SET
    			  page_id = UNHEX('<id>'),
    			  page_datequeried = %s,
    			  page_uri = %s
    			  ;";
    
    $updateSql = "UPDATE LFW_Page 
    			  SET 
                  page_datequeried = %s,
    			  page_uri = %s
    			  WHERE page_id = UNHEX('<id>');";

	$selectSql = str_replace('<id>', bin2hex($pageHash), $selectSql);
    $insertSql = str_replace('<id>', bin2hex($pageHash), $insertSql);
	$updateSql = str_replace('<id>', bin2hex($pageHash), $updateSql);
    
    $result = $wpdb->query($selectSql);
    
    if($result === FALSE) {
    	return false;
    }
    
	if($result == 0) {
		$result = $wpdb->query($wpdb->prepare($insertSql, date('Y-m-d H:i:s'), $pageUri));
	} else {
    	$result = $wpdb->query($wpdb->prepare($updateSql, date('Y-m-d H:i:s'), $pageUri));

    	if($result === FALSE) {
    	   	return false;
    	}
    }
}

function lovefilm_ws_insup_assignments($pageId, $titles)
{
    global $wpdb;

    foreach($titles as $title)
    {
    	if(property_exists($title, 'hash') && property_exists($title, 'position')) {

    		$selectSql = "SELECT page_id FROM LFW_PageAssignment WHERE page_id = UNHEX('<id>') AND catalogitem_id = UNHEX('<catId>');";
    		
    		$insertSql = "INSERT INTO LFW_PageAssignment SET
    					  page_id = UNHEX('<id>'),
    					  assignment_position = %s,
    					  nofollow = %d,
    					  catalogitem_id = UNHEX('<catId>')
    					  ;";
    	
    		$updateSql = "UPDATE LFW_PageAssignment 
                          SET
    					  assignment_position = %s,
    					  nofollow = %d
                          WHERE page_id = UNHEX('<id>') AND catalogitem_id = UNHEX('<catId>');";
    		
			$selectSql = str_replace('<id>', bin2hex($pageId), $selectSql);
			$selectSql = str_replace('<catId>', bin2hex($title->hash), $selectSql);
			
			$insertSql = str_replace('<id>', bin2hex($pageId), $insertSql);
    		$insertSql = str_replace('<catId>', bin2hex($title->hash), $insertSql);
			
    		$updateSql = str_replace('<id>', bin2hex($pageId), $updateSql);
    		$updateSql = str_replace('<catId>', bin2hex($title->hash), $updateSql);
		
		    $result = $wpdb->query($selectSql);
		    
		    if($result === FALSE) {
		    	return false;
		    }
                    if($result == 0) {
                            $result = $wpdb->query($wpdb->prepare($insertSql, $title->position, $title->nofollow));
                    } else {
                    $result = $wpdb->query($wpdb->prepare($updateSql, $title->position, $title->nofollow));

                    if($result === FALSE) {
                            return false;
                    }
		    }
    	} else {
    		_log("Missing properties: hash, position");
    	}
    }    
}

function lovefilm_ws_get_pagehash()
{
    return md5(lovefilm_ws_get_page(), true);
}

function lovefilm_ws_get_page()
{
    $domain = get_option('siteurl');
    $p = parse_url($domain);
    $page   = $p['scheme']."://".$p['host'].$_SERVER['REQUEST_URI'];
    return $page;
}


function lovefilm_ws_usage_data($uid=null, $data)
{
	if(is_null($uid))
		$uid = get_option('lovefilm-uid');
	
	if(is_null($uid))
		throw new UIDIsNullException($uid);
		
	if(!is_array($data))
        throw new InvalidArgumentException("Data must be an array");

    try {
    	$path = lovefilm_ws_get_service_endpoint(LOVEFILM_WS_REL_SUBMIT_USAGE_DATA);
    } catch(Exception $e) {
    	return false;
    }

    $content = array_merge(array("uid" => $uid), $data);
	$args = array();
	$args['headers'] = array('Content-Type'=>'application/x-www-form-urlencoded');
	$args['body'] = http_build_query($content, '', '&');
    $response = wp_remote_post($path, $args);
    
 	if(is_wp_error($response)) {
 		_log("lovefilm_ws_usage_data: ".$response->get_error_messages());
		return false; 	
	}

	if(wp_remote_retrieve_response_code($response) < 200 || wp_remote_retrieve_response_code($response) >= 300) {
		_log("lovefilm_ws_usage_data: ".wp_remote_retrieve_response_code($response)." ".wp_remote_retrieve_response_message($response)."\n".wp_remote_retrieve_body($response));
		return false;
	}

    return (int) wp_remote_retrieve_body($response);
}

function lovefilm_ws_change_context($context)
{
    global $wpdb;

    try {
    	$path = lovefilm_ws_get_service_endpoint('lovefilm-switch-context-plugin');
    } catch(Exception $e) {
    	return false;
	}

	$uid = get_option('lovefilm-uid');
	
	if($uid == NULL) {
		_log("lovefilm_ws_change_context: UID is NULL");
		return false;
	}
	
    $content = array(
                    'context' => $context,
                    'id' => $uid
                    );

	$path .= "?".http_build_query($content, '', '&');
	$response = wp_remote_request($path, array('timeout'=>LOVEFILM_HTTP_TIMEOUT, 'method' => 'PUT'));

	_log(var_export($response, true));
	
	if(is_wp_error($response))
	{
		_log("lovefilm_ws_change_context: ".implode("\n", $response->get_error_messages()));
		return false;
	}
	
   	if(wp_remote_retrieve_response_code($response) != 200)
   	{
   		_log("lovefilm_ws_change_context: ".wp_remote_retrieve_response_code($response)." ".wp_remote_retrieve_response_message($response)."\n".wp_remote_retrieve_body($response));
   		return false;
   	}	

    $wpdb->query('DELETE FROM LFW_CatalogItem');
    $wpdb->query('DELETE FROM LFW_Page');
    $wpdb->query('DELETE FROM LFW_PageAssignment');

    return true;
}

function lovefilm_ws_get_service_endpoint($rel)
{
    $services = get_option('lovefilm-ws-endpoints');
    if(!is_array($services) || !array_key_exists($rel, $services))
    {
        throw new LoveFilmBadServiceEndpointException($rel);
    }

    return $services[$rel];
}
/**
 * @desc This returns the cached Marketing message.
 * @return type String 
 */
function lovefilm_ws_get_cached_marketing_msg()
{
    $marketingMsg = get_option('lovefilm-marketing-message');
    
    return $marketingMsg;
}
/**
 * @desc This returns the marketing message from webservice and it depends on the lovefilm cron to update the cache.
 * @return type string
 */
function lovefilm_ws_get_marketing_msg()
{
	error_log('Cron job running in MARKATING MESSAGES function.');

    	$path = lovefilm_ws_get_service_endpoint(LOVEFILM_WS_REL_GET_MARKETING_MSG);
    	$response = wp_remote_get($path, array());
	    	
    	if(is_wp_error($response)) {
    		_log("lovefilm_ws_get_marketing_msg: ".implode("\n", $response->get_error_messages()));
    		throw new LoveFilmMarketingMessageNotFoundException("WP Error retrieving marketing message: ".var_export($response, true));
    	}
    	
    	if(wp_remote_retrieve_response_code($response) < 200 || wp_remote_retrieve_response_code($response) >= 300)
    	{
    		_log("lovefilm_ws_get_marketing_msg: ".wp_remote_retrieve_response_code($response)." ".wp_remote_retrieve_response_message($response)."\n".wp_remote_retrieve_body($response));
    		throw new LoveFilmMarketingMessageNotFoundException("HTTP Error retrieving marketing message: ".var_export($response, true));
    	}
	    	
    	$marketingMsg = json_decode($response['body']);
    	update_option('lovefilm-marketing-message', $marketingMsg);
}

function lovefilm_ws_get_cached_promo_code()
{
     return get_option('lovefilm-promo-code');
}
function lovefilm_ws_get_promo_code()
{
    error_log('Cron job running in PROMO-CODE function.');
    $path = lovefilm_ws_get_service_endpoint(LOVEFILM_WS_REL_PROMO_CODE);
    $response = wp_remote_get($path, array());

    if(is_wp_error($response)) {
        _log("lovefilm_ws_get_promo_code: ".implode("\n", $response->get_error_messages()));
        throw new LoveFilmPromoCodeNotFoundException("WP Error retrieving promo code: ".var_export($response, true));
    }

    if(wp_remote_retrieve_response_code($response) < 200 || wp_remote_retrieve_response_code($response) >= 300) {
        _log("lovefilm_ws_get_promo_code: ".wp_remote_retrieve_response_code($response)." ".wp_remote_retrieve_response_message($response)."\n".wp_remote_retrieve_body($response));
        throw new LoveFilmPromoCodeNotFoundException("HTTP Error retrieving promo code: ".var_export($response, true));
    }

    parse_str($response['body'], $results);
    if(!array_key_exists('promoCode', $results))
        throw new LoveFilmPromoCodeNotFoundException("No promoCode passed in response");

    // Success!
    update_option('lovefilm-promo-code', $results['promoCode']);
}

if(!function_exists('http_build_query')) {
    function http_build_query($data,$prefix=null,$sep='',$key='') {
        $ret = array();
        foreach((array)$data as $k => $v) {
        	$k = urlencode($k);
            if(is_int($k) && $prefix != null) {
            	$k = $prefix.$k;
            }
            if(!empty($key)) {
            	$k = $key."[".$k."]";
            }

            if(is_array($v) || is_object($v)) {
            	array_push($ret,http_build_query($v,"",$sep,$k));
            } else {
                array_push($ret,$k."=".urlencode($v));
            }
         }
        if(empty($sep)) {
            $sep = ini_get("arg_separator.output");
        }

        return implode($sep, $ret);
	};
};
