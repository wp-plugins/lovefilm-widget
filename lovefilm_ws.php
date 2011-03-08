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

define('LOVEFILM_CONTEXT_GAME', 'games');
define('LOVEFILM_CONTEXT_FILM', 'films');

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

class LoveFilmWebServiceErrorException extends LoveFilmWebServiceException
{
}

class UIDIsNullException extends LoveFilmWebServiceException
{
}

function lovefilm_ws_service_end_points()
{
	try {
   		$response = lovefilm_http_call("/", "GET");
	}
	catch(Exception $e) {
		return false;
	}
	
    if(!lovefilm_ws_check_status(LOVEFILM_HTTP_STATUS_OK, $response))
        return false;

    $services = array();
    foreach($response['meta'] as $header)
    {
        if(!preg_match("/^Link: /", $header))
            continue;

        $links = explode(",", $header);
        foreach($links as $link)
        {
            $rel = null;
            $href = null;

            if(preg_match("/rel=\"(.+)\"/", $link, $matches))
                $rel = $matches[1];

            if(preg_match("/\<(.+)\>/", $link, $matches))
                $href = $matches[1];

            if($rel && $href)
                $services[$rel] = $href;
        }
    }
    update_option('lovefilm-ws-endpoints', $services);
}

function lovefilm_ws_register_uid()
{
	// Ensure that the UID is wiped first
	update_option('lovefilm-uid', NULL);
	
	$path     = lovefilm_ws_get_service_endpoint(LOVEFILM_WS_REL_REGISTER_PLUGIN);
    $domain   = get_option('siteurl');
    $response = lovefilm_http_call($path, "POST", array('domain' => $domain));

    if(lovefilm_ws_check_status(LOVEFILM_HTTP_STATUS_BAD, $response))
    {
        throw new LoveFilmWebServiceErrorException("SiteUrl setting is invalid");
    }
    elseif(!lovefilm_ws_check_status(LOVEFILM_HTTP_STATUS_OK, $response))
    {
        throw new LoveFilmWebServiceErrorException("Response not Okay");
    }

    $uid = $response['body'];
    
    update_option('lovefilm-uid', $uid);

    _log("UID Recieved from WebService: ". $uid);

    return $uid;
}

function lovefilm_ws_unregister_uid($uid)
{
    if(!is_string($uid))
        throw new InvalidArgumentException("UID must be a string");

    $path = lovefilm_ws_get_service_endpoint(LOVEFILM_WS_REL_UNREGISTER_PLUGIN);

    $response = lovefilm_http_call($path, "DELETE", array("uid" => $uid));

    if(!lovefilm_ws_check_status(LOVEFILM_HTTP_STATUS_OK, $response))
        return false;

    return (int) $response['body'];
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
 * @throws LoveFilmBadServiceEndpointException
 * @throws LoveFilmWebServiceNotFoundException
 * @return array
 */
function lovefilm_ws_get_embedded_titles_ws()
{
    $path   = lovefilm_ws_get_service_endpoint(LOVEFILM_WS_REL_GET_ASSIGNED_TITLES);
    $uid    = get_option('lovefilm-uid');
    if(is_null($uid))
    	throw new UIDIsNullException();
    	
    _log("lovefilm_ws_get_embedded_titles_ws: ".var_export($uid, true));
    
    $reqUri = lovefilm_ws_get_page();
   	$response = lovefilm_http_call($path, "GET", array("id" => $uid,
            "page" => $reqUri));

    if(!lovefilm_ws_check_status(LOVEFILM_HTTP_STATUS_OK, $response))
		throw new LoveFilmWebServiceErrorException();
    
   	$titles = lovefilm_ws_parse_titles($response['body']);
    

    return $titles;
}

/**
 * Parses the XML returned from web service call into an annon object.
 *
 * @return array
 */
function lovefilm_ws_parse_titles($xmlString)
{
	global $wpdb;
	
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
    			$catOb->hash = $wpdb->escape(md5($catOb->id, true));
    		} else {
    			$catOb->hash = $wpdb->escape(md5($catOb->title.$catOb->url, true));
    			$catOb->id = $catOb->url;
    		}

            $items[] = $catOb;
        }
    }
   
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
           WHERE a.page_id = \'%s\'';
           ;

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

    @mysql_query("BEGIN", $wpdb->dbh);
    try {
    	lovefilm_ws_insup_catalogitems($titles);
	    lovefilm_ws_insup_assignments($pageHash, $titles);
	    lovefilm_ws_insup_page($pageHash, $page);
    } catch(Exception $e) {
	    @mysql_query("ROLLBACK", $wpdb->dbh);
	    _log($e);
	    throw new Exception("Could not insert into cache: ".$e->getMessage(), $e->getCode(), $e);
    }
    @mysql_query("COMMIT", $wpdb->dbh);
}

function lovefilm_ws_insup_catalogitems($titles)
{
    global $wpdb;
    
    $sql = 'INSERT INTO `LFW_CatalogItem`
                            (
                            `catalogitem_id`,
                            `catalogitem_lovefilm_resource_id`,
                            `catalogitem_url`,
                            `catalogitem_title`,
                            `catalogitem_releasedate`,
                            `catalogitem_updated`,
                            `catalogitem_rating`,
                            `catalogitem_imageurl`
                            )
                            VALUES ';

    $values = '';
    foreach($titles as $title)
    {
    	if(property_exists($title, 'id') &&
    		property_exists($title, 'hash') &&
    		property_exists($title, 'url') &&
    		property_exists($title, 'title') &&
    		property_exists($title, 'releaseDate'))
    	{
	   		if(property_exists($title, 'rating'))
	   			$rating = $title->rating;
	   		else
	   			$rating = 0;
    		
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
	   		} else {
	   			_log("No image found");
	   		} 
	   		
	        $qAr[] = $title->hash;
	        $qAr[] = $title->id;
	        $qAr[] = $wpdb->escape($title->url);
	        $qAr[] = $wpdb->escape($title->title);
	        $qAr[] = $wpdb->escape($title->releaseDate);
	        $qAr[] = date('Y-m-d H:i:s');
	        $qAr[] = $rating;
	        $qAr[] = $wpdb->escape($image_url);
	
	        $value = '(\'' . implode('\',\'', $qAr) . '\'),';
	        $values .= $value;
        	unset($qAr);
    	} else {
    	}

    }

    $sql .= substr($values, 0, -1);

    $sql .=' ON DUPLICATE KEY UPDATE catalogitem_rating=VALUES(catalogitem_rating),
                                     catalogitem_url=VALUES(catalogitem_url),
                                     catalogitem_updated=VALUES(catalogitem_updated)';

    if ($wpdb->query($sql) === FALSE) {
    	throw new Exception("Failed to insert into LFW_CatalogItem: ".mysql_error($wpdb->dbh), null);
    }
}

function lovefilm_ws_insup_page($pageHash, $pageUri)
{

    global $wpdb;

    $pageDate = date('Y-m-d H:i:s');

    $value = "'" . $wpdb->escape($pageHash) . "', '$pageDate', '" . $wpdb->escape($pageUri) . "' ";

    $sql = 'INSERT INTO LFW_Page
                    (
                     `page_id`,
                     `page_datequeried`,
                     `page_uri`
                    )
                    VALUE
                    (
                      %s
                    )
                   ON DUPLICATE KEY UPDATE 
                            page_datequeried=VALUES(page_datequeried)';

    $sql = sprintf($sql, $value);

    if ($wpdb->query($sql) === FALSE) {
    	throw new Exception("Failed to insert into LFW_Page: ".mysql_error($wpdb->dbh), null);
    }
}

function lovefilm_ws_insup_assignments($pageId, $titles)
{
    global $wpdb;

    $sql = "INSERT INTO LFW_PageAssignment
            (
            page_id,
            assignment_position,
            nofollow,
            catalogitem_id
            )
            VALUES ";

    $values = "";

    $hexPageId = bin2hex($pageId);
    
    foreach($titles as $title)
    {
    	
    	if(property_exists($title, 'hash') &&
    		property_exists($title, 'position'))
    	{
        	$values .= "(UNHEX('" . $hexPageId . "')," . $wpdb->escape($title->position).", ".$wpdb->escape($title->nofollow).", '" . $title->hash . "'),";
    	}
    }


    $values = substr($values, 0, -1);

    $sql .= $values;

    $sql .= 'ON DUPLICATE KEY UPDATE catalogitem_id=VALUES(catalogitem_id)';

    if ($wpdb->query($sql) === FALSE) {
    	throw new Exception("Failed to insert into LFW_PageAssignment: ".mysql_error($wpdb->dbh), null);
    }
}

function lovefilm_ws_get_pagehash()
{
    return md5(lovefilm_ws_get_page(), true);
}

function lovefilm_ws_get_page()
{
    $domain = get_option('siteurl');
    $page   = $domain . $_SERVER['REQUEST_URI'];
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

    $path = lovefilm_ws_get_service_endpoint(LOVEFILM_WS_REL_SUBMIT_USAGE_DATA);

    $content = array_merge(array("uid" => $uid), $data);
    $response = lovefilm_http_call($path, "POST", $content);

    if(!lovefilm_ws_check_status(LOVEFILM_HTTP_STATUS_OK, $response))
        return false;

    return (int) $response['body'];
}

function lovefilm_ws_change_context($context)
{
    global $wpdb;

    $path = lovefilm_ws_get_service_endpoint('lovefilm-switch-context-plugin');

    $content = array(
                    'context' => $context,
                    'id' => get_option('lovefilm-uid')
                    );

    $response = lovefilm_http_call($path, 'PUT', $content);

    if(lovefilm_ws_check_status(200, $response))
    {
        $wpdb->query('truncate LFW_CatalogItem');
        $wpdb->query('truncate LFW_Page');
        $wpdb->query('truncate LFW_PageAssignment');

        return true;
    }

    return false;
}

function lovefilm_http_call($path, $method, $content=null)
{
	global $servername;

    $origTime = ini_get('max_execution_time');

    ini_set('max_execution_time', 5000);

    if(!is_null($content) && !is_array($content))
        throw new InvalidArgumentException("Content must be an array");

    $method = strtoupper($method);

    if($method == "POST")
    {
        // Query Params in the Content Header for POST & PUT
        if(!is_null($content))
        {
            $opts['http'] = array(
            'method' => $method,
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query($content, '', '&'),
            'timeout' => 60,
            'ignore_errors' => true,
            );
        }
    }
    else
    {
        // Set the Query Method
        $opts['http'] = array('method' => $method);

        // Query params in the URI for GET and DELETE
        if(!is_null($content))
        {
            $contentAr = array();
            foreach($content as $key => $value)
            {
                $contentAr[] = $key . "=" . urlencode($value);
            }

            $path .= "?" . implode('&', $contentAr);
        }
    }
    
    $ctx = stream_context_create($opts);
    $fh = @fopen(LOVEFILM_WS_API_URL . $path, 'r', false, $ctx);
    if(is_null($fh) || !$fh)
    {
        throw new LoveFilmWebServiceNotFoundException(LOVEFILM_WS_API_URL . $path);
    }
    $meta = stream_get_meta_data($fh);
    $body = stream_get_contents($fh);
    fclose($fh);

    /* Dump the HTTP Request and Response to the debug.log */
    _log("HTTP Request:\n".LOVEFILM_WS_API_URL . $path."\n".var_export($opts, true));
    lovefilm_log_http_response($meta, $body);
    
    if(!is_array($meta))
        throw new Exception("No headers returned");

    if(array_key_exists('headers', $meta['wrapper_data']))
    	$meta['wrapper_data'] = $meta['wrapper_data']['headers'];
    	 
    return array("meta" => $meta['wrapper_data'], "body" => $body);
}

function lovefilm_log_http_response($meta, $body)
{
    $d = "";
	foreach($meta as $key=>$val)
	{
		if(is_array($val))
		{
			$d .= $key."\n";
			foreach($val as $key2=>$val2)
			{
				$d .= "\t".$key2." = ".$val2."\n";
			}
		}
		else
		{
			$d .= $key." = ".$val."\n";
		}
	}
	$d .= "\n\n".$body;    
    _log("HTTP Response:\n".$d);
}

function lovefilm_ws_check_status($statusCode, $response)
{
	if(is_null($response))
		return false;
		
    if(!preg_match("/HTTP\/1\.1 $statusCode /", $response['meta'][0]))
        return false;

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

function lovefilm_ws_get_marketing_msg()
{
	error_log('Cron job in MARKATING MESSAGES function.');
    $marketingMsg = get_option('lovefilm-marketing-message');
	if(is_null($marketingMsg) || $marketingMsg===FALSE || (is_string($marketingMsg) && strlen($marketingMsg)==0))
	{
	    try {
	    	$path = lovefilm_ws_get_service_endpoint(LOVEFILM_WS_REL_GET_MARKETING_MSG);

	    	$content = array();
	    
	    	$response = lovefilm_http_call($path, "GET", $content);
		    if(!lovefilm_ws_check_status(LOVEFILM_HTTP_STATUS_OK, $response))
		    {
		    	$marketingMsg = lovefilm_ws_get_default_marketing_msg();
		    } else {
		    	$marketingMsg = json_decode($response['body']);
		    	update_option('lovefilm-marketing-message', $marketingMsg);
		    }
	    } catch(LoveFilmWebServiceException $e) {
	    	$marketingMsg = NULL;
	    	update_option('lovefilm-marketing-message', "");
	    }
	}
	return $marketingMsg;
}

// Runs the cron job

add_action ('lovefilm_cron', 'lovefilm_ws_get_marketing_msg');



function lovefilm_ws_get_default_marketing_msg()
{
	return array('anchor_text'=>'Start a free trial &gt;', 'href'=>'http://www.lovefilm.com/');
}

function lovefilm_ws_get_promo_code()
{
	$promoCode = get_option('lovefilm-promo-code');
	if($promoCode===FALSE || (is_string($promoCode) && strlen($promoCode)==0))
	{
	    try {
			$path = lovefilm_ws_get_service_endpoint(LOVEFILM_WS_REL_PROMO_CODE);
	    	$response = lovefilm_http_call($path, "GET", NULL);
			if(lovefilm_ws_check_status(LOVEFILM_HTTP_STATUS_OK, $response))
			{
				parse_str($response['body'], $results);
				if(!array_key_exists('promoCode', $results))
					throw new Exception("No promoCode passed in response");
				$promoCode = $results['promoCode'];
			}
	    } catch(Exception $e)
	    {
	    	_log($e);
	    	$promoCode = NULL;
	    }
	}
	return $promoCode;
}

