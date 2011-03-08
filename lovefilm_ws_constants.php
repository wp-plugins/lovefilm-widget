<?php
/**
 * LOVEFiLM WordPress Widget
 * http://lovefilm.com/widget
 * 
 * Part of the LOVEFiLM WordPress Widget Plug-in.
 * Contains web service constants.
 */
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

define('LOVEFILM_WS_URL', $urlst);
define('LOVEFILM_WS_API_URL', LOVEFILM_WS_URL."api");