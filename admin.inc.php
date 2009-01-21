<?php
/**********************************************************************
*					Admin Page										*
*********************************************************************/
function mna_options() {
	
	$mna_settings = mna_read_options();

	if($_POST['mna_save']){
		$mna_settings[moderate_author] = (($_POST['moderate_author']) ? true : false);
		$mna_settings[addcredit] = (($_POST['addcredit']) ? true : false);
		
		update_option('ald_mna_settings', $mna_settings);
		
		$str = '<div id="message" class="updated fade"><p>'. __('Options saved successfully.','ald_mna_plugin') .'</p></div>';
		echo $str;
	}
	
	if ($_POST['mna_default']){
	
		delete_option('ald_mna_settings');
		$mna_settings = mna_default_options();
		update_option('ald_mna_settings', $mna_settings);
		
		$str = '<div id="message" class="updated fade"><p>'. __('Options set to Default.','ald_mna_plugin') .'</p></div>';
		echo $str;
	}
?>

<div class="wrap">
  <h2> Moderation Notify Author </h2>
  <div style="border: #ccc 1px solid; padding: 10px">
    <fieldset class="options">
    <legend>
    <h3>
      <?php _e('Support the Development','ald_mna_plugin'); ?>
    </h3>
    </legend>
    <p>
      <?php _e('If you find my','ald_mna_plugin'); ?>
      <a href="http://ajaydsouza.com/wordpress/plugins/moderation-notify-author/">Moderation Notify Author</a>
      <?php _e('useful, please do','ald_mna_plugin'); ?>
      <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&amp;business=donate@ajaydsouza.com&amp;item_name=Moderation%20Notify%20Author%20(From%20WP-Admin)&amp;no_shipping=1&amp;return=http://ajaydsouza.com/wordpress/plugins/moderation-notify-author/&amp;cancel_return=http://ajaydsouza.com/wordpress/plugins/moderation-notify-author/&amp;cn=Note%20to%20Author&amp;tax=0&amp;currency_code=USD&amp;bn=PP-DonationsBF&amp;charset=UTF-8" title="Donate via PayPal"><?php _e('drop in your contribution','ald_mna_plugin'); ?></a>.
	  (<a href="http://ajaydsouza.com/donate/"><?php _e('Some reasons why you should.','ald_mna_plugin'); ?></a>)</p>
    </fieldset>
  </div>
  <form method="post" id="mna_options" name="mna_options" style="border: #ccc 1px solid; padding: 10px">
    <fieldset class="options">
    <legend>
    <h3>
      <?php _e('Options:','ald_mna_plugin'); ?>
    </h3>
    </legend>
    <p>
      <label>
      <input type="checkbox" name="moderate_author" id="moderate_author" <?php if ($mna_settings[moderate_author]) echo 'checked="checked"' ?> />
      <?php _e('Send moderation email to authors?','ald_mna_plugin'); ?>
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="addcredit" id="addcredit" <?php if ($mna_settings[addcredit]) echo 'checked="checked"' ?> />
      <?php _e('Add Plugin Credits to Notification Email? While this is not compulsory, it would be nice.','ald_mna_plugin'); ?>
      </label>
    </p>
    <p>
      <input type="submit" name="mna_save" id="mna_save" value="Save Options" style="border:#00CC00 1px solid" />
      <input name="mna_default" type="submit" id="mna_default" value="Default Options" style="border:#FF0000 1px solid" onclick="if (!confirm('<?php _e('Do you want to set options to Default? If you don\'t have a copy of the username, please hit Cancel and copy it first.','ald_mna_plugin'); ?>')) return false;" />
    </p>
    </fieldset>
  </form>
</div>
<?php

}


function mna_adminmenu() {
	if (function_exists('current_user_can')) {
		// In WordPress 2.x
		if (current_user_can('manage_options')) {
			$mna_is_admin = true;
		}
	} else {
		// In WordPress 1.x
		global $user_ID;
		if (user_can_edit_user($user_ID, 0)) {
			$mna_is_admin = true;
		}
	}

	if ((function_exists('add_options_page'))&&($mna_is_admin)) {
		add_options_page(__("Notify Author", 'myald_mna_plugin'), __("Notify Author", 'myald_mna_plugin'), 9, 'mna_options', 'mna_options');
		}
}


add_action('admin_menu', 'mna_adminmenu');

?>