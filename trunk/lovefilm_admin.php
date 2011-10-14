<?php

/**
 * LOVEFiLM WordPress Widget
 * http://lovefilm.com/widget
 * 
 * Part of the LOVEFiLM WordPress Widget Plug-in.
 * Contains all the functions called as part of the
 * plug-in WordPress admin system.
 */
require_once("lovefilm_strings_en.php");
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


    $data = array(
             'context' => $context,
             'version' => LOVEFILM_WIDGET_VERSION
            );
    
    return $data;
}

//required as part of the lovefilm_admin_register_settings.
function lovefilm_section_apperance()
{
    //echo "<div align=center></div>";
}

function lovefilm_section_earn()
{
    echo '<p style="margin-left: 10px;">Please select the payment scheme.</p>';
   // echo "hi";
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
    echo '<span class="tooltip">?<span>'.LOVEFILM_STR_ADMIN_WIDTH_TYPE.'</span></span>';
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

    $select .= ( $selected == LOVEFILM_WIDGET_MODE_AFFILIATE) ? '<option value="'.LOVEFILM_WIDGET_MODE_AFFILIATE.'" selected="'.LOVEFILM_WIDGET_MODE_AFFILIATE.'">'.LOVEFILM_WIDGET_MODE_AFFILIATE.'</option>' : '<option value="'.LOVEFILM_WIDGET_MODE_AFFILIATE.'">'.LOVEFILM_WIDGET_MODE_AFFILIATE.'</option>';

    $select .= ( $selected == LOVEFILM_WIDGET_MODE_VANITY) ? '<option value="'.LOVEFILM_WIDGET_MODE_VANITY.'" selected="'.LOVEFILM_WIDGET_MODE_VANITY.'">'.LOVEFILM_WIDGET_MODE_VANITY.'</option>' : '<option value="'.LOVEFILM_WIDGET_MODE_VANITY.'">'.LOVEFILM_WIDGET_MODE_VANITY.'</option>';

    $select .= ( $selected == LOVEFILM_WIDGET_MODE_CONTEXT) ? '<option value="'.LOVEFILM_WIDGET_MODE_CONTEXT.'" selected="'.LOVEFILM_WIDGET_MODE_CONTEXT.'">'.LOVEFILM_WIDGET_MODE_CONTEXT.'</option>' : '<option value="'.LOVEFILM_WIDGET_MODE_CONTEXT.'">'.LOVEFILM_WIDGET_MODE_CONTEXT.'</option>';
    $select .= '</select>';

    echo $select;
}

function lovefilm_input_widget_theme()
{
    
    //toop tip
    echo '<span class="tooltip">?<span>Choose whether the widget is to use the light or dark colour theme.</span></span>';
    
    $options = get_option('lovefilm-settings');
    if(is_array($options) && array_key_exists('theme', $options))
    {
    	$selected = $options['theme'];
    }
    else
    {
    	$selected = LOVEFILM_THEME_LIGHT;
    }
    
    $select = '<select name="lovefilm-settings[theme]" id="lovefilm_settings_theme">';

    $select .= ( $selected == LOVEFILM_THEME_LIGHT) ? '<option value="'.LOVEFILM_THEME_LIGHT.'" selected="selected">'.LOVEFILM_THEME_LIGHT.'</option>' : '<option value="'.LOVEFILM_THEME_LIGHT.'">'.LOVEFILM_THEME_LIGHT.'</option>';

    $select .= ( $selected == LOVEFILM_THEME_DARK) ? '<option value="'.LOVEFILM_THEME_DARK.'" selected="selected">'.LOVEFILM_THEME_DARK.'</option>' : '<option value="'.LOVEFILM_THEME_DARK.'">'.LOVEFILM_THEME_DARK.'</option>';

    $select .= '</select>';

    echo $select;
}

function lovefilm_input_widget_context()
{
    //tool tip
    echo '<span class="tooltip">?<span>'.LOVEFILM_STR_ADMIN_WIDGET_CONTEXT.'</span></span>';
    
    $options = get_option('lovefilm-settings');
    $selected = $options['context'];

    $select = '<select name="lovefilm-settings[context]" >';
    $select .= ( $selected == LOVEFILM_CONTEXT_FILM) ? '<option value="'.LOVEFILM_CONTEXT_FILM.'" selected="f">'.LOVEFILM_CONTEXT_FILM.'</option>' : '<option value="'.LOVEFILM_CONTEXT_FILM.'">'.LOVEFILM_CONTEXT_FILM.'</option>';
    $select .= ( $selected == LOVEFILM_CONTEXT_GAME) ? '<option value="'.LOVEFILM_CONTEXT_GAME.'" selected="g">'.LOVEFILM_CONTEXT_GAME.'</option>' : '<option value="'.LOVEFILM_CONTEXT_GAME.'">'.LOVEFILM_CONTEXT_GAME.'</option>';
    $select .= '</select>';

    echo $select;
}

function lovefilm_input_widget_none()
{
     //tool tip
    echo '<span class="tooltip">?<span>Select None.</span></span>';
    
    $earn_type = get_option('lovefilm_earn_type');
    
    if($earn_type == 'none')
    {
       
        echo '<input type="radio" name="lovefilm-settings[earn_type]" class="earntype" value="none" checked="checked" />';
    }
    else
    {
        
        echo "<input type='radio' name='lovefilm-settings[earn_type]' class='earntype' value='none' />";
    }
    
    
    
}
function lovefilm_input_widget_aff()
{ 
  global $wp_version;
  if ( version_compare($wp_version,"3.2",">=")) 
  {
    
    $js = <<<EOT

   <script language="JavaScript">
//<![CDATA[
    jQuery(document).ready(function(){
    if(jQuery('.earntype:checked').val() == null)
	{
		jQuery('.earntype').filter('[value="none"]').prop('checked', true);

	}
            function validate(clicked)
            {
                if(clicked == 'aff')
                {
                    jQuery('#share_love').prop("disabled", true);
                    jQuery('#share_love').css("background-color", "#E4E4E4");
                    //jQuery('#share_love').val('');
                    jQuery('#lovefilm_settings_aff').prop("disabled", false);
                    jQuery('#lovefilm_settings_aff').css("background-color", "#FFFFFF");
                    
                    
                }
                if(clicked == 'share_love')
                {
                    jQuery('#lovefilm_settings_aff').prop("disabled", true);
                    jQuery('#lovefilm_settings_aff').css("background-color", "#E4E4E4");
                    //jQuery('#lovefilm_settings_aff').val('');
                    jQuery('#share_love').css("background-color", "#FFFFFF");
                    jQuery('#share_love').prop("disabled", false);
                }
                 if(clicked == 'none')
                {
                    jQuery('#lovefilm_settings_aff').prop("disabled", true);
                    jQuery('#lovefilm_settings_aff').css("background-color", "#E4E4E4");
                   // jQuery('#lovefilm_settings_aff').val('');
                    jQuery('#share_love').prop("disabled", true);
                    jQuery('#share_love').css("background-color", "#E4E4E4");
                   // jQuery('#share_love').val('');
                }
            }
            
            console.log(jQuery('.earntype:checked').val());
                validate(jQuery('.earntype:checked').val())
                jQuery('.earntype').click(function(e){
                validate(jQuery(this).val());
            });
   
        });
//]]>

</script>
EOT;
  }
  else
  {
       $js = <<<EOT

   <script language="JavaScript">
//<![CDATA[
    jQuery(document).ready(function(){
    if(jQuery('.earntype:checked').val() == null)
	{
		jQuery('.earntype').filter('[value="none"]').attr('checked', true);

	}
            function validate(clicked)
            {
                if(clicked == 'aff')
                {
                    jQuery('#share_love').attr("disabled", "disabled");
                    jQuery('#share_love').css("background-color", "#E4E4E4");
                    //jQuery('#share_love').val('');
                    jQuery('#lovefilm_settings_aff').attr("disabled", "");
                    jQuery('#lovefilm_settings_aff').css("background-color", "#FFFFFF");
                    
                    
                }
                if(clicked == 'share_love')
                {
                    jQuery('#lovefilm_settings_aff').attr("disabled", "disabled");
                    jQuery('#lovefilm_settings_aff').css("background-color", "#E4E4E4");
                    //jQuery('#lovefilm_settings_aff').val('');
                    jQuery('#share_love').css("background-color", "#FFFFFF");
                    jQuery('#share_love').attr("disabled", "");
                }
                 if(clicked == 'none')
                {
                    jQuery('#lovefilm_settings_aff').attr("disabled", "disabled");
                    jQuery('#lovefilm_settings_aff').css("background-color", "#E4E4E4");
                   // jQuery('#lovefilm_settings_aff').val('');
                    jQuery('#share_love').attr("disabled", "disabled");
                    jQuery('#share_love').css("background-color", "#E4E4E4");
                   // jQuery('#share_love').val('');
                }
            }
            
            console.log(jQuery('.earntype:checked').val());
                validate(jQuery('.earntype:checked').val())
                jQuery('.earntype').click(function(e){
                validate(jQuery(this).val());
            });
   
        });
//]]>

</script>
EOT;
}
  
    echo $js;
    //tool tip
    echo '<span class="tooltip">?<span>'.LOVEFILM_STR_ADMIN_AFFILIATE_CODE.'</span></span>';
    
    $options = get_option('lovefilm-settings');
    $input = null;
    $earn_type = get_option('lovefilm_earn_type');
    
    if ($earn_type == 'aff') {
        $input .= '<input type="radio" name="lovefilm-settings[earn_type]" class="earntype" value="aff" checked="checked"  />';
    } else {
        $input .= '<input type="radio" name="lovefilm-settings[earn_type]" class="earntype" value="aff"  />';
    }
    $input .= "<input type='text' name='lovefilm-settings[lovefilm_aff]'
                value='%s' id='lovefilm_settings_aff' />";

    echo (isset($options['lovefilm_aff'])) ? sprintf($input, get_option('lovefilm_aff_widget')) : sprintf($input, '');
}

function lovefilm_input_widget_share_love()
{   
    //tool tip
    echo '<span class="tooltip">?<span>'.LOVEFILM_STR_ADMIN_SHARE_LOVE.'</span></span>';
    
    $options = get_option('lovefilm_share_love');
    $input = null;
    $earn_type = get_option('lovefilm_earn_type');
    
    if($earn_type == 'share_love')
    {
        $input .= '<input type="radio" name="lovefilm-settings[earn_type]" class="earntype" value="share_love" checked="checked" />';
    }
    else
    {
        $input .= '<input type="radio" name="lovefilm-settings[earn_type]" class="earntype" value="share_love" />';
    }
    
    $input .= "<input type='text' name='lovefilm-settings[lovefilm_share_love]'
                value='%s' id='share_love' />";
    
    echo ($earn_type == 'share_love') ? sprintf($input, $options) : sprintf($input, '');
}

function lovefilm_input_contextual_links()
{
    //tool tip
    echo '<span class="tooltip">?<span>'.LOVEFILM_STR_ADMIN_CONTEXTUAL_DISPLAY_ARTICLE_LINK.'</span></span>';
    
    $options = get_option('lovefilm_contextual_display_article_link');
    ?>
    Yes<input name="lovefilm-settings[lovefilm_contextual_display_article_link]" type="radio" value="1" <?php checked( '1', get_option( 'lovefilm_contextual_display_article_link' ) ); ?> />
    No<input name="lovefilm-settings[lovefilm_contextual_display_article_link]" type="radio" value="0" <?php checked( '0', get_option( 'lovefilm_contextual_display_article_link' ) ); ?> />
    
    
  <?php 
  
}

function lovefilm_validate_settings($input)
{
    
	/**
	 * Array of valid Widget Contexts
	 */
    $validContext   = array(
    				   LOVEFILM_CONTEXT_FILM, 
    				   LOVEFILM_CONTEXT_GAME
    				  );
	/**
	 * Array of valid modes
	 */
   	$validMode      = array(
    			       LOVEFILM_WIDGET_MODE_CONTEXT, 
    			       LOVEFILM_WIDGET_MODE_VANITY,
    			       LOVEFILM_WIDGET_MODE_AFFILIATE
    			      );
    /**
     * Array of valid width types
     */
    $validWidthType = array(
    				   LOVEFILM_WIDTH_TYPE_FLUID, 
    				   LOVEFILM_WIDTH_TYPE_FIXED
    				  );
    /**
     * Array of valid themes
     */			
    $validTheme     = array(
    			       LOVEFILM_THEME_LIGHT, 
    			       LOVEFILM_THEME_DARK
    			      );
	/**
	 * Array to store the valid input
	 */    			     
    $validInput     = array();
	/**
	 * Has a user error occured?
	 */
    $error          = false;
   	/**
   	 * Validate the Width entered by the WP-Admin.
   	 * If the Width is fluid, then we check to ensure
   	 * that the Width is between the minimum and 
   	 * maximum range. If not, we set the Width to the
   	 * default value.
   	 */
    if($input['lovefilm_width_type'] != LOVEFILM_WIDTH_TYPE_FLUID)
    {
        if(is_null($input['lovefilm_width']) || 
           $input['lovefilm_width'] == '' ||
           $input['lovefilm_width'] < LOVEFILM_WIDTH_MIN || 
           $input['lovefilm_width'] > LOVEFILM_WIDTH_MAX)
        {
            $validInput['lovefilm_width'] = LOVEFILM_DEFAULT_WIDTH;
            $error = true;
        }
        else
        {
            $validInput['lovefilm_width'] = $input['lovefilm_width'];
        }
    }
	/**
	 * Validate the Context entered by the WP-Admin.
	 * If the Context is an invalid value, we set
	 * the Context to the default value.
	 */
    if(!in_array($input['context'], $validContext))
    {
        $validInput['context'] = LOVEFILM_DEFAULT_CONTEXT;
        $error = true;
    }
    else
    {
        $validInput['context'] = $input['context'];
    }
	/**
	 * Set the Widget Mode.
	 * As the Admin Panel currently lacks any other
	 * useage modes (for now), we set it to the
	 * default mode. 
	 */
    $validInput['type'] = LOVEFILM_DEFAULT_MODE;
	/**
	 * Validate the Theme selected by the WP-Admin.
	 * If the Theme selected is an invalid entry,
	 * we set the Theme to the default Theme.
	 */
    if(!in_array($input['theme'], $validTheme))
    {
        $validInput['theme'] = LOVEFILM_DEFAULT_THEME;
        $error = true;
    }
    else
    {
        $validInput['theme'] = $input['theme'];
    }
	/**
	 * Validate the Width Type selected by the WP-Admin.
	 * If the Width Type selected is an invalid entry,
	 * we set the Width Type to the default Width Type.
	 */
    if(!in_array($input['lovefilm_width_type'], $validWidthType))
    {
    	$validInput['lovefilm_width_type'] = LOVEFILM_DEFAULT_WIDTH_TYPE;
        $error = true;
    }
    else
    {
        $validInput['lovefilm_width_type'] = $input['lovefilm_width_type'];
    }
	/**
	 * We dont validate the affliate id, we simply set it
	 * to what ever value the WP-Admin entered.
	 */
 
    $validInput['lovefilm_aff'] = $input['lovefilm_aff'];
    update_option('lovefilm_aff_widget', $input['lovefilm_aff']);

    // Check if all validation has passed
    // If Context has changed
    // 1. Send a change of context request. 
    // 2. If successful: truncate the LoveFilm tables to ensure no 'out of context' titles are displayed.
    // 3. If fail: trigger WordPress settings error.

    if(!$error)
    {
    	// context switch has occured.
        $shiftResult = lovefilm_ws_change_context($validInput['context']);

        if($shiftResult === TRUE)
        {
        	update_option('lovefilm_context', $validInput['context']);
        }
    }
    
   //Adds the contextual widget Display links.
    if($input['lovefilm_contextual_display_article_link'] == "" || $input['lovefilm_contextual_display_article_link'] == null)
       {
           update_option('lovefilm_contextual_display_article_link', 1);
       }
     else {
           update_option('lovefilm_contextual_display_article_link', $input['lovefilm_contextual_display_article_link']);
          }
          
               
         //share love code overwirtting the promo code.
          update_option('lovefilm_earn_type' , $input['earn_type']);
          if(isset($input['lovefilm_share_love']))
          {
              update_option('lovefilm_share_love' , $input['lovefilm_share_love']);
          }
              
    return $validInput;
}

/*
 * Clears the currently cached Titles from the local
 * database. When a page is requested with the Widget
 * on it, and it has no titles in its local cache, this
 * will trigger the Widget to go fetch new titles. 
 */
function lovefilm_admin_clearDbCache()
{
	try {
    	$titles = lovefilm_ws_get_embedded_titles_ws();
		if(count($titles)>0) {
    		lovefilim_clearall_pagetiltes();
    		lovefilm_ws_set_embedded_titles_db($titles);
		}
	} 
	catch(Exception $e)
	{
		// WebService is not returing parsable results,
		// Try again later.
	}
}
    
   
/**
 * Flush all the entries form the page table cache
 * @global type $wpdb
 * @return true
 */
function lovefilim_clearall_pagetiltes()
{
	global $wpdb;
	$wpdb->query( $wpdb->prepare("DELETE FROM LFW_Page"));
	$wpdb->query( $wpdb->prepare("DELETE FROM LFW_PageAssignment"));
	$wpdb->query( $wpdb->prepare("DELETE FROM LFW_CatalogItem"));
	return true;
}