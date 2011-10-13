<?php

/** -------------------------------------------------------------------
 *  LOVEFiIM Tinymce editor button */
function lf_addbuttons() {
    // Don't bother doing this stuff if the current user lacks permissions
    if (!current_user_can('edit_posts') && !current_user_can('edit_pages'))
        return;

    // Add only in Rich Editor mode
    if (get_user_option('rich_editing') == 'true') {
        add_filter("mce_external_plugins", "add_lf_tinymce_plugin");
        add_filter('mce_buttons', 'register_lf_button');
    }
}

function register_lf_button($buttons) {
    array_push($buttons, "separator", "lf_button");
    return $buttons;
}

// Load the TinyMCE plugin : editor_plugin.js (wp2.5)
function add_lf_tinymce_plugin($plugin_array) {
    $plugin_array['lf_button'] = plugins_url('js/lf_editor_plugin.js', (__FILE__));
    return $plugin_array;
}

// init process for button control
add_action('init', 'lf_addbuttons');

//---------------------


/**
 * This function displays the Lovefilm Quick link box on the post edit page. 
 */
function lovefilm_contextual_meta_custom() {
            add_meta_box('contextualdiv', __('LOVEFiLM quick links'), 'lovefilm_contextual_meta_box', 'post', 'advanced', 'high');
}

add_action('edit_form_advanced', 'lovefilm_contextual_meta_custom');
add_action('admin_head', 'lovefilm_contextual_styles');
/**
 * This is the Lovefilm Quick links css .
 */
function lovefilm_contextual_styles() {
    ?>
    <style type="text/css">
        #lf-contextual {
        }

        #lf-contextual #select {
            width:200px;
        }

        #advanced-sortables #lf-contextual #select {
            float:right;
            display:inline-block;
        }

        #normal-sortables #lf-contextual #select {
            float:right;
            display:inline-block;
        }

        #side-sortables #lf-contextual #select {
            margin: 10px auto;
        }

        #lf-contextual #select .film-selected {
            border:1px solid #ccc;
        }

        #lf-contextual #select .film-selected img {
            border:2px #dfdfdf solid;
            padding:2px;
        }

        #lf-contextual #select .film-selected .film-title {
            text-decoration: none;
        }

        #lf-contextual #select .film-selected .film-title h4 {
            margin-bottom: 0px;
            padding-bottom: 0px;
        }

        #lf-contextual #results table h4 {
            margin: 5px 0;
        }
        
        .display-link{
            margin: 5px;
        }
        
        .backdrop
            {
                    position:fixed;
                    top:0px;
                    left:0px;
                    background:#000;
                    z-index: 10;
                    width: 100%; height: 100%;
                    opacity: .80;
                    z-index: 999999;
                    filter:alpha(opacity=0);
                    display:none;
            }


            .box
            {
                position:absolute;
                top:5%;
                left:30%;
                width:500px;
                min-height:600px;
                background:#ffffff;
                z-index:9999999;
                padding:10px;
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                border-radius: 5px;
                -moz-box-shadow:0px 0px 5px #444444;
                -webkit-box-shadow:0px 0px 5px #444444;
                box-shadow:0px 0px 5px #444444;
                border:solid #B60C00 3px;
                display:none;

            }
            
            
            .close_wrap{
                float:right;
            }

            .close
            {
                float:right;
                margin-right:6px;
                padding: 5px;
                cursor:pointer;
            }
            
            #loading_popup
            {
                float:left;
                padding: 5px;
                margin-right:6px;
                cursor:pointer;
            }
            
            .popup_line{
                clear:both;
                border-bottom: 2px #444444 solid;
                margin-top: 12px;
                margin-bottom: 5px;
            }
            #popup-logo{
                float:left;
                padding-bottom: 5px;
            }
            #popup-close{
                
                color: #AE1227;
                
            }
            
            
            .display-link{
            margin: 5px;
            }
      
    </style>
    <?php
}
/**
 * This is the Lovefilm Quick links search form 
 * @global type $post
 * @global type $wpdb 
 */
function lovefilm_contextual_meta_box() {
    global $post, $wpdb;
   ?>
       
    
    <div id="lf-contextual">
        <?php if($post->ID > 0): ?>
        <div class="search-wrapper">
        <input type="text" autocomplete="off" id="SearchText" name="searchtext"> 
        <select name="searchmode" id="SearchMode" >
            <option value="film">Film</option>
            <option value="games">Games</option>
            <option value="tv">Tv</option>
        </select>
        <input type="hidden" id="lf_post_id" name="lf_post_id" value="<?php echo $post->ID; ?>" />
        <input type="submit" id="Submit" class="button-primary" value="Search">
        <span class="tooltip">?<span>
                <p>Enter the name and type of product to find and click 'Search'. Once a list of products has been found, click 'Add' on the required product to display it within the LOVEFiLM widget.</p>
                <p>Once selected, the product will be appear within the 'Selected title' box and should you wish, you can remove it from the LOVEFiLM widget by clicking 'Remove'.</p>
                <p>Use the 'Display article link' checkbox to choose whether to display the article link at the end of the post. This checkbox is ignored where the global LOVEFiLM setting is set to ON.</p>
                
           </span></span>
    </div>
        <?php else: ?>
        <p>Publish the post and search for quick links...</p>
        <?php endif; ?>
        <div id="select">
            <?php
            $query = "SELECT contextual_post_id FROM LFW_Contextual WHERE contextual_post_id = $post->ID";
            $exist = $wpdb->get_var($query);
            if ($exist == null || $exist == "") {
                
            } else {
                $query = "SELECT contextual_id, contextual_title_url, contextual_title, contextual_image, contextual_release_date, contextual_title, contextual_display_link FROM LFW_Contextual WHERE contextual_post_id = $post->ID";
                $exist = $wpdb->get_row($query);
                ?>  <h3>Selected title</h3>

                <div align="center" class="film-selected"><br />
                    <?php if ($exist->contextual_image == ""): ?>
                        <a href="<?php echo $exist->contextual_title_url; ?>" target="_blank"><img style="border:2px #dfdfdf solid; padding:2px;" src="<?php echo get_option('siteurl') . '/wp-content/plugins/lovefilm/img/default-image.gif'; ?>" /></a>
                    <?php else: ?>
                        <a href="<?php echo $exist->contextual_title_url; ?>" target="_blank"><img style="border:2px #dfdfdf solid; padding:2px;" src="<?php echo $exist->contextual_image; ?>" /></a>
                    <?php endif; ?>
                    <a href="<?php echo $exist->contextual_title_url; ?>" target="_blank" class="film-title"><h4><?php echo $exist->contextual_title; ?></h4></a>
                    <h4 style="margin-top: 3px;">
                    <?php if ($exist->contextual_release_date > 0) {
                        echo $exist->contextual_release_date;
                    } else {
                        echo "";
                    } ?></h4>
                    
        <?php
                $checked = null; 
                if($exist->contextual_display_link == 1) 
                 {
                    $checked = 'checked';
                 } ?>
        <input type ="checkbox" class="display-link" name="display-link" <?php echo $checked; ?> />&nbsp;<strong>Display article link</strong>
                     <br />
                    <input type="hidden" class="lf_remove_id" value="<?php echo $exist->contextual_id ?>" />
                    <input type="submit" id="lf_remove" class="button lf_remove" value="Remove"/><br />
                    <br />
                </div>
        <?php
    }
    ?>

        </div>	

    <?php $progress_logo = plugins_url('/img/load.gif',__FILE__); ?>
        <div id="loading">Please wait...<img src="<?php echo $progress_logo; ?>" /></div>


        <div id="results" style="" align="left"></div>
        <div style="clear:both"></div>
    </div>      
    

    <?php
}

add_action('admin_head', 'lovefilm_contextual_ajax');

/**
 * All the Ajax and the jquery functionality goes here
 */
function lovefilm_contextual_ajax() {
    ?>
    <script language="javascript">
        jQuery(document).ready(function() {

        
            jQuery('#select').css('border', "");
            jQuery('#loading').css('display','none');
            jQuery('#loading_popup').css('display','none');


            /*--- This is the Pagination code through the anchor tag. */ 

            jQuery('.pagination').live('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
           
           
                var prep = jQuery(this).attr('href').split("?");
                var vars = prep[1].split("&");
                var pairs = new Array();
                for (var i = 0; i < vars.length; i++) {
                    var r = vars[i].split("=");
                    pairs[r[0]] = r[1];
                }
                jQuery('#loading').css('display','inline');                              
                jQuery('#loading_popup').css('display','inline');

                var data = {
                    action: 'search_action',
                    searchmode: pairs['mode'],
                    searchtext: pairs['query'],
                    searchindex: pairs['index'],
                    lf_post_id: pairs['lf_post_id'],
                    page: pairs['page']
                };

                jQuery.post(ajaxurl, data, function(response) {
                    
                    jQuery('#results').css('display','inline');
                        jQuery(e.target).closest('#results1').html(unescape(response));
                        jQuery(e.target).closest('#results').html(unescape(response));
                        jQuery('#results').css('display','block');                   
                        jQuery('#loading').css('display','none');                              
			jQuery('#loading_popup').css('display','none');
                });
                return false;
            });

            /*--- This is to use the key press when the uesr hit enter from the search text box then it will produce the jQuery('#results').. 

                jQuery('#SearchText').keypress(function (e){
                    if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) 
                    {
                        jQuery('#Submit').click();
                        return false;
                    }

                });*/

            /*--- This is to add the selected search from . */

            jQuery('.add_data').live('click', function(e) {
                jQuery('#loading').css('display','inline');                              
                jQuery('#loading_popup').css('display','inline');
                e.stopPropagation();
                e.preventDefault();
                var id = jQuery(this).attr('id');
                            
        										
                var add_data = {
                    action: 'search_action_add',
                    lf_image: (jQuery('#form_'+id+' .lf_image').val()),
                    lf_title_url: (jQuery('#form_'+id+' .lf_title_url').val()),
                    lf_title: (jQuery('#form_'+id+' .lf_title').val()),
                    lf_release_date: (jQuery('#form_'+id+' .lf_release_date').val()),
                    lf_director: (jQuery('#form_'+id+' .lf_director').val()),
                    lf_format: (jQuery('#form_'+id+' .lf_format').val()),
                    lf_synopsis: (jQuery('#form_'+id+' .lf_synopsis').val()),
                    lf_rating: (jQuery('#form_'+id+' .lf_rating').val()),
                    lf_post_id:(jQuery('#lf_post_id').val()),
                    lf_mode:(jQuery('.lf_mode').val())
                };
        					
                jQuery('#select').html('');
                
                jQuery.post(ajaxurl, add_data, function(response) {
                    jQuery('.selected_remove').css('border', '1px solid #DFDFDF');
                    jQuery('#select').html(unescape(response));	
                    jQuery('#loading').css('display','none');                              
                    jQuery('#loading_popup').css('display','none');
                    jQuery('.popup_closeme').trigger("click");
                });

                return false;

            });
                
            /* The search button submit click event */

            jQuery('#Submit').live('click', function() {

                jQuery('#results').css('display','none');
                jQuery('#results').html('');
                jQuery('#loading').css('display','inline');                              
                jQuery('#loading_popup').css('display','inline');

                var data = {
                    action: 'search_action',
                    searchmode: (jQuery('#SearchMode').val()),
                    searchtext: escape(jQuery('#SearchText').val()),
                    lf_post_id:(jQuery('#lf_post_id').val())
                };

                jQuery.post(ajaxurl, data, function(response) {
                    jQuery('#results').css('display','block');
                    jQuery('#results').html(unescape(response));	
                    jQuery('#loading').css('display','none');                              
                    jQuery('#loading_popup').css('display','none');
                    
                });

                return false;
            });
            
                  /* The search button submit click event */

            jQuery('#Submit1').live('click', function() {
    
                var data = {
                    action: 'search_action',
                    searchmode: (jQuery('#SearchMode1').val()),
                    searchtext: escape(jQuery('#SearchText1').val()),
                    lf_post_id:(jQuery('#lf_post_id1').val())
                };
                jQuery('#loading_popup').css('display','inline');  

                jQuery.post(ajaxurl, data, function(response) {
                    jQuery('#results').css('display','block');
                    //console.log('hi');
                    jQuery('#results1').html(unescape(response));	
                    jQuery('#loading').css('display','none');                              
		    jQuery('#loading_popup').css('display','none');
                    
                });

                return false;
            });
                
            /* The search button submit keypress event */
            jQuery('#SearchText').live('keypress', function(e) {
                if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)){
                    jQuery('#results').css('display','none');
                    jQuery('#results').html('');
                    jQuery('#loading').css('display','inline');                              
                jQuery('#loading_popup').css('display','inline');

                    var data = {
                        action: 'search_action',
                        searchmode: (jQuery('#SearchMode').val()),
                        searchtext: escape(jQuery('#SearchText').val()),
                        lf_post_id:(jQuery('#lf_post_id').val())
                    };

                    jQuery.post(ajaxurl, data, function(response) {
                        jQuery('#results').css('display','block');
                        jQuery('#results').html(unescape(response));	
                        jQuery('#loading').css('display','none');                              
                        jQuery('#loading_popup').css('display','none');
                    });
                    return false;
                }
                   
            });


            jQuery('.lf_remove').live('click', function(e) {

                jQuery('#loading').css('display','inline');
                jQuery('#loading_popup').css('display','inline');
                var remove_data = {
                    action: 'remove_action',
                    lf_remove_id:(jQuery('.lf_remove_id').val())
                }
                jQuery.post(ajaxurl, remove_data, function(response) {
                    jQuery('#select').css('border','none');	
                    jQuery('#select').html('');	
                    jQuery('#loading').css('display','none');                              
                    jQuery('#loading_popup').css('display','none');
                });

                return false; 
            });
            
            /* Display link */
            
        jQuery('.display-link').live('click', function(e) {
        if(jQuery('input[name=display-link]').is(':checked')== true)
        {
            var checked = 'true';
    	}
        else
            {
                checked = 'false';
            }
            jQuery('#loading').css('display','inline');                              
            jQuery('#loading_popup').css('display','inline');
        
        var display_link = {
    	    action: 'display_article_link',
            lf_post_id:(jQuery('#lf_post_id').val()),
            display_link: checked
        }
        jQuery.post(ajaxurl, display_link, function(response) {
            console.log(response);
            jQuery('#loading').css('display','none');                              
            jQuery('#loading_popup').css('display','none');
        });
            
        });
            
            
            
            
            /* POP UP BOX CODE BEGINING */
                jQuery('.lightbox').click(function(){
                    jQuery('.backdrop').fadeTo(300,0.5);
                    jQuery('.box').fadeIn(300);
                });

            /* POP UP BOX CODE ENDING */

               });
    </script>
    <?php
}

add_action('wp_ajax_search_action', 'lovefilm_contextual_search_action_callback');
add_action('wp_ajax_search_action_add', 'lovefilm_contextual_search_action_insup');
add_action('wp_ajax_remove_action', 'lovefilm_contextual_remove_action');
add_action('wp_ajax_display_article_link','lovefilm_contextual_display_article_link');


	

/**
 * This function will update the display article links weather to display them or not on the post page.
  @global type $wpdb
  @global type $post

 */
function lovefilm_contextual_display_article_link() {

    global $wpdb, $post;


    $lf_post_id = $_POST['lf_post_id'];

    $display_link = $_POST['display_link'];
    if ($display_link == 'true') {

        $display_link = 1;
    } else {

        $display_link = 0;
    }
 

    $display = array('contextual_display_link' => $display_link);
    
    var_dump($display);

    $wpdb->update("LFW_Contextual", $display, array('contextual_post_id' => $lf_post_id), array('%d'), array('%d'));


    die();
}

/**
 * Removes the selected title.
 * @global type $wpdb 
 */
function lovefilm_contextual_remove_action() {
    global $wpdb;
    $lf_remove_id = $_POST['lf_remove_id'];
    $wpdb->query("DELETE FROM LFW_Contextual WHERE contextual_id = $lf_remove_id");
    die();
}

/**
 * inserts or updates the selected title.
 * @global type $wpdb 
 */
function lovefilm_contextual_search_action_insup() {
    global $wpdb;
    
    $lf_title = stripslashes($_POST['lf_title']);
    $lf_title_url = stripslashes($_POST['lf_title_url']);
    $lf_release_date = $_POST['lf_release_date'];
    $lf_director = stripslashes($_POST['lf_director']);
    $lf_format = $_POST['lf_format'];
    $lf_image = $_POST['lf_image'];
    $lf_synopsis = stripslashes($_POST['lf_synopsis']);
    $lf_rating = $_POST['lf_rating'];
    $lf_post_id = $_POST['lf_post_id'];
    $lf_mode = $_POST['lf_mode'];


    $data_insert = array('contextual_post_id' => $lf_post_id,
        'contextual_title' => $lf_title,
        'contextual_title_url' => $lf_title_url,
        'contextual_release_date' => $lf_release_date,
        'contextual_director' => $lf_director,
        'contextual_format' => $lf_format,
        'contextual_mode' => $lf_mode,
        'contextual_image' => $lf_image,
        'contextual_synopsis' => $lf_synopsis,
        'contextual_rating' => $lf_rating,
        'contextual_display_link' => 0
    );
    
    $query = "SELECT contextual_display_link FROM LFW_Contextual WHERE contextual_post_id = $lf_post_id";
    $contextual_display_link = $wpdb->get_var($query);

    $data_update = array(
        'contextual_title' => $lf_title,
        'contextual_title_url' => $lf_title_url,
        'contextual_release_date' => $lf_release_date,
        'contextual_director' => $lf_director,
        'contextual_format' => $lf_format,
        'contextual_mode' => $lf_mode,
        'contextual_image' => $lf_image,
        'contextual_synopsis' => $lf_synopsis,
        'contextual_rating' => $lf_rating,
        'contextual_display_link' => $contextual_display_link
    );


    $query = "SELECT contextual_post_id FROM LFW_Contextual WHERE contextual_post_id = $lf_post_id";
    $exist = $wpdb->get_var($query);
    if ($exist == null || $exist == "") {
        $wpdb->insert("LFW_Contextual", $data_insert, array('%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%f'));
    } else {
        $wpdb->update("LFW_Contextual", $data_update, array('contextual_post_id' => $lf_post_id), array('%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%f'), array('%d'));
    }

    $query = "SELECT contextual_id FROM LFW_Contextual WHERE contextual_post_id = $lf_post_id";
    $exist = $wpdb->get_var($query);
    ?>
    <h3>Selected title</h3>
    <div id="selection"  align="center" style="border:1px solid #ccc"><br />
        <?php if ($lf_image == ""): ?>
        <a href="<?php echo $lf_title_url; ?>" target="_blank"><img style="border:2px #dfdfdf solid; padding:2px;" src="<?php echo plugins_url('/img/default-image.gif',__FILE__); ?>" /></a>
    <?php else: ?>
            <a href="<?php echo $lf_title_url; ?>" target="_blank"><img style="border:2px #dfdfdf solid; padding:2px;" src="<?php echo $lf_image; ?>" /></a>
    <?php endif; ?>
        <a class="menu-top" id="selection_url" href="<?php echo $lf_title_url; ?>" target="_blank" style="text-decoration: none;"><h4 style="margin-bottom: 0px; padding-bottom: 0px;"><?php echo $lf_title; ?></h4></a>
        <h4 style="margin-top: 3px;">
                    <?php if ($lf_release_date > 0) {
                        echo $lf_release_date;
                    } else {
                        echo "";
                    } ?></h4>
        <?php
        
//        $query = "SELECT contextual_display_link FROM LFW_Contextual WHERE contextual_post_id = $lf_post_id";
//        $contextual_display_link = $wpdb->get_var($query);
        
        $checked = null; 
                if($contextual_display_link == 1) 
                 {
                    $checked = 'checked';
                 } ?>
        
        
        <input type ="checkbox" class="display-link" name="display-link"  <?php echo $checked; ?> />&nbsp;<strong>Display article link</strong>
        <br />
        <form id="form_remove" action="" method="post">
            <input type="hidden" class="lf_remove_id" value="<?php echo $exist ?>" />    
            <input type="submit" id="lf_remove" class="button lf_remove" value="Remove"/><br />
        </form>
        <br />
    </div>
    <?php
    die();
}

/**
 * Search results according to the search mode.
 */
function lovefilm_contextual_search_action_callback() {
    
    $searchtext = $_POST['searchtext'];
    $searchmode = $_POST['searchmode'];
    $searchindex = (isset($_POST['searchindex']))?($_POST['searchindex']): null;
    $page = (isset($_POST['page']))?($_POST['page']):null;
    $lf_post_id = (isset($_POST['lf_post_id']))?($_POST['lf_post_id']):null;
    $api_endpoint = LOVEFILM_WS_API_URL.'/search?mode=' . $searchmode . '&index=' . $searchindex . '&query=' . $searchtext;
    
    error_log($lf_post_id);
    $results = @simplexml_load_file($api_endpoint);
    if (!($results)) 
    {
       echo "<p>Unfortunately your search results could not be found. Please try searching for a different title.</p>";
    }
    else 
    { 
    
    $totalresult = $results->totalresults;
    echo 'Total search results: <strong>' . $totalresult . '</strong>';

    echo '<table cellspacing="5" cellpadding="5">';
    echo '<form></form>';
    foreach ($results->film as $film) {
        if ($film->image == "") {
            echo '<tr><td width="20%"><a href=" ' . $film->title_url . ' " rel="nofollow" target="_blank" alt="Title url" style="text-decoration:none;"><img style="border:1px #dfdfdf solid; padding:2px;" src="' . plugins_url('/img/default-image.gif',__FILE__) . '" /></a></td>';
        } else {
            echo '<tr><td width="20%"><a href=" ' . $film->title_url . ' " rel="nofollow" target="_blank" alt="Title url" style="text-decoration:none;"><img width="80" src="' . $film->image . '" alt="No Image" style="border:1px #dfdfdf solid; padding:2px;"/></a></td>';
        }
        echo '<td width="70%" valign="top"><a style="text-decoration: none;" href=" ' . $film->title_url . ' " rel="nofollow" target="_blank" alt="Title url"><h4>' . $film->title . '</a>  ' . (!empty($film->release_date) ? ('(' . $film->release_date . ')') : '') . '</h4>';
        echo '<strong>Director:</strong> ' . $film->director;
        echo '<br /><strong>Studio:</strong> ' . $film->studio;
        echo '<br /><strong>Starring:</strong> ';
        $i = 0;
        foreach ($film->actors as $actor) {
            if ($i < 2) {
                echo $actor . ', ';
                $i++;
            } else {
                echo '...';
                break;
            }
        }
        echo '';
        echo '<br/><strong>Format:</strong> ' . $film->format . '</td>';
        echo '<td width="10%">';
        echo '<form id="form_' . $film->id . '" action="" method="post">';
        echo '<input type="hidden" name="lf_mode[$film->id]" class="lf_mode" value="' . $searchmode . '" />';
        echo '<input type="hidden" name="lf_image[$film->id]" class="lf_image" value="' . $film->image . '" />';
        echo '<input type="hidden" name="lf_title_url" class="lf_title_url" value="' . $film->title_url . '" />';
        echo '<input type="hidden" name="lf_title" class="lf_title" id="' . $film->id . 'lf_title" value="' . htmlentities($film->title) . '" />';
        echo '<input type="hidden" name="lf_release_date" id="' . $film->id . 'lf_release_date" class="lf_release_date" value="' . $film->release_date . '" />';
        echo '<input type="hidden" name="lf_director" id="' . $film->id . 'lf_director" class="lf_director" value="' . htmlentities($film->director) . '" />';
        echo '<input type="hidden" name="lf_format" id="' . $film->id . 'lf_format" class="lf_format" value="' . $film->format . '" />';
        echo '<input type="hidden" name="lf_synopsis" id="' . $film->id . 'lf_synopsis" class="lf_synopsis" value="' . htmlentities($film->synopsis) . '" />';
        echo '<input type="hidden" name="lf_rating" id="' . $film->id . 'lf_rating" class="lf_rating" value="' . $film->rating . '" />';
        echo '<input type="hidden" name="lf_post_id" id="lf_post_id" value="' . $lf_post_id . '" />';
        echo '<input type="submit" id="' . $film->id . '" class="add_data button" value="Add" class="button"><a style="display:none" class="popup_closeme"></a>';
        echo '</form>';
        echo '</td></tr>';
    }

    foreach ($results->tv as $tv) {
        if ($tv->image == "") {
            echo '<tr><td width="20%"><a href=" ' . $tv->title_url . ' " rel="nofollow" target="_blank" alt="Title url" style="text-decoration:none;"><img style="border:1px #dfdfdf solid; padding:2px;" src="' . plugins_url('/img/default-image.gif',__FILE__) . '" /></a></td>';
        } else {
            echo '<tr><td width="20%"><a href=" ' . $tv->title_url . ' " rel="nofollow" target="_blank" alt="Title url" style="text-decoration:none;"><img width="80" src="' . $tv->image . '" alt="No Image" style="border:1px #dfdfdf solid; padding:2px;"/></a></td>';
        }
        echo '<td width="70%"><a href=" ' . $tv->title_url . ' " rel="nofollow" target="_blank" alt="Title url"><h4>' . $tv->title . '</a> ' . (!empty($tv->release_date) ? ('(' . $tv->release_date . ')') : '') . '</h4>';
        echo '<strong>Director:</strong> ' . $tv->director;
        echo '<br /><strong>Studio:</strong> ' . $tv->studio;
        echo '<br /><strong>Starring:</strong> ';
        $i = 0;
        foreach ($tv->actors as $actor) {
            if ($i < 2) {
                echo $actor . ', ';
                $i++;
            } else {
                echo '...';
                break;
            }
        }
        echo '';
        echo '<br/><strong>Format:</strong> ' . $tv->format . '</td>';
        echo '<td width="10%">';
        echo '<form id="form_' . $tv->id . '" action="" method="post">';
        echo '<input type="hidden" name="" class="lf_mode" value="' . $searchmode . '" />';
        echo '<input type="hidden" name="lf_image[$film->id]" class="lf_image" value="' . $tv->image . '" />';
        echo '<input type="hidden" name="lf_title_url" class="lf_title_url" value="' . $tv->title_url . '" />';
        echo '<input type="hidden" name="lf_title" class="lf_title" id="' . $tv->id . 'lf_title" value="' . htmlentities($tv->title) . '" />';
        echo '<input type="hidden" name="lf_release_date" id="' . $tv->id . 'lf_release_date" class="lf_release_date" value="' . $tv->release_date . '" />';
        echo '<input type="hidden" name="lf_director" id="' . $tv->id . 'lf_director" class="lf_director" value="' . htmlentities($tv->director) . '" />';
        echo '<input type="hidden" name="lf_format" id="' . $tv->id . 'lf_format" class="lf_format" value="' . $tv->format . '" />';
        echo '<input type="hidden" name="lf_synopsis" id="' . $tv->id . 'lf_synopsis" class="lf_synopsis" value="' . htmlentities($tv->synopsis) . '" />';
        echo '<input type="hidden" name="lf_rating" id="' . $tv->id . 'lf_rating" class="lf_rating" value="' . $tv->rating . '" />';
        echo '<input type="hidden" name="lf_post_id" id="lf_post_id" value="' . $lf_post_id . '" />';
        echo '<input type="submit" id="' . $tv->id . '" class="add_data" value="Add" class="button"><a style="display:none" class="popup_closeme"></a>';
        echo '</form>';
        echo '</td></tr>';
    }

    foreach ($results->games as $games) {
        if ($games->image == "") {
            echo '<tr><td width="20%"><a href=" ' . $games->title_url . ' " rel="nofollow" target="_blank" alt="Title url" style="text-decoration:none;"><img style="border:1px #dfdfdf solid; padding:2px;" src="' . plugins_url('/img/default-image.gif',__FILE__) . '" /></a></td>';
        } else {
            echo '<tr><td width="20%"><a href=" ' . $games->title_url . ' " rel="nofollow" target="_blank" alt="Title url" style="text-decoration:none;"><img width="80" src="' . $games->image . '" alt="No Image" style="border:1px #dfdfdf solid; padding:2px;"/></a></td>';
        }
        echo '<td width="70%"><a href=" ' . $games->title_url . ' " rel="nofollow" target="_blank" alt="Title url" style="text-decoration:none;"><h4>' . $games->title . '</a> ' . (!empty($games->release_date) ? ('(' . $games->release_date . ')') : '') . '</h4>';
        echo '<br /><strong>Developer:</strong> ' . $games->developer;
        echo '<br /><strong>Format:</strong> ' . $games->format;
        echo '</td>';
        echo '<td width="10%">';
        echo '<form id="form_' . $games->id . '" action="" method="post">';
        echo '<input type="hidden" name="" class="lf_mode" value="' . $searchmode . '" />';
        echo '<input type="hidden" name="lf_image[$film->id]" class="lf_image" value="' . $games->image . '" />';
        echo '<input type="hidden" name="lf_title_url" class="lf_title_url" value="' . $games->title_url . '" />';
        echo '<input type="hidden" name="lf_title" class="lf_title" id="' . $games->id . 'lf_title" value="' . htmlentities($games->title) . '" />';
        echo '<input type="hidden" name="lf_release_date" id="' . $games->id . 'lf_release_date" class="lf_release_date" value="' . date('Y', strtotime($games->release_date)) . '" />';
        echo '<input type="hidden" name="lf_director" id="' . $games->id . 'lf_director" class="lf_director" value="' . htmlentities($games->developer) . '" />';
        echo '<input type="hidden" name="lf_format" id="' . $games->id . 'lf_format" class="lf_format" value="' . $games->format . '" />';
        echo '<input type="hidden" name="lf_synopsis" id="' . $games->id . 'lf_synopsis" class="lf_synopsis" value="' . htmlentities($games->synopsis) . '" />';
        echo '<input type="hidden" name="lf_rating" id="' . $games->id . 'lf_rating" class="lf_rating" value="' . $games->rating . '" />';
        echo '<input type="hidden" name="lf_post_id" id="lf_post_id" value="' . $lf_post_id . '" />';
        echo '<input type="submit" id="' . $games->id . '" class="add_data button" value="Add" class="button"><a style="display:none" class="popup_closeme"></a>';
        echo '</form>';
        echo '</td></tr>';
    }
        echo '</table>';
        
        lovefilm_contextual_pagination($totalresult, $searchtext, $searchmode, $lf_post_id, $searchindex, $page );
    }
    die();
}
/**
 * The Pagination that deals with each search jQuery('#results').
 * @param type $totalresult
 * @param type $query
 * @param type $mode
 * @param type $index
 * @param type $page 
 */
function lovefilm_contextual_pagination($totalresult, $query, $mode, $lf_post_id, $index = 1, $page = 1 ) {
    
    ?><br /><div style="clear: both; text-decoration: none;" align="center"> <?php
    
    $page = $page;

    $cur_page = $page;
    if ($page == 0)
        $page = 1;
    $page -= 1;
    $per_page = 5;
    $previous_btn = true;
    $next_btn = true;
    $first_btn = true;
    $last_btn = true;
    $start = $page * $per_page;

    $no_of_paginations = ceil($totalresult / $per_page);

    if ($cur_page >= 7) {
        $start_loop = $cur_page - 3;
        if ($no_of_paginations > $cur_page + 3)
            $end_loop = $cur_page + 3;
        else if ($cur_page <= $no_of_paginations && $cur_page > $no_of_paginations - 6) {
            $start_loop = $no_of_paginations - 6;
            $end_loop = $no_of_paginations;
        } else {
            $end_loop = $no_of_paginations;
        }
    } else {
        $start_loop = 1;
        if ($no_of_paginations > 7)
            $end_loop = 7;
        else
            $end_loop = $no_of_paginations;
    }


    // FOR ENABLING THE FIRST BUTTON
    if ($first_btn && $cur_page > 1) {
        ?><a style="text-decoration: none; padding:3px; border:1px solid #ccc;line-height:30px;" href="?query=<?php echo $query ?>&mode=<?php echo $mode ?>&index=1&lf_post_id=<?php echo $lf_post_id ?>" class="pagination">First</a>     <?php
    } else if ($first_btn) {
        //disable first button
    }

    // FOR ENABLING THE PREVIOUS BUTTON
    if ($previous_btn && $cur_page > 1) {
        $pre = $cur_page - 1;
        $index = $index - 5;
        if ($index <= 0) {
            $index = 1;
        }
        ?><a style="text-decoration: none; padding:3px; border:1px solid #ccc;line-height:30px;" href="?query=<?php echo $query ?>&mode=<?php echo $mode ?>&index=<?php echo $index; ?>&page=<?php echo $page ?>&lf_post_id=<?php echo $lf_post_id ?>" class="pagination">Previous</a> <?php
    } else if ($previous_btn) {
        //DISABLE PREVIOUS BUTTON
    }
    for ($i = $start_loop; $i <= $end_loop; $i++) {

        $index = $i * ($per_page);
        $index = $index - 5;

        $index++;

        if ($index == 0 && $i == 1) {
            $index = 1;
        }

        if ($cur_page == "") {
            $cur_page = 1;
        }
        if ($cur_page == $i) {
            ?> <a style="text-decoration: none; padding:3px; border:3px solid #ccc;line-height:30px;" href="?query=<?php echo $query ?>&mode=<?php echo $mode ?>&index=<?php echo $index ?>&page=<?php echo $i ?>&lf_post_id="<?php echo $lf_post_id ?> class="pagination"><?php echo $i ?></a> <?php
        } else {
            ?> <a style="text-decoration: none; padding:3px; border:1px solid #ccc;line-height:30px;" href="?query=<?php echo $query ?>&mode=<?php echo $mode ?>&index=<?php echo $index ?>&page=<?php echo $i ?>&lf_post_id="<?php echo $lf_post_id ?> class="pagination"><?php echo $i ?></a> <?php
        }
    }

    // TO ENABLE THE NEXT BUTTON
    if ($next_btn && $cur_page < $no_of_paginations) {
        $nex = $cur_page + 1;

        $next = ($nex - 1) * 5;
        $next++;
        if ($next <= 0) {
            $next = 1;
        }

        if ($next == 1 && $nex == 1) {
            $next = 6;
            $nex = 2;
        }
        ?> <a style="text-decoration: none; padding:3px; border:1px solid #ccc;line-height:30px;" href="?query=<?php echo $query ?>&mode=<?php echo $mode ?>&index=<?php echo $next ?>&page=<?php echo $nex ?>&lf_post_id="<?php echo $lf_post_id ?> class="pagination">Next</a> <?php
    } else if ($next_btn) {
        // disable next button
    }

    // TO ENABLE THE END BUTTON
    if ($last_btn && $cur_page < $no_of_paginations) {
        $end_page = ($no_of_paginations - 1) * 5;
        $end_page++;
        ?> <a style="text-decoration: none; padding:3px; border:1px solid #ccc;line-height:30px;" href="?query=<?php echo $query ?>&mode=<?php echo $mode ?>&index=<?php echo $end_page ?>&page=<?php echo $no_of_paginations ?>&lf_post_id="<?php echo $lf_post_id ?> class="pagination">Last</a> <?php
    } else if ($last_btn) {
        //disable last button
    }
    ?> 

    </div> 
    <div align="right"><span>Page <strong><?php echo $cur_page ?></strong> of <strong> <?php echo $no_of_paginations ?></strong></span></div>
    <?php
}
/**
 * Adds an additional column to the home page. 
 * @param array $columns
 * @return type 
 */
function lovefilm_contextual_post_column($columns) {
    $columns["lovefilm_post_column"] = __('LOVEFiLM Title', 'lovefilm');
    return $columns;
}

add_filter('manage_posts_columns', 'lovefilm_contextual_post_column');
/**
 * Show the lovefilm title on the list of post page.
 * @global type $wpdb
 * @global type $post
 * @param type $column_name 
 */
function lovefilm_contextual_post_show_selected($column_name) {
    global $wpdb, $post;
    $id = $post->ID;
    if ($column_name === 'lovefilm_post_column') {
        $query = "SELECT contextual_mode,contextual_image, contextual_title_url, contextual_title FROM LFW_Contextual WHERE contextual_post_id = $id";
        $show = $wpdb->get_row($query);
        if(!empty($show))
        {
            if ($show->contextual_image != "") {
                echo '<a href="' . $show->contextual_title_url . '" target="_blank" title="' . $show->contextual_title . '" ><img style="border:1px #dfdfdf solid; padding:2px;" src="' . $show->contextual_image . '" height="114px" width="80px" /></a>';
            } else {
                echo '<a href="' . $show->contextual_title_url . '" target="_blank" title="' . $show->contextual_title . '" ><img style="border:1px #dfdfdf solid; padding:2px;" height="114px" width="80px" src="' . plugins_url('/img/default-image.gif',__FILE__) . '" /></a>';
            }
        }
    }
}

add_action('manage_posts_custom_column', 'lovefilm_contextual_post_show_selected'); #

    add_filter('the_content', 'lovefilm_contextual_footer_link');

/**
 * Displays the link on the footer of the post page with the selected title.
 * @global type $wpdb
 * @global type $post
 * @param type $content_to_filter
 * @return type 
 */
function lovefilm_contextual_footer_link($content_to_filter) {
    global $wpdb, $post;
    $query = "SELECT contextual_title, contextual_title_url, contextual_mode, contextual_display_link FROM LFW_Contextual WHERE contextual_post_id = $post->ID";
    $article_links = null;
    $exist = $wpdb->get_row($query);
    if($exist == null)
    {
        $contextual_link = 0;
    }
    else
    {
        $contextual_link = $exist->contextual_display_link;
    }
        
    if(get_option("lovefilm_contextual_display_article_link") == 1 || $contextual_link == '1')
    {
    if (!is_home()) {
        if ($exist != null || $exist != "") {
            $mode = $exist->contextual_mode;
            if ($mode == 'film' || $mode == 'tv') {
                $mode = 'watch';
            } else {
                $mode = 'rent';
            }
            $article_links = '<div id="featured-article-title"><a href="' . $exist->contextual_title_url . '" style="text-decoration:none;" target="_blank">';
            $article_links .= '<i>Sign up now and ' . $mode . ' <strong>' . $exist->contextual_title . '</strong> for FREE with your LOVEFiLM trial >> </i></a></div>';
        } else {
            $article_links = null;
        }
     }
    }
    return $content_to_filter . $article_links;
}

/**
 * Function for implement the pop up box.
 * @global type $post
 * @global type $wpdb 
 */
function lovefilm_light_box_popup() {
    global $wpdb, $post;
    if($post->ID > 0)
    {
    ?>
    <a href="#" class="lightbox"></a>
    <div class="backdrop"></div>
     <?php $progress_logo = plugins_url('/img/load.gif',__FILE__); ?>
        
    <div class="box"><div id="popup-logo"><img src="<?php echo plugins_url('/img/logo.gif',__FILE__); ?>" /> </div><div class="close_wrap"><div id="loading_popup">Please wait...<img src="<?php echo $progress_logo; ?>" /></div><div id="popup-close" class="close button">Close&nbsp;<img src="<?php echo plugins_url('/img/close.jpg',__FILE__); ?>" /></div></div> 
        <div class="popup_line"></div>
     <div class="search-wrapper1">
        <input type="text" autocomplete="off" id="SearchText1" name="searchtext"> 
        <select name="searchmode" id="SearchMode1" >
            <option value="film">Film</option>
            <option value="games">Games</option>
            <option value="tv">Tv</option>
        </select>
        <input type="hidden" id="lf_post_id" name="lf_post_id" value="<?php echo the_ID(); ?>" />
        <input type="submit" id="Submit1" class="button-primary" value="Search">
        <span class="tooltip">?<span>
                <p>Enter the name and type of product to find and click 'Search'. Once a list of products has been found, click 'Add' on the required product to display it within the LOVEFiLM widget.</p>
                <p>Once selected, the product will appear within the 'Selected title' box and should you wish, you can remove it from the LOVEFiLM widget by clicking 'Remove'.</p>
                <p> A link will also be created between the product and any highlighted text. To remove this link use the WordPress Unlink button.</p>
                <p> </p>
         </span></span>
     
    </div>
        <div id="results1" style="" align="left"></div>
        <div style="clear:both"></div>
        </div>
    <?php
    }
}

add_action('admin_footer', 'lovefilm_light_box_popup');