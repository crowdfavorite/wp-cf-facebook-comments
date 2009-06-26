<?php
/*
Plugin Name: CF Facebook Comments
Plugin URI:
Description: Adds the ability to use Facebook comments on a site
Version: 1.0
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

define('CF_FB_HAS_KEY', get_option('cf_fb_api_key'));

/**************
* HEADER WORK *
**************/
/* Add the initial facebook javascript */
function cf_add_fb_js() {
	wp_enqueue_script('fb_js', 'http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php');
	
}
if (CF_FB_HAS_KEY) {
	add_action('init', 'cf_add_fb_js');
}
/* Add the proper namespace for the facebook XFBML */
function cf_fb_xfbml_doctype($output) {
	return 'xmlns:fb="http://www.facebook.com/2008/fbml" '.$output; 
}
if (CF_FB_HAS_KEY) {
	add_filter('language_attributes', 'cf_fb_xfbml_doctype',$output);
}


/**************
* FOOTER WORK *
**************/
/* This script parses the XFBML to allow
* 	for the 'Connect' tags to be rendered */
function cf_add_fb_init() {
	?>
	<script type="text/javascript">
		FB.init(
			"<?php echo attribute_escape(get_option('cf_fb_api_key')); ?>",
			"<?php echo attribute_escape(WP_PLUGIN_URL.'/cf-facebook-comments/xd_receiver.php'); ?>"
		); 
	</script>
	<?php
}
if (CF_FB_HAS_KEY) {
	add_action('wp_footer', 'cf_add_fb_init');
}



/****************
* TEMPLATE TAGS *
****************/
/* Template Tag for displaying the Facebook Comment Form 
* 
*  Use the filter to apply additional parameters
* 	(see http://wiki.developers.facebook.com/index.php/Fb:comments_(XFBML) for
* 	more information on parameters)*/
function cf_get_fb_comment_form() {
	$comment_html = '<fb:comments></fb:comments>';
	return apply_filters('cf_fb_comment_form', $comment_html);
}
function cf_fb_comment_form() {
	echo cf_get_fb_comment_form();
}


/***************************
* ADMIN SIDE OF THINGS NOW *
***************************/
/* Admin menu now */
function cf_fb_comments_admin_form() {
	if (isset($_POST['cf_action']) && $_POST['cf_action'] == 'save_fb_comment_api_key') {
		update_option('cf_fb_api_key', $_POST['api_key']);
		$updated_string = '<div class="updated fade" id="message" style="background-color: rgb(255, 251, 204);"><p><strong>Settings saved.</strong></p></div>';
	}
	$api_key = get_option('cf_fb_api_key');
	
	?>
	<div class="wrap">
		<h2>Facebook Comments API Key</h2>
		<?php echo $updated_string; ?>
		<form action="" method="post" name="fb_comment_api_key">
			<table class="form-table">
				<tbody>
					<tr>
						<th><label for="api_key">API Key</label></th>
						<td><input type="text" name="api_key" value="<?php echo attribute_escape($api_key); ?>" id="api_key" class="regular-text"/></td>
				</tbody>
			</table>
			<input type="hidden" name="cf_action" value="save_fb_comment_api_key" />
			<p><button onclick="document.fb_comment_api_key.submit();" />Save Options</button></p>
		</form>
	</div>
	<?php
}
function cf_fb_comment_admin_menu() {
	/* Only Add this admin menu if the snippets plugin is installed */
	if (current_user_can('manage_options') && function_exists('cf_fb_comment_form')) {
		add_options_page( 
			'CF Facebook Comments Detail', 
			'CF Facebook Comments', 
			10, 
			basename(__FILE__), 
			'cf_fb_comments_admin_form' 
		);
	}
}
add_action('admin_menu', 'cf_fb_comment_admin_menu');
?>