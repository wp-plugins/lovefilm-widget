<script type="text/javascript">
LFWidget.remote_addr = '<?php echo LOVEFILM_WS_URL; ?>';
LFWidget.domain = '<?php echo get_option('lovefilm_domain') ?>';
LFWidget.affid = '<?php echo (array_key_exists('lovefilm_aff', $widgetOpts))?$widgetOpts['lovefilm_aff']:''; ?>';
LFWidget.theme = '<?php if(isset($widgetOpts['theme']) && !empty($widgetOpts['theme'])) echo $widgetOpts['theme']; ?>';
LFWidget.lfid = '<?php echo $widgetId; ?>';
LFWidget.promoCode = <?php echo (is_null($promoCode))?'null':'"'.$promoCode.'"'; ?>;
LFWidget.context = <?php echo (isset($widgetOpts['context']) && !empty($widgetOpts['context']))?'"'.$widgetOpts['context'].'"':'"'.LOVEFILM_DEFAULT_CONTEXT.'"'; ?>;
</script>
<div id="lf-widget" class="widget" <?php if(isset($widgetOpts['lovefilm_width']) && $widgetOpts != 'fluid') echo "style=\"width:{$widgetOpts['lovefilm_width']}px\""; ?>>
    <div id="lf-wrapped" <?php if(isset($widgetOpts['theme']) && !empty($widgetOpts['theme'])) echo " class=\"{$widgetOpts['theme']}\""; ?>>
        <div class="header">
            <a href="<?php echo is_null($mrktMsg) ? 'http://www.lovefilm.com' : $mrktMsg->href ?>" target="_blank" rel="nofollow" class="logo-link">
            <h3>LOVEFiLM</h3>
            <?php if(!is_null($mrktMsg)): ?><p id="lf-message"><?php echo $mrktMsg->anchor_text; ?></p><?php endif; ?>
            </a>
        </div>

        <div class="content accordion">
            <?php //print_r($exist);
                    $display = true;
                    if(($exist != null || $exist != "") && !is_home())
                    {
                        $display = false;
            ?>
            <div class="heading"><span class="arrow open">Featured Title</span></div>
            <div class="frame" id="featured-title">
                <a class="featured-title-review" href="<?php echo $exist->contextual_title_url ?>" alt="<?php echo $exist->contextual_title ?>" target="_blank">
                    <div class="wrap">
                        <span class="image-wrap">
                            <?php if(isset($exist->contextual_image) && !empty($exist->contextual_image)) : ?>
                            <img src="<?php echo $exist->contextual_image ?>" />
                            <?php else: ?>
                            <img src="<?php echo plugins_url('/img/default-image.gif',__FILE__) . ''; ?>" />
                            <?php endif; ?>
                        </span>
                        <span class="rental">Rent</span>
                    </div>
                    <div class="featured-title-description">
                        <h4>
                            <?php echo $exist->contextual_title ?>
                            <?php if(!empty($exist->contextual_release_date)) : ?>
                            <span>(<?php echo $exist->contextual_release_date ?>)</span>
                            <?php endif; ?>
                        </h4>
                        <span class="ratings ratings-stars-<?php echo strtr($exist->contextual_rating, '.', '-') ?>"><?php echo $exist->contextual_rating ?> out of 5 stars</span>
                        <?php if($exist->contextual_mode == "tv" || $exist->contextual_mode == "film"){?>
                        <dl class="clearfix">
                            <dt>Director:</dt>
                            <dd><strong><?php echo $exist->contextual_director ?></strong></dd>
                        </dl>
                        <?php } else { ?>
                         <dl class="clearfix">
                            <dt>Developer:</dt>
                            <dd><strong><?php echo $exist->contextual_director ?></strong></dd>
                        </dl>
                        <?php } ?>
                        <p>
                            <?php echo $exist->contextual_synopsis ?> 
                        </p><span class="ellipsis">...</span>
                        <span class="read-more">Read more</span>
                    </div>
                </a>
            </div>
            <?php } ?>
            <div class="heading"><span class="arrow<?php if($display) echo ' open' ?>">Latest Releases</span></div>
            <div class="frame">
                <iframe id="latest-releases" src="" scrolling="no" frameBorder="0"></iframe>
            </div>
            
            <div class="heading"><span class="arrow">Most Popular</span></div>
            <div class="frame">
                <iframe id="most-popular" src="" scrolling="no" frameBorder="0"></iframe>
            </div>
            <?php if($display == true ): ?>
            <?php if($embed_status == Lovefilm_Widget::SERVICE_SUCCESS) : ?>
            <div class="heading"><span class="arrow">LOVEFiLM Favourites</span></div>
            <div class="frame" id="favourites">
            	<ul id="constraint">
                    <?php foreach($titles as $i => $item): ?>

                    <li class="movie<?php echo ($i == count($titles) - 1)? ' last' : '' ?>" id="movie-<?php echo $i?>">
                    <a href="<?php echo $item->url ?>" <?php echo ($item->nofollow == 1)?"rel=\"nofollow\"":""; ?> target="_blank">
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
            <?php endif; ?>
        </div>
        <div class="footer" id="lf-footer">
			<a href="http://www.lovefilm.com/widgets" target="_blank" rel="nofollow">Get this widget for your site &rsaquo;</a>
		</div>	
    </div>
</div>
