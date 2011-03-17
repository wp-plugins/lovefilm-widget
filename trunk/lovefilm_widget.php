<?php

require_once('lovefilm_catalogue.php');

class Lovefilm_Widget extends WP_Widget
{
    const SERVICE_SUCCESS = 0;
    const SERVICE_FAILURE = -1;

    public function Lovefilm_Widget()
    {
        $widget_ops = array('classname' => 'lovefilm_widget', 'description' => 'Displays the LOVEFiLM Widget');
        $control_ops = array('id_base' => 'lovefilm_widget');
        $this->WP_Widget('lovefilm_widget', 'LOVEFiLM Widget', $widget_ops, $control_ops);
    }

    public function widget($args, $instance)
    {
        $embed_status = Lovefilm_Widget::SERVICE_SUCCESS;
        $favourites = Lovefilm_Catalogue::Favourites($_SERVER['REQUEST_URI']);

        //lovefilm_ws_get_embedded_titles_db();
        $titlesPresent = lovefilm_ws_check_embedded_titles();

        lovefilm_ws_service_end_points();

        if(is_null($titlesPresent) || empty($titlesPresent))
        {
            // No titles for this page in the database.
            // Call webservice for titles and store them in the DB.
            try
            {
                $titles = lovefilm_ws_get_embedded_titles_ws();

                if(count($titles) == 0)
                    throw new LoveFilmWebServiceException('Failed to collect embedded titles');

                lovefilm_ws_set_embedded_titles_db($titles);
                $titles = lovefilm_ws_get_embedded_titles_db();
                $embed_status == Lovefilm_Widget::SERVICE_SUCCESS;
            }
            catch(Exception $e)
            {
            	_log($e);
                $embed_status = Lovefilm_Widget::SERVICE_FAILURE;
            }
        }
        else
        {
            $titles = lovefilm_ws_get_embedded_titles_db();
        }
        
        //_log("TITLES:\n".var_export($titles, true));

        
        $mrktMsg = lovefilm_ws_get_marketing_msg();
        $promoCode = lovefilm_ws_get_promo_code();
        
        if($embed_status == Lovefilm_Widget::SERVICE_SUCCESS)
        {
        	$widgetOpts = get_option('lovefilm-settings');
        	if($widgetOpts === FALSE)
        		$widgetOpts = array();
        		
            $widgetId   = get_option('lovefilm-uid');
        	require_once('lovefilm_widget_public.php');
        }
    }

    protected function getFilms()
    {
        
    }

    public function form($instance)
    {
        /* no-op */
    }

    public function update($new_instance, $old_instance)
    {
        /* no-op */
    }

}