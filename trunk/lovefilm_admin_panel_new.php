<div class="wrap metabox-holder">
    <div id="icon-options-general" class="icon32"></div><h2>LOVEFiLM Widget Config</h2>


    <br />
    <div style="float:left;">
    <form method="post" action="options.php">
        <?php settings_fields('lovefilm-settings') ?>

        <div class="postbox" style="padding-bottom: 10px;width:700px; float:left;margin-right: 10px">
            <?php do_settings_sections('lovefilm-settings-main'); ?>
        </div>
    
<div class="postbox" style="width:300px; margin-right: 10px; float:left;">
    <h3>About this plugin</h3>
    <div style="padding:10px 10px 15px;">
    <table>
        <tr><td><strong>Author:</strong></td><td><h4 style="margin: 0px;"><a href="http://lovefilm.com/" target="_blank">LOVEFiLM</a></h3></td> </tr>
        <tr><td><strong>Version:</strong></td><td><?php echo LOVEFILM_WIDGET_VERSION; ?></td> </tr>
        <tr><td><strong>FAQ page:</strong></td><td><a href="http://www.lovefilm.com/partnership/widgets" target="_blank">LOVEFiLM Widget FAQs</a></td> </tr>
        <tr><td><strong>Please: </strong></td><td><a href="http://wordpress.org/extend/plugins/lovefilm-widget/" target="_blank">Rate this widget</a></td> </tr>
        <tr><td><strong>Feedback: </strong></td><td><a href="mailto:widgets@lovefilm.com">widgets@lovefilm.com</a></td> </tr>
    </table>
    </div>
</div>


<br /><br />
<div style="height:10px; clear: both;"></div>
<div class="postbox" style="padding-bottom: 10px;width: 500px;  margin-bottom: 10px; clear: both; ">
    <?php do_settings_sections('lovefilm-settings-earn-type'); ?>
</div>
<input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
</div>


</form>
</div>
