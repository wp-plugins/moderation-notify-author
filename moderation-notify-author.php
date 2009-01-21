<?php
/*
Plugin Name: Moderation Notify Author
Plugin URI: http://ajaydsouza.com/wordpress/plugins/moderation-notify-author/
Description: Activate this plugin to automatically notify the author of a comment moderation request (in addition to the admin of the blog). Based on the plugin by <a href="http://weblogtoolscollection.com/">Mark Ghosh</a>. <a href="options-general.php?page=mna_options">Configure...</a>
Version: 1.0
Author: Ajay D'Souza
Author URI: http://ajaydsouza.com/
*/

if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");

function ald_mna_init() {
     load_plugin_textdomain('myald_mna_plugin', PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)));
}
add_action('init', 'ald_mna_init');

define('ALD_MNA_DIR', dirname(__FILE__));

function ald_mna($comment_id) {
	global $wpdb;

	$mna_settings = mna_read_options();
	if( !$mna_settings[moderate_author] ) return true; // Exit if option disabled

	$comment = get_comment($comment_id);
	if( '0' != $comment->comment_approved ) return true; // Exit if comment is approved
	
	$post = get_post($comment->comment_post_ID);    
	$admin_email = get_option('admin_email');
	$siteurl = get_option('siteurl');
	
	$user = get_userdata( $post->post_author );
	if ('' == $user->user_email) return true; // If there's no email to send the comment to
	if ($admin_email == $user->user_email) return true; // If the author is the admin	
	
	$comment_author_domain = @gethostbyaddr($comment->comment_author_IP);
	$comments_waiting = $wpdb->get_var("SELECT count(comment_ID) FROM $wpdb->comments WHERE comment_approved = '0'");

	$notify_message  = sprintf( __('A new comment on the post #%1$s "%2$s" is waiting for your approval'), $post->ID, $post->post_title ) . "\r\n";
	$notify_message .= get_permalink($comment->comment_post_ID) . "\r\n\r\n";
	$notify_message .= sprintf( __('Author : %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
	$notify_message .= sprintf( __('E-mail : %s'), $comment->comment_author_email ) . "\r\n";
	$notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "\r\n";
	$notify_message .= sprintf( __('Whois  : http://ws.arin.net/cgi-bin/whois.pl?queryinput=%s'), $comment->comment_author_IP ) . "\r\n";
	$notify_message .= __('Comment: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
	$notify_message .= sprintf( __('Approve it: %s'),  "$siteurl/wp-admin/comment.php?action=mac&c=$comment_id" ) . "\r\n";
	$notify_message .= sprintf( __('Delete it: %s'), "$siteurl/wp-admin/comment.php?action=cdc&c=$comment_id" ) . "\r\n";
	$notify_message .= sprintf( __('Spam it: %s'), "$siteurl/wp-admin/comment.php?action=cdc&dt=spam&c=$comment_id" ) . "\r\n";
	$notify_message .= sprintf( __('Currently %s comments are waiting for approval. Please visit the moderation panel:'), $comments_waiting ) . "\r\n";
	$notify_message .= "$siteurl/wp-admin/moderation.php\r\n";
	
	if ($mna_settings[addcredit]) $notify_message .= "\r\n\r\n" . sprintf( __('Email notification sent by ') ) . "Moderation Notify Author. Download from http://ajaydsouza.com/wordpress/plugins/moderation-notify-author/\r\n";

	$subject = sprintf( __('[%1$s] Please moderate: "%2$s"'), get_option('blogname'), $post->post_title );

	$notify_message = apply_filters('comment_moderation_text', $notify_message, $comment_id);
	$subject = apply_filters('comment_moderation_subject', $subject, $comment_id);

	@wp_mail($user->user_email, $subject, $notify_message);

	return true;
}
add_action('comment_post', 'ald_mna');


// Default Options
function mna_default_options() {
	$mna_settings = 	Array (
						moderate_author => true,		// Moderate author
						addcredit => true		// Moderate author
						);
	return $mna_settings;
}

// Function to read options from the database
function mna_read_options() 
{
	$mna_settings_changed = false;
	
	$defaults = mna_default_options();
	
	$mna_settings = array_map('stripslashes',(array)get_option('ald_mna_settings'));
	unset($mna_settings[0]); // produced by the (array) casting when there's nothing in the DB
	
	foreach ($defaults as $k=>$v) {
		if (!isset($mna_settings[$k]))
			$mna_settings[$k] = $v;
		$mna_settings_changed = true;	
	}
	if ($mna_settings_changed == true)
		update_option('ald_mna_settings', $mna_settings);
	
	return $mna_settings;

}


// This function adds an Options page in WP Admin
if (is_admin() || strstr($_SERVER['PHP_SELF'], 'wp-admin/')) {
	require_once(ALD_MNA_DIR . "/admin.inc.php");
}

?>