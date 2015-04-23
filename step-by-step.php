<?php
/**
 *
 * @package   Step by Step
 * @author    Kyle M. Brown <kyle@kylembrown.com>
 * @license   GPL-2.0+
 * @link      http://kylembrown.com/step-by-step
 * @copyright 2014 Kyle M. Brown
 *
 * @wordpress-plugin
 * Plugin Name:       Step by Step
 * Plugin URI:        http://kylembrown.com/step-by-step
 * Description:       Create step-by-step instructions with images and display them on WordPress post or pages.
 * Version:           0.3.0
 * Author:            Kyle M. Brown
 * Author URI:        http://kylembrown.com/step-by-step
 * Text Domain:       step-by-step
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/kmb40/step-by-step
 */

function sbs_custom_admin_head(){
	echo '<script type="text/javascript">
			jQuery(document).ready( function(){
				jQuery("#post").attr("enctype","multipart/form-data");
	    } );
	</script>';
}
add_action('admin_head', 'sbs_custom_admin_head');

register_activation_hook(  __FILE__, 'install_step_setting');
function install_step_setting()
{
	$check_setting = get_option( 'step_setting' );
	if(isset($check_setting) && $check_setting=='0')
		update_option( 'step_setting', '1' );
	else if(!isset($check_setting))
		add_option( 'step_setting', '1', '', 'yes' );
}

//wordpress hook to delete the file and records form database
register_uninstall_hook( __FILE__, 'sbs_uninstall' );

function sbs_uninstall()
{
	if ( ! current_user_can( 'activate_plugins' ) )
        return;
	
	$sbs_setting='';
	$sbs_setting = get_option( 'step_setting' );

	if(isset($sbs_setting) && $sbs_setting=='0'){
		delete_option( 'step_setting' );

		$args = array(
			'post_type' =>'article'
		);

		$posts = get_posts( $args );
		
		if (is_array($posts)) {
		   foreach ($posts as $post) {
			   wp_delete_post( $post->ID, true);			   
		   }
		}
	}
}

function custom_post_type()
{

	$labels = array(
		'name'               => _x( 'Guides', 'post type general name' ),
		'singular_name'      => _x( 'Guide', 'post type singular name' ),
		'add_new'            => _x( 'Add New', 'guide' ),
		'add_new_item'       => __( 'Add New Guide' ),
		'edit_item'          => __( 'Edit Guide' ),
		'new_item'           => __( 'New Guide' ),
		'all_items'          => __( 'All Guides' ),
		'view_item'          => __( 'View Guide' ),
		'search_items'       => __( 'Search Guides' ),
		'not_found'          => __( 'No guides found' ),
		'not_found_in_trash' => __( 'No guides found in the trash' ), 
		'parent_item_colon'  => '',
		'menu_name'          => 'Step by Step'
	);
	$args = array(
		'labels'        => $labels,
		'description'   => 'Holds our guides and guide specific data',
		'public'        => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'rewrite'            => array('slug'=>'guide','with_front'=>false),
		'query_var'          => true,
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => 5,
		'menu_icon'          => 'dashicons-format-aside',
		'supports'      => array( 'title', 'editor', 'thumbnail')
		
	);
	register_post_type( 'article', $args );	
	
}
add_action( 'init', 'custom_post_type' );

// function added here to add the setting page
add_action('admin_menu' , 'sbs_enable_pages');

function sbs_enable_pages() {
	add_submenu_page('edit.php?post_type=article', 'Step by Step Settings', 'Settings', 'edit_posts', basename(__FILE__), 'sbs_setting');
}

function sbs_setting() { 

	$setting_val='';
	$checked='';

	if(isset($_POST['save_settings']) && $_POST['save_settings']=='Save Settings'){
		if(isset($_POST['step_setting']))
			$setting_val = $_POST['step_setting'];
		else
			$setting_val=0;

		update_option( 'step_setting', $setting_val );
	}
	$step_setting = get_option( 'step_setting' );

	if(isset($step_setting) && $step_setting=='1')
		$checked ="checked=checked";
	else
		$checked='';

?>

<div class="wrap">
     <h2 style="margin-bottom:50px;">Step by Step Settings</h2>
	 <div style="width:65%;float:left;">
	 <form name="step" id="step" action="" method="post">
		<p style="font-size:14px;width:20%;float:left;"><strong>Save Settings and Guides</strong></p>

		<p style="font-size:14px;width:80%;float:left;">
			<input type="checkbox" name="step_setting" id="step_setting" value="1" <?php echo $checked;?>> Save your settings and existing guides when uninstalling this plugin. Useful when upgrading or re-installing. 
			<br/><br/><strong>Note:</strong>If you deselect this box ALL of your guides will be erased when the plugin is uninstalled.
		</p>

		<p style="clear:both;"></p>

		<p style="width:80%;float:right">
			<input type="submit" name="save_settings" id="save_settings" value="Save Settings" class="button button-primary button-large">
		</p>

		<p style="clear:both;"></p>
   </form>
   </div>

   <div class="sidebar-container" style="width:30%;float:right; background-color: #FFFFFF;border: 1px solid #E5E5E5;border-radius: 0;font-size: 15px;margin-bottom: 15px;padding: 0;position: relative;text-align: left;">
	<div class="sidebar-content" style="padding:14px;">
		<p><?php _e( 'Get notified about updates and of the Pro version.'); ?></p>

		<form target="_blank" method="post" action="http://mayvik.us2.list-manage1.com/subscribe/post?u=927eadd7a0cb4d4e8d9057240&id=4404ee12f8" novalidate>
			<p>
				<input type="email" placeholder="Your email address" class="large-text" name="EMAIL">
			</p>
			<p>
				<input type="submit" class="button-primary" name="subscribe" value="Keep Me Updated">
			</p>
		</form>
	</div>
</div>

<div style="clear:both;"></div>

<div class="sidebar-container" style="width:30%;float:right; background-color: #FFFFFF;border: 1px solid #E5E5E5;border-radius: 0;font-size: 15px;margin-bottom: 15px;padding: 0;position: relative;text-align: left;">
	<div class="sidebar-content" style="padding:14px;">
		<p>
			<?php _e('Please help us get noticed with a rating and short review.','step-by-step'); ?>
		</p>

		<a href="http://wordpress.org/support/view/plugin-reviews/step-by-step" class="button-primary" target="_blank">
			<?php _e('Rate this plugin on WordPress.org','step-by-step'); ?></a>
	</div>
</div>

<div style="clear:both;"></div>

<div class="sidebar-container" style="width:30%;float:right; background-color: #FFFFFF;border: 1px solid #E5E5E5;border-radius: 0;font-size: 15px;margin-bottom: 15px;padding: 0;position: relative;text-align: left;">
	<div class="sidebar-content" style="padding:14px;">
		<p>
			<?php _e('For feature request or help?','step-by-step'); ?>
		</p>
		<p>
			<a href="http://wordpress.org/support/plugin/step-by-step" class="button-primary" target="_blank">
				<?php _e('Visit our Community Support Forums','step-by-step'); ?></a>
		</p>
	</div>
</div>

<div style="clear:both;"></div>

 </div>
 <?php
}
add_filter( 'post_updated_messages', 'sbs_updated_messages' );

function sbs_updated_messages( $messages ) {
	global $post, $post_ID;
	
	$messages['article'] = array(
		0 => '', 
		1 => sprintf( __('Guide updated. <a href="%s">View guide</a>'), esc_url( get_permalink($post_ID) ) ),
		2 => __('Custom field updated.'),
		3 => __('Custom field deleted.'),
		4 => __('Guide updated.'),
		5 => isset($_GET['revision']) ? sprintf( __('Guide restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('Guide published. <a href="%s">View guide</a>'), esc_url( get_permalink($post_ID) ) ),
		7 => __('Guide saved.'),
		8 => sprintf( __('Guide submitted. <a target="_blank" href="%s">Preview guide</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		9 => sprintf( __('Guide scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview guide</a>'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
		10 => sprintf( __('Guide draft updated. <a target="_blank" href="%s">Preview guide</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	);
	return $messages;
}

//adding the meta box when the admin panel initialises
add_action("admin_init", "add_article_metabox");


function add_article_metabox(){
    add_meta_box('manage_steps', 'Manage Guide Steps', 'steps_meta_box', 'article', 'normal', 'default');
}


function steps_meta_box($post, $args) { 
	$steps_meta = get_post_meta($post->ID, 'meta-step', true);
?>
	<div id="mainstep">
	   <?php if(isset($steps_meta['step'])) { 
		$i = 1;
		if(is_array($steps_meta['step']) ) {
		foreach($steps_meta['step'] as $key => $value) {
			?>
			<div id='step<?php echo $i;?>' style="background: -moz-linear-gradient(center top , #F5F5F5, #FCFCFC) repeat scroll 0 0 rgba(0, 0, 0, 0);">
				<p style="text-align:right"><a href="javascript:void(0);" onclick="return removeDiv('<?php echo 'step'.$i;?>');">- Delete Step</a></p>
				<p>Step<span style="color:red;">*</span><br> <span><input type="text" size="40" value="<?php echo  $value;?>" name="step[]" id="step"></span></p>
				
				<p>Body<span style="color:red;">*</span><br> <span><textarea name="step_title[]" id="step_title" rows="4" cols="40"><?php echo  $steps_meta['step_title'][$key];?></textarea></span></p>
				
				<p>Note<br> <span><input type="text" size="40" value="<?php echo $steps_meta['note'][$key];?>" name="note[]" id="note"></span></p>
				<p>
				<?php
				if(isset($steps_meta['step_image'][$key]) && !empty($steps_meta['step_image'][$key])) {
					$image_attributes = wp_get_attachment_image_src( $steps_meta['step_image'][$key],array(100,100) ); 
					$attr = get_the_post_thumbnail($steps_meta['step_image'][$key], 'thumbnail');
					
				?>
					<img style="vertical-align: middle;" src="<?php echo $image_attributes[0]; ?>" width="<?php echo $image_attributes[1]; ?>" height="<?php echo $image_attributes[2]; ?>">&nbsp;&nbsp;<a href="javascript:void(0);" alt="Remove" title="Remove" onclick="return remove_attachement('<?php echo $steps_meta['step_image'][$key];?>',<?php echo $post->ID;?>,'<?php echo $i;?>')">Remove</a>
					<img id="loader" style="display: none;margin: 0 auto;text-align: center;" src="<?php echo plugins_url()?>/step-by-step/includes/images/loader.gif" />
					<p>Image<br> <span><input type="file" size="60" value="" name="step_image[]" id="step_image"></span></p>
				<?php }else { ?>
				</p>
				<p>Image<br> <span><input type="file" size="60" value="" name="step_image[]" id="step_image"></span></p>
				<?php } ?>
			</div>
			<?php
			$i++;
		 }	
		}
		} else {?>
			<div id='step1' style="background: -moz-linear-gradient(center top , #F5F5F5, #FCFCFC) repeat scroll 0 0 rgba(0, 0, 0, 0);">
			<p>Step<span style="color:red;">*</span><br> <span><input type="text" size="40" value="" name="step[]" id="step"></span></p>
			
			<p>Body<span style="color:red;">*</span><br> <span><textarea name="step_title[]" id="step_title" rows="4" cols="40"></textarea></span></p>
			
			<p>Note<br> <span><input type="text" size="40" value="" name="note[]" id="note"></span></p>
			<p>Image<br> <span><input type="file" size="60" value="" name="step_image[]" id="step_image"></span></p>
		</div>
		<?php } ?>
	</div>
	<div style="clear:both;"></div>
	<div style="padding-bottom:5px;text-align:right;color:#fff;"><a href="javascript:void(0);" onClick="addmorediv()">+ Add Step</a></div>
	<input type="hidden" name="step_count" id="step_count" value="1">

	<?php if(isset($steps_meta['step']) && count($steps_meta['step'])>0) { ?>

		<div style="background: -moz-linear-gradient(center top , #F5F5F5, #FCFCFC) repeat scroll 0 0 rgba(0, 0, 0, 0);">
		<p>Get the Short Code</span><br> <span>
		<textarea rows="3" cols="40" readonly>[display_article id='<?php echo $post->ID; ?>' type='article']</textarea>
		</span>
		</div>
		
	<?php } ?>


<script>
function remove_attachement(attachementID, postID,stepId)
{
	var data = {
		action: 'custom_delete_attachement',
		attachement_ID:attachementID,
		post_ID:postID
	};

	jQuery.post(ajaxurl, data, function(response) {
		jQuery("#loader"+stepId).css({'display':'inline-block'});
		jQuery("#step_img").hide();		
		window.setTimeout('location.reload()', 1000);
	});

}


function addmorediv()
{
	var cnt = jQuery('#step_count').val();
	cnt = parseInt(cnt)+1;
	jQuery('#mainstep').append('<div id="step'+cnt+'" style="padding-top:10px;background: -moz-linear-gradient(center top , #F5F5F5, #FCFCFC) repeat scroll 0 0 rgba(0, 0, 0, 0);"><p style="text-align:right"><a href="javascript:void(0);" onclick="return removeDiv(\'step'+cnt+'\');">- Delete Step</a></p><p>Step<span style="color:red;">*</span><br> <span><input type="text" size="40" value="" name="step[]" id="step"></span></p><p>Body<span style="color:red;">*</span><br> <span><textarea name="step_title[]" id="step_title" rows="4" cols="40"></textarea></span></p><p>Note<br> <span><input type="text" size="40" value="" name="note[]" id="note"></span></p><p>Image<br> <span><input type="file" size="60" value="" name="step_image[]" id="step_image"></span></p></div>');
	jQuery('#step_count').val(cnt);
}
function removeDiv(divId)
{
	jQuery('#'+divId).remove();
}

</script>
<?php } 

add_action( 'wp_ajax_custom_delete_attachement', 'remove_attachement_image' );

function remove_attachement_image() {
	global $wpdb; // this is how you get access to the database

	$attachement_ID = intval( $_POST['attachement_ID'] );
	$post_ID = intval( $_POST['post_ID'] );
	
	if(isset($attachement_ID) && isset($post_ID))
	{
		
		wp_delete_attachment( $attachement_ID);
		$steps_meta_data = get_post_meta($post_ID, 'meta-step', true);
		
		if(($key = array_search($attachement_ID, $steps_meta_data['step_image'])) !== false) {
			 unset($steps_meta_data['step_image'][$key]);
			update_post_meta( $post_ID, 'meta-step',  $steps_meta_data );
		}	
		$msg = 'attachment has been deleted successfully';
	}

	
}

function prfx_meta_save( $post_id ) {
	if ( !wp_is_post_revision( $post_id ))
	{
	$attached_file_array='';
	$steps_meta = get_post_meta($post_id, 'meta-step', true);
	if(isset($steps_meta['step_image']))
		$attached_file_array = $steps_meta['step_image'];


	if(!is_array($attached_file_array)) { $attached_file_array = array(); }// Kyle added to fix "in_array() expects parameter" 2 to be array error on line 228
	

	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/image.php' );

	// required for wp_handle_upload() to upload the file
	$upload_overrides = array( 'test_form' => FALSE );

   
	// load up a variable with the upload direcotry
	$uploads = wp_upload_dir();
 
	// foreach file uploaded do the upload


	if(!empty($_FILES['step_image']['name'])){//Kmb to fix array error
		
	foreach($_FILES['step_image']['name'] as $key => $filenamevalue) {
		
		$attach_id = '';
		if(isset($_FILES['step_image']['name'][$key]) && $_FILES['step_image']['error'][$key]!='4'){
			
			if(isset($steps_meta['step_image'][$key]) && !empty($steps_meta['step_image'][$key])) {
				wp_delete_attachment( $steps_meta['step_image'][$key] );
				if (in_array($steps_meta['step_image'][$key], $attached_file_array)) {
					unset($attached_file_array[array_search($steps_meta['step_image'][$key],$attached_file_array)]);
				}
			}

 
			// create an array of the $_FILES for each file
			$file_array = array(
				'name' => $filenamevalue,
				'type' => $_FILES['step_image']['type'][$key],
				'tmp_name' => $_FILES['step_image']['tmp_name'][$key],
				'error' => $_FILES['step_image']['error'][$key],
				'size' => $_FILES['step_image']['size'][$key],
			);

			// check to see if the file name is not empty
			if ( !empty( $file_array['name'] ) ) {
	 
				// upload the file to the server
				$uploaded_file = wp_handle_upload( $file_array, $upload_overrides );

				// Check the type of tile. We'll use this as the 'post_mime_type'.
				$filetype = wp_check_filetype( basename( $uploaded_file['file'] ), null );

				
				// Prepare an array of post data for the attachment.
				$attachment = array(
					'guid'           => $uploads['url'] . '/' . basename( $uploaded_file['file'] ), 
					'post_mime_type' => $filetype['type'],
					'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $uploaded_file['file'] ) ),
					'post_content'   => '',
					'post_status'    => 'inherit'
				);

				$imagename =  $uploads['path'] . '/' . basename($uploaded_file['file'] );

				// Insert the attachment.
				$attach_id = wp_insert_attachment( $attachment, $imagename, $post_id );
							
				// get the attachemnet image relative path for genrate the image metadata
				$attachment_image_path = $uploads['path'] . '/' . basename($uploaded_file['file'] );

				// Generate the metadata for the attachment, and update the database record.
				$attach_data = wp_generate_attachment_metadata( $attach_id, $attachment_image_path );
					
				wp_update_attachment_metadata( $attach_id, $attach_data );
				$attached_file_array[$key] =  $attach_id;

				
				
		
			}
		}
	}
}//	End if(!empty ->

	$step='';
	$step_title='';
	$note='';

	if(isset($_POST['step']))
		$step=$_POST['step'];
	if(isset($_POST['step_title']))
		$step_title=$_POST['step_title'];
	if(isset($_POST['note']))
		$note=$_POST['note'];
	
	if(isset($_POST['publish']) || isset($_POST['save']))
	{
		$result = array('step'=>$step, 'step_title'=>$step_title,'note'=>$note,'step_image'=>$attached_file_array);
		update_post_meta( $post_id, 'meta-step',  $result );
	}
}
}
add_action( 'save_post', 'prfx_meta_save' );

add_action( 'admin_init', 'disable_autosave' );
function disable_autosave() {
        wp_deregister_script( 'autosave' );
}
function article_func( $atts ) {
	
   	if(isset($atts['id']))
	{
		$post_id = sanitize_text_field( $atts['id'] );
		$post_type= sanitize_text_field( $atts['type'] );
		$post = get_post( $post_id );
		
		$thumb_image_url = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'medium');
		$article_steps_meta = get_post_meta($post_id, 'meta-step', true);
		$string ='<div>';
		$string .='<table cellspacing="5" cellpadding="5" border="0" >';
		$string .= '<tr><td colspan="2" valign="top"><strong>'.$post->post_title.'</strong></td></tr>';

		if(isset($thumb_image_url[0])){
			$string .='<tr><td><img src="'.$thumb_image_url[0].'" alt="'.$post->post_content.'" width="'.$thumb_image_url[1].'" height="'.$thumb_image_url[2].'"></td><td style="vertical-align:top;">'.$post->post_content.'</td> </TR>';
		}else{
			$string .='<tr><td colspan="2" valign="top">'.$post->post_content.'</td> </tr>';
		}


		if(count($article_steps_meta["step"])) {
			$string .='<tr><td colspan="2">&nbsp;</td></tr><tr><td colspan="2"><table cellspacing="5" cellpadding="5" border="0"><tr>';
			for($i=0;$i<count($article_steps_meta["step"]);$i++){ 	
				if(isset($article_steps_meta["step_image"][$i])){
				 $kk=wp_get_attachment_image_src( $article_steps_meta["step_image"][$i], 'thumbnail', true );
				 $imgString='<img src="'.$kk[0].'" width="'.$kk[1].'"  height="'.$kk[2].'" >';				
				} else {
					$imgString='';
				}

				$string .='<td width="33%" valign="top">' .$imgString.'<br><br><strong>'.$article_steps_meta["step"][$i].' </strong><br><br>'.$article_steps_meta["step_title"][$i].'<br><br><strong>'.substr($article_steps_meta["note"][$i],0,125).'</strong></td>';
								
				if(($i+1)%3==0) {$string .='</tr><tr>';}
			} 

			$string .='</tr></table></td></tr></table>';
		}
		
		return $string .=' </div>';
	}
}
add_shortcode( 'display_article', 'article_func' );
/*Uncomment to use the alternative guide instead of artcle without breaking legacy guides. kmb*/
//add_shortcode( 'display_guide', 'article_func' );

add_filter('single_template', 'my_custom_template');

function my_custom_template($single) {
    global $wp_query, $post;

/* Checks for single template by post type */
if ($post->post_type == "article"){
	if(file_exists(plugin_dir_path( __FILE__ ).'single-article.php'))
        return plugin_dir_path( __FILE__ ). 'single-article.php';
}
    return $single;
}


// plugin definitions
define( 'FB_BASENAME', plugin_basename( __FILE__ ) );
define( 'FB_BASEFOLDER', plugin_basename( dirname( __FILE__ ) ) );
define( 'FB_FILENAME', str_replace( FB_BASEFOLDER.'/', '', plugin_basename(__FILE__) ) );

function filter_plugin_meta($links, $file) {
	
	/* create link */
	if ( $file == FB_BASENAME ) {
		array_unshift(
			$links,
			sprintf( '<a href="edit.php?post_type=article">Guides</a>', FB_FILENAME, __('Guides') )
		);
	}
	
	return $links;
}

global $wp_version;
if ( version_compare( $wp_version, '2.8alpha', '>' ) )
//	add_filter( 'plugin_row_meta', 'filter_plugin_meta', 10, 2 ); // only 2.8 and higher
add_filter( 'plugin_action_links', 'filter_plugin_meta', 10, 2 );

?>