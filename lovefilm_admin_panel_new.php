
<div class="wrap">
	<h2>LOVEFiLM Widget Config</h2>
    <a href="http://www.lovefilm.com/partnership/widgets">LOVEFiLM Widget FAQs</a>
	<form method="post" action="options.php">
		<?php settings_fields('lovefilm-settings') ?>
        <?php do_settings_sections('lovefilm-settings-main'); ?>
	<input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
	</form>
</div>
