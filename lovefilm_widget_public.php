<script type="text/javascript">
LFWidget.remote_addr = '<?php echo LOVEFILM_WS_URL; ?>';
LFWidget.domain = '<?php echo get_option('lovefilm_domain') ?>';
LFWidget.affid = '<?php echo (array_key_exists('lovefilm_aff', $widgetOpts))?$widgetOpts['lovefilm_aff']:''; ?>';
LFWidget.theme = '<?php if(isset($widgetOpts['theme']) && !empty($widgetOpts['theme'])) echo $widgetOpts['theme']; ?>';
LFWidget.lfid = '<?php echo $widgetId; ?>';
LFWidget.promoCode = <?php echo (is_null($promoCode))?'null':'"'.$promoCode.'"'; ?>
LFWidget.context = <?php echo (isset($widgetOpts['context']) && !empty($widgetOpts['context']))?$widgetOpts['context']:'"'.LOVEFILM_DEFAULT_CONTEXT.'"'; ?>
</script>
<div id="lf-widget" <?php if(isset($widgetOpts['lovefilm_width']) && $widgetOpts != 'fluid') echo "style=\"width:{$widgetOpts['lovefilm_width']}px\""; ?>>
    <div id="lf-wrapped" <?php if(isset($widgetOpts['theme']) && !empty($widgetOpts['theme'])) echo " class=\"{$widgetOpts['theme']}\""; ?>>
        <div class="header">
            <h3><a href="http://www.lovefilm.com" target="_blank">LOVEFiLM</a></h3>
            <p><?php if(!is_null($mrktMsg)): ?><a id="lf-message" href="<?php echo $mrktMsg->href; ?>" target="_blank"><?php echo $mrktMsg->anchor_text; ?></a><?php endif; ?></p>
        </div>

        <div class="content accordion">
            
            <div class="heading"><span class="arrow open">Latest Releases</span></div>
            <div class="frame">
                <iframe id="latest-releases" src="" scrolling="no" frameBorder="0"></iframe>
            </div>
            
            <div class="heading"><span class="arrow">Most Popular</span></div>
            <div class="frame">
                <iframe id="most-popular" src="" scrolling="no" frameBorder="0"></iframe>
            </div>
            <?php if($embed_status == Lovefilm_Widget::SERVICE_SUCCESS) : ?>
            <div class="heading"><span class="arrow">LOVEFiLM Favourites</span></div>
            <div class="frame" id="favourites">
            	<ul id="constraint">
                    <?php foreach($titles as $i => $item): ?>

                    <li class="movie<?php echo ($i == count($titles) - 1)? ' last' : '' ?>" id="movie-<?php echo $i?>">
                    <a href="<?php echo $item->url ?>" <?php echo ($item->nofollow)?"rel=\"nofollow\"":""; ?> target="_blank">
                        <span class="wrap">
                            <img src="<?php echo $item->imageUrl ?>" alt="<?php echo $item->title ?>" />
                            <span class="mask">&nbsp;</span>
                        </span>
                        <span class="text">
                            <?php echo $item->title ?> 
                        </span>
                    </a>
                	</li>

                <?php endforeach; ?>

                </ul>
                <div id="scroller">
                    <div id="scroller-indent" class="clearfix">
                    </div>
                    <div style="clear: both;"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div class="footer" id="lf-footer">
			<a href="http://www.lovefilm.com/widgets" target="_blank">Get this widget for your site &rsaquo;</a>
		</div>	
    </div>
</div>
