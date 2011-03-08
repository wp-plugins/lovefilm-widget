<?php

/**
 * LOVEFiLM WordPress Widget
 * http://lovefilm.com/widget
 * 
 * Part of the LOVEFiLM WordPress Widget Plug-in.
 * Contains all the functions called as part of the
 * plug-in WordPress admin system.
 */
/**
 * Constant definitions.
 */
define('LOVEFILM_OPTIONS_PAGE_TITLE', 'LOVEFiLM Widget Configuration');
define('LOVEFILM_OPTIONS_MENU_TITLE', 'LOVEFiLM Widget');
define('LOVEFILM_OPTIONS_MENU_GLOBAL_TITLE', 'LOVEFiLM Widget Settings');
define('LOVEFILM_OPTIONS_MENU_SLUG', 'LOVEFiLM-Config-Slug');
define('LOVEFILM_OPTIONS_MENU_GLOBAL_SLUG', 'LOVEFiLM-Config-Top-Slug');

/**
 * Displays the LOVEFiLM Widget Configuration Panel
 * in the WordPress Admin system.
 */
function lovefilm_admin_show_options_panel()
{

    if(!current_user_can('manage_options'))
    {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    /* Display the Admin Configuration Panel */
    //settings_fields( 'lovefilm-settings' );
    require_once( 'lovefilm_admin_panel_new.php' );
}

/**
 * Collects useage data about the current WordPress
 * install to send back to LOVEFiLM Web Service.
 */
function lovefilm_admin_collect_useage_data()
{
    $args = array();

    $wp_query = new WP_Query($args);

    while($wp_query->have_posts())
    {
        the_permalink($wp_query->the_post()->ID);
    }

    $options = get_option('lovefilm-settings');
    
    $context = 'films'; // Default context 
    if(is_array($options) && array_key_exists('context', $options) && !is_string($options['context']))
    	$context = $options['context'];

    $vHandle = fopen(dirname(__FILE__) . '/ver', 'r');
    $version = fgets($vHandle);
    fclose($vHandle);

    $data = array(
             'context' => $context,
             'version' => $version
            );
    
    return $data;
}


function lovefilm_section_main()
{

}

function lovefilm_width_type_input()
{

    echo '<div id="lf_dmarker"></div>';

    $js = <<<EOT

<script language="JavaScript">
//<![CDATA
    dMarker = document.getElementById("lf_dmarker");
    dMarker.parentNode.parentNode.style.display = "none";
//]]>
</script>
EOT;

    echo $js;
}

function lovefilm_input_width()
{
    $options = get_option('lovefilm-settings');
    $empty = ($options['lovefilm_width_type'] == "" || is_null($options['lovefilm_width_type']));

    $radio = 'Fluid <input type="radio" name="lovefilm-settings[lovefilm_width_type]" value="fluid" onClick="toggleWidthVis(false)" %s />'
            . 'Fixed <input type="radio" name="lovefilm-settings[lovefilm_width_type]" value="fixed" onClick="toggleWidthVis(true)" %s/>';

    if($empty || $options['lovefilm_width_type'] == 'fluid')
    {
        echo sprintf($radio, 'CHECKED', '');
    }
    else
    {
        echo sprintf($radio, '', 'CHECKED');
    }

    $input = "<span id='lf_width_input_hide'><input type='text' id='lf_width_input' name='lovefilm-settings[lovefilm_width]' value='%s' /> pixels (min: 200, max: 350)</span>";

    $valueField = ($options['lovefilm_width_type'] == 'fluid') ? "" : $options['lovefilm_width'];

    echo sprintf($input, $valueField);

    // JS to do a show hide om the input box
    $js = <<<EOT

   <script language="JavaScript">
//<![CDATA[
    widthInput = document.getElementById('lf_width_input');
    
    if(widthInput.value == "")
    {
        toggleWidthVis(false);
    }

function toggleWidthVis(state)
{
    widthInput = document.getElementById('lf_width_input_hide');
    
    if(state)
    {
        widthInput.style.visibility = "visible";
    }
    else
    {
        widthInput.style.visibility = "hidden";
    }
}
//]]>

</script>
EOT;

    echo $js;
}

function lovefilm_input_widget_type()
{
    $options = get_option('lovefilm-settings');

    $selected = $options['type'];

    $select = '<select name="lovefilm-settings[type]" id="lovefilm_settings_type">';

    $select .= ( $selected == 'affiliate') ? '<option value="affiliate" selected="affiliate">Affiliate</option>' : '<option value="affiliate">Affiliate</option>';

    $select .= ( $selected == 'vanity') ? '<option value="vanity" selected="vanity">Vanity</option>' : '<option value="vanity">Vanity</option>';

    $select .= ( $selected == 'contextual') ? '<option value="contextual" selected="contextual">Contextual</option>' : '<option value="contextual">Contextual</option>';
    $select .= '</select>';

    echo $select;
}

function lovefilm_input_widget_theme()
{

    $options = get_option('lovefilm-settings');
    if(is_array($options) && array_key_exists('theme', $options))
    {
    	$selected = $options['theme'];
    }
    else
    {
    	$selected = 'light';
    }
    
    $select = '<select name="lovefilm-settings[theme]" id="lovefilm_settings_theme">';

    $select .= ( $selected == 'light') ? '<option value="light" selected="selected">Light</option>' : '<option value="light">Light</option>';

    $select .= ( $selected == 'dark') ? '<option value="dark" selected="selected">Dark</option>' : '<option value="dark">Dark</option>';

    $select .= '</select>';

    echo $select;
}

function lovefilm_input_widget_context()
{
    $options = get_option('lovefilm-settings');
    $selected = $options['context'];

    $select = '<select name="lovefilm-settings[context]" >';
    $select .= ( $selected == 'films') ? '<option value="films" selected="f">Film</option>' : '<option value="films">Film</option>';
    $select .= ( $selected == 'games') ? '<option value="games" selected="g">Game</option>' : '<option value="games">Game</option>';
    $select .= '</select>';

    echo $select;
}

function lovefilm_input_widget_aff()
{
    $options = get_option('lovefilm-settings');

    $input = "<input type='text' name='lovefilm-settings[lovefilm_aff]'
                value='%s' id='lovefilm_settings_aff' /> <span class='optional'>(optional)</span>";

    echo (isset($options['lovefilm_aff'])) ? sprintf($input, $options['lovefilm_aff']) : sprintf($input, '');
}

function lovefilm_validate_settings($input)
{

    $validInput = array();
    $validContext = array('films', 'games');
    $validType = array('contextual', 'vanity', 'affiliate');
    $validWidthType = array('fluid', 'fixed');
    $validTheme = array('light', 'dark');
    $error = false;
    // Width may not be null , empty, lower than 200 or greater than 450

    if($input['lovefilm_width_type'] != "fluid")
    {
        if(is_null($input['lovefilm_width']) || $input['lovefilm_width'] == '' ||
                $input['lovefilm_width'] < 200 || $input['lovefilm_width'] > 350)
        {
            add_settings_error('lovefilm-settins_lovefilm_width', 'width-error', 'Width is required and must be between 200 and 350', 'error');
            $validInput['lovefilm_width'] = $input['lovefilm_width'];
            $error = true;
        }
        else
        {
            $validInput['lovefilm_width'] = $input['lovefilm_width'];
        }
    }

    if(!in_array($input['context'], $validContext))
    {
        add_settings_error('lovefilm-settins_context', 'context-error', 'Invalid Context Specified', 'error');
        $validInput['context'] = $input['context'];
        $error = true;
    }
    else
    {
        $validInput['context'] = $input['context'];
    }
    /*
      if(!in_array($input['type'], $validType))
      {
      add_settings_error('lovefilm-settins_type', 'type-error', 'Invalid Type Specified', 'error');
      $validInput['type'] = $input['type'];
      }
      else
      {
      $validInput['type'] = $input['type'];
      } */$validInput['type'] = 'affiliate';

    if(!in_array($input['theme'], $validTheme))
    {
        add_settings_error('lovefilm-settins_type', 'type-error', 'Invalid Theme Specified', 'error');
        $validInput['theme'] = $input['theme'];
        $error = true;
    }
    else
    {
        $validInput['theme'] = $input['theme'];
    }

    if(!in_array($input['lovefilm_width_type'], $validWidthType))
    {
        add_settings_error('lovefilm-settins_lovefilm_width_type', 'type-error', 'Invalid Width Type Specified', 'error');
        $error = true;
    }
    else
    {
        $validInput['lovefilm_width_type'] = $input['lovefilm_width_type'];
    }

    $validInput['lovefilm_aff'] = $input['lovefilm_aff'];

    // Check if all validation has passed
    // If Context has changed
    // 1. Send a change of context request. 
    // 2. If successful: truncate the LoveFilm tables to ensure no 'out of context' titles are displayed.
    // 3. If fail: trigger WordPress settings error.

    if(!$error)
    {
        $options = get_option('lovefilm-settings');

        if($validInput['context'] != $options['context'])
        {
            // context switch has occured.
            $shiftResult = lovefilm_ws_change_context($validInput['context']);

            if(!$shiftResult)
            {
                // Remote context change failed - triggering this error stops the wp-option being updated.
                add_settings_error('lovefilm_aff', 'type-error', 'Unable to change Context (Films / Games) at this time. Please try again later.');
                $validInput['context'] = $options['context'];
            }
        }
    }

    return $validInput;
}

/*
 * Set up a cron job to run the every 24 hours to clear the DB cache.
 */
function lovefilm_admin_clearDbCache()
{
	// Clear marketing msg
	update_option('lovefilm-marketing-message', "");        
	try {
    	$titles = lovefilm_ws_get_embedded_titles_ws();
		lovefilim_clearall_pagetiltes();
    	lovefilm_ws_set_embedded_titles_db($titles);
	} 
	catch(Exception $e)
	{
		// WebService is not returing parsable results,
		// Try again later.
	}
}
    
// Runs the cron job action.
add_action ('lovefilm_cron', 'lovefilm_admin_clearDbCache');
    
    
/*
 * Flush all the entries form the page table cache
 */
function lovefilim_clearall_pagetiltes()
{
	global $wpdb;
	$wpdb->query( $wpdb->prepare("DELETE FROM LFW_Page"));
	$wpdb->query( $wpdb->prepare("DELETE FROM LFW_PageAssignment"));
	$wpdb->query( $wpdb->prepare("DELETE FROM LFW_CatalogItem"));
	return true;
}
    