<div class="wrap">
	<h2>LOVEFiLM Widget Config</h2>
	<form method="post" action="options.php">
		<?php settings_fields('lovefilm-settings') ?>
        <table class="form-table">
		<tr valign="top">
			<th scope="row">Widget Mode</th>
			<?php $mode = get_option('lovefilm-widget-mode'); ?>
<!--			<td><input type="hidden" name="lovefilm-widget-mode" value="Affiliate" />Affiliate</td>-->
			<td><select id="lovefilm-widget-mode" class="postform" name="lovefilm-widget-mode">
				<option value="Affiliate" <?php echo ($mode == "Affiliate")?"selected=\"selected\"":""; ?>>Affiliate</option> 
				<option value="Contextual" <?php echo ($mode == "Contextual")?"selected=\"selected\"":""; ?>>Contextual</option> 
				<option value="Vanity" <?php echo ($mode == "Vanity")?"selected=\"selected\"":""; ?>>Vanity</option> 
			</select></td>
		</tr>
        <tr valign="top">
            <?php $width = get_option('lovefilm-widget-mode'); ?>
            <th scope="row">Width</th>
            <td><input type="text" name="lovefilm-widget-width" value="<?php echo $width ?>" /><td>
        </tr>
		<tr valign="top">
			<th scope="row">Affiliate ID</th>
			<td><input type="text" name="lovefilm-affiliate-id" value="<?php echo get_option('lovefilm-affiliate-id'); ?>" /></td>
		</tr>
		<tr valign="top">
			<th scope="row">Content</th>
			<?php $content = get_option('lovefilm-widget-content'); ?>
			<td><select id="lovefilm-widget-content" class="postform" name="lovefilm-widget-content">
				<option value="Films" <?php echo ($content == "Films")?"selected=\"selected\"":""; ?>>Films</option> 
				<option value="Games" <?php echo ($content == "Games")?"selected=\"selected\"":""; ?>>Games</option> 
			</select></td>
		</tr>
		<tr valign="top">
			<th scope="row">Theme</th>
			<?php $content = get_option('lovefilm-widget-theme'); ?>
			<td><select id="lovefilm-widget-theme" class="postform" name="lovefilm-widget-theme">
				<option value="light" <?php echo ($content == "light")?"selected=\"selected\"":""; ?>>Light</option> 
				<option value="dark" <?php echo ($content == "dark")?"selected=\"selected\"":""; ?>>Dark</option> 
			</select></td>
		</tr>
		</table>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
		</p>
	</form>
</div>