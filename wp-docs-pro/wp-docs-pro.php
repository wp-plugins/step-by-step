<?php
/**
 *
 * @package   wpDocs
 * @author    Kyle M. Brown <kyle@kylembrown.com>
 * @license   GPL-2.0+
 * @link      http://kylembrown.com/wpDocs
 * @copyright 2014 Kyle M. Brown
 *
 * @wordpress-plugin
 * Plugin Name:       WPD
 * Plugin URI:        http://kylembrown.com/wp-docs
 * Description:       Create step-by-step instructions with images and display them on WordPress post or pages.
 * Version:           1.6.0
 * Author:            Kyle M. Brown
 * Author URI:        http://kylembrown.com/wp-docs
 * Text Domain:       wp-docs-pro
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */


/* Filter the single_template with our custom function (CPT Root/index) ~ Kyle*/
add_filter('archive_template', 'SBS_root_custom_template');

function SBS_root_custom_template($archive) {
    global $wp_query, $post;

/* Checks for single template by post type */
if ($post->post_type == "article"){
    if(file_exists(plugin_dir_path( __FILE__ ). '/includes/WPD_templates/archive-article.php'))
        return plugin_dir_path( __FILE__ ) . '/includes/WPD_templates/archive-article.php';
}
    return $archive;
}

// Plugin version
function sbs_plugin_get_version() {
    $plugin_data = get_plugin_data( __FILE__ );
    $plugin_version = $plugin_data['Version'];
    echo 'v'.$plugin_version;
}

// Loads native thickbox code 6152104 kmb ; called with 'class="thickbox"' 	 	
	add_action('init', 'sbs_thickbox'); 	 	
	function sbs_thickbox() { 	 	
	    if (! is_admin()) { 	 	
	        wp_enqueue_script('thickbox', null,  array('jquery')); 	 	
	        wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0'); 	 	
	    } 	 	
	}

// Register style sheet.
add_action( 'init', 'register_plugin_styles' );

/**
 * Register style sheet.
 */
function register_plugin_styles() {
	wp_register_style( 'wp-docs-pro', plugins_url( 'wp-docs-pro/style/dl.css' ) );
	wp_enqueue_style( 'wp-docs-pro' );
	wp_register_script('wp-docs-pro-jcode', plugins_url( 'wp-docs-pro/js/change.js' ) );
	wp_enqueue_script('wp-docs-pro-jcode');	
}

function sbs_custom_admin_head(){
	echo '<script type="text/javascript">
			jQuery(document).ready( function(){
				jQuery("#post").attr("enctype","multipart/form-data");			
			
	    } );
	</script>';
}
add_action('admin_head', 'sbs_custom_admin_head');


/********* pro section added *****************************/
function my_admin_scripts() {
	wp_enqueue_script('media-upload');
	wp_enqueue_script('thickbox');
	wp_register_script('my-upload', plugins_url('js/my-admin.js', __FILE__), array('jquery','media-upload','thickbox'));
	wp_enqueue_script('my-upload');		
	wp_enqueue_script( 'my-editor', plugins_url('js/kia-metabox.min.js', __FILE__), array( 'jquery', 'word-count', 'editor', 'quicktags', 'wplink', 'wp-fullscreen', 'media-upload' ), '1.1', true );
	wp_print_scripts('my-editor');
	
}
 
function my_admin_styles() {
	wp_enqueue_style('thickbox');
}
 

add_action('admin_print_scripts', 'my_admin_scripts');
add_action('admin_print_styles', 'my_admin_styles');

add_action('init', 'javascript_on_header' );
 
function javascript_on_header () {
   	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-widget' );
	wp_enqueue_script( 'jquery-ui-mouse' );
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script('tiny_mce');	
}


/********* pro section end here *****************************/

register_activation_hook(  __FILE__, 'install_step_setting');
function install_step_setting()
{
	$check_setting = get_option( 'step_setting' );
	if(isset($check_setting) && $check_setting=='0')
		update_option( 'step_setting', '1' );
	else if(!isset($check_setting))
		add_option( 'step_setting', '1', '', 'yes' );


	$check_native_upload = get_option( 'native_upload' );
	if(isset($check_native_upload) && $check_native_upload=='0')
		update_option( 'native_upload', '1' );
	else if(!isset($check_native_upload))
		add_option( 'native_upload', '1', '', 'yes' );	
		

}
function set_step_autonumber_value(){
	
$check_step_autonumber = get_option( 'set_step_autonumber' );
	
if(!isset($check_step_autonumber) || $check_step_autonumber!=1)
	{
		global $wpdb;
		
			/// get all article type posts available in wordpress with step meta key
	$posts=$wpdb->get_results("select meta_value,post_id from {$wpdb->base_prefix}postmeta as postmeta inner join {$wpdb->base_prefix}posts as posts on postmeta.post_id=posts.ID where posts.post_type='article' and postmeta.meta_key='meta-step'", OBJECT);
	if(count($posts)>0){
		foreach ($posts as $article)
		{
			$article_id=$article->post_id;
			/// get step meta data
			$meta_step_values=unserialize($article->meta_value);
			$meta_step_values['step_autonumber'][0]="yes";
			update_post_meta($article_id,'meta-step',$meta_step_values);
		}
	}
		
		update_option( 'set_step_autonumber', '1' );
	}
	

}
add_action('init','set_step_autonumber_value');

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
		delete_option( 'native_upload' );

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
		'menu_name'          => 'WP Docs Pro'
	);
	$args = array(
		'labels'        => $labels,
		'description'   => 'Holds our guides and guide specific data',
		'public'        => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'rewrite'            => array('slug'=>'docs','with_front'=>false),
		'query_var'          => true,
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => 5,
		'menu_icon'          => 'dashicons-format-aside',
		'supports'      => array( 'title', 'editor', 'thumbnail'),
		//'taxonomies' => array('category')
		
	);
	register_post_type( 'article', $args );		
	
	register_sidebar( array(
		'name' => 'Guide Table of Contents',
		'id' => 'guide_menu_widget',
		'before_widget' => '<div class="widget">',
		'after_widget' => '</div>',
		'before_title' => '<h1 class="title">',
		'after_title' => '</h1>',
	) );
    flush_rewrite_rules(); /*** Start Flush Rewrite Rules to condition the WP install for the plugins actiavtion and or removal ~ Kyle ***/
}
add_action( 'init', 'custom_post_type' );

add_action( 'after_setup_theme', 'media_size_setting',10,2);
function media_size_setting()
{
	$thumbnail_setting = get_option( 'article-thumbnail' );
	if($thumbnail_setting['thumbnail_size_w'])
		$small_width =$thumbnail_setting['thumbnail_size_w'];
	else
		$small_width =150;

	if($thumbnail_setting['thumbnail_size_h'])
		$small_height =$thumbnail_setting['thumbnail_size_h'];
	else
		$small_height =150;

	if($thumbnail_setting['medium_size_w'])
		$medium_size_w =$thumbnail_setting['medium_size_w'];
	else
		$medium_size_w = 300;

	if($thumbnail_setting['medium_size_h'])
		$medium_size_h =$thumbnail_setting['medium_size_h'];
	else
		$medium_size_h=300;

	if($thumbnail_setting['large_size_w'])
		$large_size_w =$thumbnail_setting['large_size_w'];
	else
		$large_size_w = 600;

	if($thumbnail_setting['large_size_h'])
		$large_size_h =$thumbnail_setting['large_size_h'];
	else
		$large_size_h=400;

	add_theme_support( 'article-thumbnails' );
	add_image_size( 'article-thumbnails', $small_width, $small_height, true );

	add_theme_support( 'article-medium' );
	add_image_size( 'article-medium', $medium_size_w, $medium_size_h, true );

	add_theme_support( 'article-large' );
	add_image_size( 'article-large', $large_size_w, $large_size_h, true );
}

// function added here to add the setting page
add_action('admin_menu' , 'sbs_enable_pages');

function sbs_enable_pages() {
	add_submenu_page('edit.php?post_type=article', 'WP Docs Pro Settings', 'Settings', 'edit_posts', basename(__FILE__), 'sbs_setting');
	//add_submenu_page('edit.php?post_type=article', 'Guide Media Settings', 'Guide Media Settings', 'edit_posts', 'guide-media-setting', 'guide_media_setting');
	add_submenu_page('edit.php?post_type=article', 'Table of Contents', 'Table of Contents', 'edit_posts', 'custom-menu', 'custom_menu_setting');
}

function custom_menu_setting(){ ?>

	<div class="wrap">
		 <h2 style="margin-bottom:50px;">Table of Contents</h2>
		 <div><p><h3>There are currently two methods of creating a table of contents for your guides.</br>
			    Note: The prerequisite for each is that your guide/s must have been created prior to arranging them into a table of contents.</h3></p>
			<p><strong>When Using Guides - </strong>Method One (Recommended):<br>
				1. Visit the native Nav Menu <a href="<?php echo admin_url().'nav-menus.php';?>">editor </a> and select the guides (See Figure 1) that you created using the Step-by-step plugin and create your table of contents using standard <a href="http://codex.wordpress.org/WordPress_Menu_User_Guide" target="_blank">functionality</a> for sidebars or the navigation bar. </br>
				2. Visit the  <a href="<?php echo admin_url().'widgets.php';?>">"widgets"</a> section and you will find a widget placeholder name "Guides Table of Contents".<br/>
				3. Select the custom menu from the list of available widgets and place it inside of the Guides Table of Contents area.<br/>
				4. Thats it! Your table of contents should appear whenever you view any of your guides.<br/>
				</br><br/>
				<img src="<?php echo plugins_url('/admin/assets/guides.png', __FILE__) ; ?>" title="Figure 1" alt="Figure 1">Figure 1<br/><br/><b>Note: The plugin has made this an option, even though you may not have seen it previously.</b></p><br/>
		      <strong>When Using Shortcodes - </strong> Method Two:<br/> 
		       Visit the native Nav Menu <a href="<?php echo admin_url().'nav-menus.php';?>">editor </a> and select the post or pages where you have placed your shortcodes and create your table of contents using standard <a href="http://codex.wordpress.org/WordPress_Menu_User_Guide" target="_blank">functionality</a> for sidebars or the navigation bar.</br></br>
		      <a href="<?php echo admin_url().'nav-menus.php';?>"><button class="button button-primary">Go to Nav Menu Editor</button></a>
			  </div>
		
	<?php
	//	register_nav_menu( 'guide-menu', 'Guide Menu' );
	//	 wp_nav_menu( array( 'theme_location' => 'guide-menu'));	
	?>
	</div>
<?php
}

function sbs_setting() { 

	
	
	////////// settings for general tab
	$setting_val='';
	$checked='';

	if(isset($_POST['save_settings']) && $_POST['save_settings']=='Save Settings'){
		if(isset($_POST['step_setting']))
			$setting_val = $_POST['step_setting'];
		else
			$setting_val=0;

		update_option( 'step_setting', $setting_val );

		if(isset($_POST['native_upload']))
			$setting_val = $_POST['native_upload'];
		else
			$setting_val=0;

		update_option( 'native_upload', $setting_val );

		if(isset($_POST['guide_lightbox']))
			$setting_val = $_POST['guide_lightbox'];
		else
			$setting_val=0;

		update_option( 'guide_lightbox', $setting_val );

		if(isset($_POST['cus_menu']))
			$setting_val = $_POST['cus_menu'];
		else
			$setting_val=0;

		update_option( 'cus_menu', $setting_val );


		if(isset($_POST['guides_directory_name']))
			$setting_val = $_POST['guides_directory_name'];
		else
			$setting_val="";

		update_option( 'guides_directory_name', $setting_val );
	}
	$step_setting = get_option( 'step_setting' );

	if(isset($step_setting) && $step_setting=='1')
		$checked ="checked=checked";
	else
		$checked='';

	$native_upload = get_option( 'native_upload' );

	if(isset($native_upload) && $native_upload=='1')
		$native_checked ="checked=checked";
	else
		$native_checked='';

	$guide_lightbox = get_option( 'guide_lightbox' );

	if(isset($guide_lightbox) && $guide_lightbox=='1')
		$guide_lightbox ="checked=checked";
	else
		$guide_lightbox='';

	$cus_menu = get_option( 'cus_menu' );

	if(isset($cus_menu) && $cus_menu=='1')
		$cus_menu ="checked=checked";
	else
		$cus_menu='';


	$guides_directory_name = get_option( 'guides_directory_name' );

	if(!empty($guides_directory_name) && $guides_directory_name!='')
		$guides_directory_name =$guides_directory_name;
	else
		$guides_directory_name='';


	

	wp_register_script('tabjs', plugins_url('js/tabcontent.js', __FILE__));
	wp_enqueue_script('tabjs');		
	wp_enqueue_style('tabcss', plugins_url('includes/tabcontent.css', __FILE__)); 	

/// settings for media tab
global $_wp_additional_image_sizes;
	
	if(isset($_POST['submit']) && $_POST['submit']=='Submit'){
		$media_setting = get_option( 'article-thumbnail' );
		$data =array(
			'thumbnail_size_w'=>$_POST['thumbnail_size_w'],
			'thumbnail_size_h'=>$_POST['thumbnail_size_h'],
			'medium_size_w' =>$_POST['medium_size_w'],
			'medium_size_h' =>$_POST['medium_size_h'],
			'large_size_w' =>$_POST['large_size_w'],
			'large_size_h' =>$_POST['large_size_h']
			);
		if($media_setting){
			update_option( 'article-thumbnail', $data ); 
		} else {
			add_option( 'article-thumbnail', $data, '', 'yes' );
		}
	}

	$thumbnail_setting = get_option( 'article-thumbnail' );

	if($thumbnail_setting['thumbnail_size_w'])
		$small_width =$thumbnail_setting['thumbnail_size_w'];
	else
		$small_width =150;

	if($thumbnail_setting['thumbnail_size_h'])
		$small_height =$thumbnail_setting['thumbnail_size_h'];
	else
		$small_height =150;

	if($thumbnail_setting['medium_size_w'])
		$medium_size_w =$thumbnail_setting['medium_size_w'];
	else
		$medium_size_w = 300;

	if($thumbnail_setting['medium_size_h'])
		$medium_size_h =$thumbnail_setting['medium_size_h'];
	else
		$medium_size_h=300;

	if($thumbnail_setting['large_size_w'])
		$large_size_w =$thumbnail_setting['large_size_w'];
	else
		$large_size_w = 600;

	if($thumbnail_setting['large_size_h'])
		$large_size_h =$thumbnail_setting['large_size_h'];
	else
		$large_size_h=400;


?>
<h1>Step by Step Pro Settings <?php sbs_plugin_get_version();?></h1>
 <div style="margin: 0 auto; padding: 20px 20px 0 0;">
        <ul class="tabs" data-persist="true">
            <li><a href="#view1">General</a></li>
            <li><a href="#view2">Media</a></li>
        </ul>
 <div class="tabcontents">
   <div id="view1">
<div class="wrap">
     <h2 style="margin-bottom:50px;">General Settings</h2>
	 <div style="width:65%;float:left;">
	 <form name="step" id="step" action="" method="post">
		<p style="font-size:14px;width:20%;float:left;"><strong>Save Settings and Guides</strong></p>

		<p style="font-size:14px;width:80%;float:left;">
			<input type="checkbox" name="step_setting" id="step_setting" value="1" <?php echo $checked;?>> Save your settings and existing guides when uninstalling this plugin. Useful when upgrading or re-installing. 
			<br/><br/><strong>Note: </strong>If you deselect this box ALL of your guides will be erased when the plugin is uninstalled.
		</p>

		<p style="clear:both;"></p>
		<p style="font-size:14px;width:20%;float:left;"><strong>Uploader</strong></p>
		<p style="font-size:14px;width:80%;float:left;">
			<input type="checkbox" name="native_upload" id="native_upload" value="1" <?php echo $native_checked;?>> Use native WordPress uploader.
		</p>

		<p style="clear:both;"></p>

		<p style="font-size:14px;width:20%;float:left;"><strong>Light Box</strong></p>
		<p style="font-size:14px;width:80%;float:left;">
			<input type="checkbox" name="guide_lightbox" id="guide_lightbox" value="1" <?php echo $guide_lightbox;?>> Use Light box for images 
		</p>

		<p style="clear:both;"></p>

		<p style="font-size:14px;width:20%;float:left;"><strong>Default Menu</strong></p>
		<p style="font-size:14px;width:80%;float:left;">
			<input type="checkbox" name="cus_menu" id="cus_menu" value="1" <?php echo $cus_menu;?>> Use native WordPress default menu
		</p>
		
		<p style="clear:both;"></p>

		<p style="font-size:14px;width:20%;float:left;"><strong>Guides Directory Name</strong></p>
		<p style="font-size:14px;width:80%;float:left;">
			<input type="text" name="guides_directory_name" id="guides_directory_name" value="<?php if(!empty($guides_directory_name)){ echo $guides_directory_name;} ?>"> Use Guides Directory Name for page title.
		</p>

		<p style="clear:both;"></p>

		<p style="font-size:14px;width:20%;float:left;"><strong>WYSIWYG Editor (beta)</strong></p>
		<p style="font-size:14px;width:80%;float:left;">
			<input type="checkbox" name="wysiwyg_editor" id="wysiwyg_editor" value="1"> Use WYSIWYG WordPress Editor for steps.
		</p>
		<p style="clear:both;"></p>

		<p style="font-size:14px;width:20%;float:left;"><strong>Docs Slug (beta)</strong></p>
		<p style="font-size:14px;width:80%;float:left;">
			<input type="text" name="guides_slug_name" id="guides_slug_name" value="<?php if(!empty($guides_slug_name)){ echo $guides_slug_name;} ?>"> Create your own slug name.
		</p>
		
		<p style="width:80%;float:right">
			<input type="submit" name="save_settings" id="save_settings" value="Save Settings" class="button button-primary button-large">
		</p>

		<p style="clear:both;"></p>
   </form>
   </div>

   <div class="sidebar-container" style="width:30%;float:right; background-color: #FFFFFF;border: 1px solid #E5E5E5;border-radius: 0;font-size: 15px;margin-bottom: 15px;padding: 0;position: relative;text-align: left;">
	<div class="sidebar-content" style="padding:14px;">
		<p><?php _e( 'Tell us what you think. We love feedback.'); ?></p>

		<form target="_blank" method="post" action="https://docs.google.com/forms/d/1td7lEQuD1a1Sb1anCT11_PwJHWIKorGnEuuQ5K80fVA/formResponse" novalidate>
			<p>
				<input type="text" placeholder="Your feedback" class="large-text" name="entry_738345416">
			</p>
			<p>
				<input type="submit" class="button-primary" name="subscribe" value="Send Us Feedback">
			</p>
		</form>
	</div>
</div>

<div style="clear:both;"></div>

 </div>
 </div>
  <div id="view2">
        <div class="wrap">
		 <h2 class="title">Guide Media Setting</h2>
		 <p>The sizes listed below determine the maximum dimensions in pixels to use when adding an image to the Guide Media Library.</p>
		 <div style="width65%;float:left;">
			<form name="frm" id="frm" method="post">
				 <table class="form-table">
					 <tr><th><label>Thumbnail size:</label><br><i>default 150 x 150</i></th>
						 <td>
							<label for="thumbnail_size_w">Width</label>
							<input type="number" class="small-text" value="<?php echo $small_width;?>" id="thumbnail_size_w" min="0" step="1" name="thumbnail_size_w">
							<label for="thumbnail_size_h">Height</label>
							<input type="number" class="small-text" value="<?php echo $small_height;?>" id="thumbnail_size_h" min="0" step="1" name="thumbnail_size_h"><br>
						</td>
					</tr>

					<!-- <tr><th><label>Medium size:</label></th> <td><fieldset><legend class="screen-reader-text"><span>Medium size</span></legend>
						<label for="medium_size_w">Max Width</label>
						<input type="number" class="small-text" value="<?php echo $medium_size_w;?>" id="medium_size_w" min="0" step="1" name="medium_size_w">
						<label for="medium_size_h">Max Height</label>
						<input type="number" class="small-text" value="<?php echo $medium_size_h;?>" id="medium_size_h" min="0" step="1" name="medium_size_h">
						</fieldset></td>
					</tr>

					<tr><th><label>Large size:</label></th> 
						 <td><fieldset><legend class="screen-reader-text"><span>Large size</span></legend>
						<label for="large_size_w">Max Width</label>
						<input type="number" class="small-text" value="<?php echo $large_size_w;?>" id="large_size_w" min="0" step="1" name="large_size_w">
						<label for="large_size_h">Max Height</label>
						<input type="number" class="small-text" value="<?php echo $large_size_h;?>" id="large_size_h" min="0" step="1" name="large_size_h">
						</fieldset></td>
					</tr> -->
					<input type="hidden" class="small-text" value="<?php echo $medium_size_w;?>" id="medium_size_w" min="0" step="1" name="medium_size_w">
					<input type="hidden" class="small-text" value="<?php echo $medium_size_h;?>" id="medium_size_h" min="0" step="1" name="medium_size_h">
					<input type="hidden" class="small-text" value="<?php echo $large_size_w;?>" id="large_size_w" min="0" step="1" name="large_size_w">
					<input type="hidden" class="small-text" value="<?php echo $large_size_h;?>" id="large_size_h" min="0" step="1" name="large_size_h">
		
					<tr><th></th><td><input type="submit" name="submit" value="Submit"  class="button button-primary button-large"></td></tr>		
				</table>
		  </form>			
	</div>  
	
	 <div class="sidebar-container" style="width:30%;float:right; background-color: #FFFFFF;border: 1px solid #E5E5E5;border-radius: 0;font-size: 15px;margin-bottom: 15px;padding: 0;position: relative;text-align: left;">
	<div class="sidebar-content" style="padding:14px;">
		<p><?php _e( 'Tell us what you think. We love feedback.'); ?></p>

		<form target="_blank" method="post" action="https://docs.google.com/forms/d/1td7lEQuD1a1Sb1anCT11_PwJHWIKorGnEuuQ5K80fVA/formResponse" novalidate>
			<p>
				<input type="text" placeholder="Your feedback" class="large-text" name="entry_738345416">
			</p>
			<p>
				<input type="submit" class="button-primary" name="subscribe" value="Send Us Feedback">
			</p>
		</form>
	</div>
</div>

<div style="clear:both;"></div>

   </div>
 </div>
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


function add_article_metabox($post){
	
    add_meta_box('manage_steps', 'Manage Guide Steps', 'steps_meta_box', 'article', 'normal', 'default');
  
    add_meta_box('article_shortcode', 'Copy Shortcode', 'display_short_metabox', 'article', 'side', 'low');
    add_meta_box('guide_option', 'Guide Options', 'display_guide_options', 'article', 'side', 'low');
    
}
function display_short_metabox($post, $args) {
	$steps_meta = get_post_meta($post->ID, 'meta-step', true);
	  if(isset($steps_meta['step']) && count($steps_meta['step'])>0) {
			echo "[display_article id='".$post->ID."' type='article']";
	  }
}
function display_guide_options($post, $args){
	$steps_meta = get_post_meta($post->ID, 'meta-step', true);
	?> <p><span><input  type="checkbox" <?php if(!empty($steps_meta['step_listView'][0]) && $steps_meta['step_listView'][0] == "yes"){ echo "checked='checked'";} ?> size="40" name="step_listView[]" class="step_listView" value="yes" /></span>List View 
				<span style="padding-left:20px"><input <?php if(!empty($steps_meta['step_autonumber'][0]) && $steps_meta['step_autonumber'][0] == "yes"){ echo "checked='checked'";} ?> type="checkbox" value="yes" class="step_listView" size="40" name="step_autonumber[]"></span> Show Number				
				</p><?php 
}


function steps_meta_box($post, $args) { 
	$steps_meta = get_post_meta($post->ID, 'meta-step', true);

	$chek_native_upload_value = get_option( 'native_upload' );
	if(isset($chek_native_upload_value) && $chek_native_upload_value==1)
		$upload_value = $chek_native_upload_value;
	else
		$upload_value =0;


	if(isset($steps_meta['step'])){
		$total_step = count($steps_meta['step']);
	} else {
		$total_step=1;
	}


?>
	<div id="mainstep">
	   <?php if(isset($steps_meta['step'])) { 
		$i = 1;
		
		if(is_array($steps_meta['step']) ) {
		foreach($steps_meta['step'] as $key => $value) {


		?>
			<div id='step<?php echo $i;?>' style="background: -moz-linear-gradient(center top , #F5F5F5, #FCFCFC) repeat scroll 0 0 rgba(0, 0, 0, 0);" class="">
			<div class="postbox closed">
			<div title="Click to toggle" class="handlediv"><br></div>
			<h3 class="hndle" style="background-color:#e9e9e9;"><span>Step <?php echo $i;?></span></h3>
			<div class="inside">

				<div style="position: relative; padding: 10px 10px 20px;">
					<!--<div style="float: left; font-size: 16px;"><?php echo $i; ?></div>-->
					<div style="text-align: right; float: right; width: 72%;"><a href="javascript:void(0);" onclick="return removeDiv('<?php echo 'step'.$i;?>');">- Delete Step</a></div>
				</div>

			
				
				<p>Step Label Text<br> <span><input type="text" size="40" value="<?php echo  $value;?>" name="step[]" id="step"></span></p>
				
				<p>Body<span style="color:red;">*</span><br> <span>
				<?php 
				$step_title = 'step_title'.$i;	
				wp_editor($steps_meta['step_title'][$key],$step_title,$settings=array('textarea_name'=>'step_title[]','media_buttons'=>true,'textarea_rows'=>5) );?>
				<!-- <textarea name="step_title[]" id="step_title" rows="4" cols="40"><?php echo $steps_meta['step_title'][$key];?></textarea> --></span></p>
				
				<p>Note<br> <span><input type="text" size="40" value="<?php echo $steps_meta['note'][$key];?>" name="note[]" id="note"></span></p>

				<?php
				if(isset($steps_meta['step_image'][$key]) && !empty($steps_meta['step_image'][$key])) {
					$image_attributes = wp_get_attachment_image_src( $steps_meta['step_image'][$key],array(100,100) ); 
					$attr = get_the_post_thumbnail($steps_meta['step_image'][$key], 'thumbnail');
				?>
					<p id="gimage<?php echo $i;?>"><img style="vertical-align: middle;" src="<?php echo $image_attributes[0]; ?>" width="<?php echo $image_attributes[1]; ?>" height="<?php echo $image_attributes[2]; ?>">&nbsp;&nbsp;<a href="javascript:void(0);" alt="Remove" title="Remove" onclick="return remove_attachement('<?php echo $steps_meta['step_image'][$key];?>',<?php echo $post->ID;?>,'<?php echo $i;?>')">Remove</a><img id="loader" style="display: none;margin: 0 auto;text-align: center;" src="<?php echo plugins_url()?>/step-by-step/includes/images/loader.gif" /></p>	
				<?php } ?>
								
				<?php 
				$get_native_upload = get_option( 'native_upload' );
				if(isset($get_native_upload) && $get_native_upload=='1'){ 
				?>
					<p for="upload_image">
						<div id="upload_image<?php echo $i;?>" style="margin-bottom:10px;"></div>
						<input id="upload_image_new<?php echo $i;?>" type="hidden" size="36" name="step_image[]" value="" />
						<input id="upload_image_button" class="button" type="button" value="Upload Image"  onclick="native_uploader('upload_image<?php echo $i;?>','<?php echo $i;?>')"/>					
					</p>
				<?php } else { ?>
					<p>Image<br> <span><input type="file" size="60" value="" name="step_image[]" id="step_image"></span></p>	
				<?php } ?>
			</div>
			</div>
			</div>
			<?php
			$i++;
		 }	
		}
		} else {?>
			<div id='step1' style="background: -moz-linear-gradient(center top , #F5F5F5, #FCFCFC) repeat scroll 0 0 rgba(0, 0, 0, 0);" class="meta-box-sortables ui-sortable">
			<div class="postbox">
			<div title="Click to toggle" class="handlediv"><br></div>
			<h3 class="hndle ui-sortable-handle" style="background-color:#e9e9e9;"><span>Step 1</span></h3>
			<div class="inside">

			<p>Step Label Text<br> <span><input type="text" size="40" value="" name="step[]" id="step"></span></p>
			
			<p>Body<span style="color:red;">*</span><br> <span><?php wp_editor( '','step_title1',$settings=array('textarea_name'=>'step_title[]','media_buttons'=>true,'textarea_rows'=>5) ); ?><!-- <textarea name="step_title[]" id="step_title" rows="4" cols="40"></textarea> --></span>
			</p>
			
			<p>Note<br> <span><input type="text" size="40" value="" name="note[]" id="note"></span></p>
			
			<?php 
				$get_native_upload = get_option( 'native_upload' );
				if(isset($get_native_upload) && $get_native_upload=='1'){ 
			?>
				<p for="upload_image">
					<div id="upload_image1" style="margin-bottom:10px;"></div>
					<input id="upload_image_new" type="hidden" size="36" name="step_image[]" value="" />
					<input id="upload_image_button" class="button" type="button" value="Upload Image"  onclick="native_uploader('upload_image1','1')"/>			
				</p>
			<?php } else { ?>
				<p>Image<br> <span><input type="file" size="60" value="" name="step_image[]" id="step_image"></span></p>
			<?php } ?>
</div></div>
		</div>
		<?php } ?>
	</div>
	<div style="clear:both;"></div>
	<div style="padding-bottom:5px;text-align:right;color:#fff;"><a href="javascript:void(0);" onClick="addmorediv('<?php echo $upload_value;?>')">+ Add Step</a></div>
	<input type="hidden" name="step_count" id="step_count" value="<?php echo $total_step;?>">

	<?php if(isset($steps_meta['step']) && count($steps_meta['step'])>0) { ?>

		<div style="background: -moz-linear-gradient(center top , #F5F5F5, #FCFCFC) repeat scroll 0 0 rgba(0, 0, 0, 0);">
		
			<script>
			jQuery('.metabox_submit').click(function(e) {
			    e.preventDefault();
			    jQuery('#publish').click();
			});
			</script>
			<input name="save" type="submit" class="metabox_submit button button-primary" value="Update" />
		
		<p>Get the Short Code</span><br> <span>
		<textarea rows="3" cols="40" readonly>[display_article id='<?php echo $post->ID; ?>' type='article']</textarea>
		</span>
		</div>
		
	<?php } ?>


<script>
jQuery(document).ready( function($) {
    $('.meta-box-sortables').sortable({
        disabled: true
    });

    //$('.postbox .hndle').css('cursor', 'pointer');
});
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


function addmorediv(native_upload)
{

	var cnt = jQuery('#step_count').val();
	var str ='';

	cnt = parseInt(cnt)+1;

	var data = {
		action: 'custom_dynamic_editor',
		native_upload:native_upload,
		cnt:cnt
	};
	
	jQuery.post(ajaxurl, data, function(response) {
		jQuery("#mainstep").append(response);
		var obj=jQuery("#step"+cnt).find("div.postbox");
		jQuery(obj).find(".hndle, .handlediv").click(function(){
			jQuery(obj).toggleClass('closed');
		});
		tinymce.execCommand( 'mceAddEditor', false, 'step_title'+cnt);
		quicktags({id : 'step_title'+cnt});
		tinymce.init({
			selector: 'step_title'+cnt,
			modal: true,
			menubar: false,
			//statusbar: true,
			theme:"modern",
			skin:"lightgray",
			language:"en",
			selector:"#" + 'step_title'+cnt,
			resize:"vertical",
			keep_styles:true,
			wpautop:true,
			indent:true,
			plugins:"charmap,hr,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview",
			toolbar1:"bold,italic,underline,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,undo,redo,link,unlink,fullscreen",
			tabfocus_elements:":prev,:next",
			body_class:"id post-type-post post-status-publish post-format-standard",
		});
        // attempt to fix problem of quicktags toolbar with no buttons. Shout out to http://www.kathyisawesome.com/426/multiple-wordpress-wysiwyg-visual-editors/ Kmb 11162014
		QTags._buttonsInit();
		//tinyMCE.execCommand('mceAddEditor', false, 'step_title'+cnt); 
		
        
	});
		/*var fullId= 'step_title'+cnt;
		 // use wordpress settings
        tinymce.init({
        selector: fullId,

        theme:"modern",
        skin:"lightgray",
        language:"en",
        formats:{
                        alignleft: [
                            {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: {textAlign:'left'}},
                            {selector: 'img,table,dl.wp-caption', classes: 'alignleft'}
                        ],
                        aligncenter: [
                            {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: {textAlign:'center'}},
                            {selector: 'img,table,dl.wp-caption', classes: 'aligncenter'}
                        ],
                        alignright: [
                            {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: {textAlign:'right'}},
                            {selector: 'img,table,dl.wp-caption', classes: 'alignright'}
                        ],
                        strikethrough: {inline: 'del'}
                    },
                    relative_urls:false,
                    remove_script_host:false,
                    convert_urls:false,
                    browser_spellcheck:true,
                    fix_list_elements:true,
                    entities:"38,amp,60,lt,62,gt",
                    entity_encoding:"raw",
                    keep_styles:false,
                    paste_webkit_styles:"font-weight font-style color",
                    preview_styles:"font-family font-size font-weight font-style text-decoration text-transform",
                    wpeditimage_disable_captions:false,
                    wpeditimage_html5_captions:true,
                    plugins:"charmap,hr,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview",
                    selector:"#" + fullId,
                    resize:"vertical",
                    menubar:false,
                    wpautop:true,
                    indent:false,
                    toolbar1:"bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,fullscreen,wp_adv",toolbar2:"formatselect,underline,alignjustify,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help",
                    toolbar3:"",
                    toolbar4:"",
                    tabfocus_elements:":prev,:next",
                    body_class:"id post-type-post post-status-publish post-format-standard",

});


        // this is needed for the editor to initiate
        tinyMCE.execCommand('mceAddEditor', false, fullId); 

    });*/
	jQuery('#step_count').val(cnt);	
}
function removeDiv(divId)
{
	jQuery('#'+divId).remove();
}

</script>
<?php } 
function load_editor($editor_id){
	ob_start();
    wp_editor( '', $editor_id,array('media_buttons' => true,'teeny' => false,'quicktags' =>true,'tinymce'=>true,'textarea_name'=>'step_title[]','textarea_rows'=>5,'class'=>'wp-editor-area quicktags-toolbar'));
    $html1 = ob_get_contents();
    ob_end_clean();
	return $html1;
}

add_action( 'wp_ajax_custom_dynamic_editor', 'add_multiple_editor' );

function add_multiple_editor()
{
	$native_upload = intval( $_POST['native_upload'] );
	$cnt = intval( $_POST['cnt'] );
	$str='';
	$html='';
	$html1='';
	

	$content = '';
	$editor_id = 'step_title'.$cnt;


	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-widget' );
	wp_enqueue_script( 'jquery-ui-mouse' );
	wp_enqueue_script( 'jquery-ui-sortable' );
	

	add_filter( 'meta_content', 'wptexturize'        );
	add_filter( 'meta_content', 'convert_smilies'    );
	add_filter( 'meta_content', 'convert_chars'      );
	add_filter( 'meta_content', 'wpautop'            );
	add_filter( 'meta_content', 'shortcode_unautop'  );
	add_filter( 'meta_content', 'prepend_attachment' );
	add_filter( 'meta_content', 'do_shortcode');
	
	$html1 = load_editor($editor_id);


	//$html1 ='<textarea name="step_title[]" id="step_title" rows="4" cols="40"></textarea>';

	if($native_upload == 1){
		$str ='<p for="upload_image">
		<div id="upload_image'.$cnt.'" style="margin-bottom:10px;"></div>
		<input id="upload_image_new'.$cnt.'" type="hidden"  name="step_image[]" value="" />
		<input id="upload_image_button" class="button" type="button" value="Upload Image" onclick="native_uploader(\'upload_image'.$cnt.'\',\''.$cnt.'\');"></p>';
	} else {
		$str ='<p>Image<br> <span><input type="file" size="60" value="" name="step_image[]" id="step_image"></span></p>';
	}
	
	$html = '<div id="step'.$cnt.'" style="" class="meta-box-sortables ui-sortable"><div class="postbox"><div title="Click to toggle" class="handlediv"><br></div><h3 class="hndle ui-sortable-handle" style="background-color:#e9e9e9;"><span>Step '.$cnt.'</span></h3><div class="inside"><div style="position: relative; padding: 10px 10px 20px;"><div style="text-align: right; float: right; width: 72%;"><a href="javascript:void(0);" onclick="return removeDiv(\'step'.$cnt.'\');">- Delete Step</a></div></div><p>Step Label Text<br> <span><input type="text" size="40" value="" name="step[]" id="step"></span></p><p>Body<span style="color:red;">*</span><br> <span id="messageid'.$cnt.'">'.$html1.'</span></p><p id="noteid'.$cnt.'">Note<br> <span><input type="text" size="40" value="" name="note[]" id="note"></span></p>'.$str.'</div></div></div>';
	echo $html;
	
	exit;
}

add_action( 'wp_ajax_custom_delete_attachement', 'remove_attachement_image' );

function remove_attachement_image() {
	global $wpdb; // this is how you get access to the database

	$attachement_ID = intval( $_POST['attachement_ID'] );
	$post_ID = intval( $_POST['post_ID'] );
	
	if(isset($attachement_ID) && isset($post_ID))
	{
		
		//wp_delete_attachment( $attachement_ID);
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

		// Kyle added to fix "in_array() expects parameter" 2 to be array error on line 228
		if(!is_array($attached_file_array)) { $attached_file_array = array(); }
		

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

if(isset($_POST['step_image']) && is_array($_POST['step_image'])){
	foreach($_POST['step_image'] as $key => $imgvalue){
		if(isset($imgvalue) && $imgvalue !=''){
			global $wpdb;
			
			$filetype = wp_check_filetype( basename( $imgvalue ), null );
			$attach_id = false;
			
			// Finally, run a custom database query to get the attachment ID from the modified attachment URL
			$attach_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wposts.guid = '%s' AND wposts.post_type = 'attachment'", $imgvalue ) );
			
			if(isset($attach_id)){
				// Generate the metadata for the attachment, and update the database record.
				$attach_data = wp_generate_attachment_metadata( $attach_id, $imgvalue );
				wp_update_attachment_metadata( $attach_id, $attach_data );
				$attached_file_array[$key] =  $attach_id;
			}
			
		}
	}
}

	$step='';
	$step_title='';
	$note='';
	$step_listView='';
	$step_autonumber ='';

	
	if(isset($_POST['step']))
		$step=$_POST['step'];
	if(isset($_POST['step_title']))
		$step_title=$_POST['step_title'];
	if(isset($_POST['note']))
		$note=$_POST['note'];
	if(isset($_POST['step_listView']))
		$step_listView=$_POST['step_listView'];
	if(isset($_POST['step_autonumber']))
		$step_autonumber=$_POST['step_autonumber'];

	
	
 
	
	if(isset($_POST['publish']) || isset($_POST['save']))
	{
		$result = array('step'=>$step, 'step_title'=>$step_title,  'step_listView'=>$step_listView,  'step_autonumber'=>$step_autonumber,'note'=>$note,'step_image'=>$attached_file_array);

		
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

		$string ='<div class="fullArticle">';

		$string .= '<h3>'.$post->post_title.'</h3>';
		if(isset($thumb_image_url[0])){
			$string .='<div class="top-content" style="margin-bottom: 40px;">
			<a href="'.$thumb_image_url[0].'" class="thickbox"><img style="float:left;margin:0 10px 0 0;padding-top:7px;" src="'.$thumb_image_url[0].'" width="'.$thumb_image_url[1].'" height="'.$thumb_image_url[2].'"></a>'.$post->post_content.'</div>
			<div style="clear:both;"></div>';
		}else{
			$string .='<div class="top-content">'.$post->post_content.'</div>';
		}

		$total_article = count($article_steps_meta["step"]);

		if(count($article_steps_meta["step"])) {

			$string .='<dl>';

			$num = 1;	
			for($i=0;$i<count($article_steps_meta["step"]);$i++){ 	

				if(isset($article_steps_meta["step_image"][$i])){
					 $kk=wp_get_attachment_image_src( $article_steps_meta["step_image"][$i], 'thumbnail', true );
					 $imgString='<a href="'.$kk[0].'" class="thickbox"><img src="'.$kk[0].'" width="'.$kk[1].'"  height="'.$kk[2].'" ></a>';
				} else {
					 $imgString='';
				}
			
				if(!empty($article_steps_meta["step_listView"][0]) && $article_steps_meta["step_listView"][0] == "yes"){
						if(!empty($article_steps_meta["step_autonumber"][0]) && $article_steps_meta["step_autonumber"][0] == "yes"){
							$string .='<dt>'.$num.'</dt>';
						}
					$string .='<dd class="dd-list-view"><h4 class="display-hlist">'.$article_steps_meta["step"][$i].'</h4>'.str_replace('<a " ','<a  class="thickbox " ',$article_steps_meta["step_title"][$i]).'<br><strong>'.substr($article_steps_meta["note"][$i],0,125).'</strong>';
					$string .='<p>'.$imgString.'</p>';
					$string .='</dd>';

				}else{
					if(!empty($article_steps_meta["step_autonumber"][0]) && $article_steps_meta["step_autonumber"][0] == "yes"){
							$string .='<dt>'.$num.'</dt>';
					}
					$string .='<dd><div class="step-content  grid-container"><div class="step-image  grid-img">'.$imgString.'</div><div class="step-text grid-detail"><h4  class="display-hlist">'.$article_steps_meta["step"][$i].' </h4>'.str_replace('<a " ','<a  class="thickbox"  ',$article_steps_meta["step_title"][$i]).'<br><strong>'.substr($article_steps_meta["note"][$i],0,125).'</strong></div><div style="clear:both;"></div>';
					$string .='</dd>';
				}
				$num++;
			} 

			$string .='</dl>';
		}
		
		return $string .=' </div>';
	}
}
add_shortcode( 'display_article', 'article_func' );
/*Uncomment to use the alternative guide instead of artcle without breaking legacy guides. kmb*/
//add_shortcode( 'display_guide', 'article_func' );

add_action('admin_footer','checked_custom_post_type');

function checked_custom_post_type()
{
	echo '<script>
			jQuery( document ).ready(function() {
				var checked = jQuery("#add-article-hide:checked").length; 
				if (checked == 0) {
					jQuery("#add-article-hide").prop( "checked", true );
					jQuery("#add-article").show();
					jQuery("#add-article-hide").removeClass( "hide-if-js");
				}
			});
		 </script>';
}

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







/**
 * Add column headers to our "All Feeds" CPT page
 * 
 * @since 2.0.0
 */
function gce_add_column_headers( $defaults ) {
		
	$new_columns = array( 
		'cb'           => $defaults['cb'],
		
		'feed-sc'      => __( 'Shortcode', 'gce' )
	
	);

	return array_merge( $defaults, $new_columns );
}
add_filter( 'manage_article_posts_columns', 'gce_add_column_headers' );  
/**
 * Fill out the columns
 * 
 * @since 2.0.0
 */
function gce_column_content( $column_name, $post_ID ) {
	
	switch ( $column_name ) {

	
		case 'feed-sc':
			echo '<code>[display_article id="' . esc_attr( $post_ID ) . '"  type="article"]</code>';
	
			break;

	}
}
add_action( 'manage_article_posts_custom_column', 'gce_column_content', 10, 2 );


?>