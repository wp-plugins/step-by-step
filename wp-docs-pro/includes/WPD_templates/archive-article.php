<?php
/**
 * The Template for displaying all single docs in an index
 */
?>
<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?> style="margin-top:0px !important;">
<!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<link rel='stylesheet' id='TOC-style-css'  href='<?php echo  plugin_dir_url( __FILE__ ) ; ?>../../style/toc_style.css' type='text/css' media='all' />
	<link rel='stylesheet' id='esig-overall-style-css'  href='<?php echo  plugin_dir_url( __FILE__ ) ; ?>../../style/style.css' type='text/css' media='all' />
	<link rel='stylesheet' id='standalone-style-css'  href='<?php echo  plugin_dir_url( __FILE__ ) ; ?>../../style/standalone_style.css' type='text/css' media='all' />
	<!--[if lt IE 9]>
	<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js"></script>
	<![endif]-->
	<?php wp_head(); ?>	
</head>
<?php echo " wpDocs [knowledge base index]" ;?>
<!-- added the header section -->
<body class="single single-article logged-in admin-bar no-customize-support masthead-fixed full-width singular">
<div role="navigation" class="signer-header">
	<div class="container">
		<div class="nav-menu"><ul style="float: left; word-wrap: break-word;width: 500px! important;margin-top: -5px;"><li>
		<?php 
			$guides_directory_name = get_option( 'guides_directory_name' );
			if(!empty($guides_directory_name) && $guides_directory_name!=''){
				//the_title( '<span class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></span>' );
				echo $guides_directory_name;
			} 
		?>
		</li></ul></div>
		<?php 
		$cus_menu = get_option('cus_menu' );
		if(isset($cus_menu) && $cus_menu=='1'){
			wp_nav_menu( array( 'theme_location' => 'primary', 'menu_class' => 'nav-menu' ) );
		}
		?>
		
	</div>
</div>

<!-- end header section -->

<!-- kmb added the sidebar section -->
<?php if ( is_active_sidebar( 'guide_menu_widget' ) ) { ?>
<div class="sidebar-outer-container">
 <div id="tertiary" class="sidebar-container" role="complementary">
	<div class="sidebar-inner">
		<div class="">
			<?php dynamic_sidebar( 'guide_menu_widget' ); ?>
		</div>
	</div>
 </div> 
</div>
<?php } ?>
<!-- kmb end sidebar section -->

<!-- added the middle container section -->

<div class="container first-page doc_page">

<!-- added the middle container section -->

	<div class="wrap" role="main">
	<?php while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" class="article type-article status-publish has-post-thumbnail">
			<div class="entry-content" style=" border: medium none !important;margin: 0 !important;max-width: 100% !important;padding: 0 !important;width: 100% !important;">
				<?php
				$thumb_image_url = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'medium');
				$article_steps_meta = get_post_meta($post->ID, 'meta-step', true);
				$image_container='';
				
				$guide_lightbox = get_option( 'guide_lightbox' );
				if(isset($guide_lightbox) && $guide_lightbox=='1'){
					$light_box_class = 'class="thickbox"';
					$light_box_class1 = 'class="thickbox"';
				} else {
					$light_box_class ='';
					$light_box_class1 ='';
				}


				if ( has_post_thumbnail() ) {
					$image_container ='<span><a href="'.$thumb_image_url[0].'" '.$light_box_class.'>'.get_the_post_thumbnail( $post->ID,'medium').'</a></span>';
				}

				$string ='<div class="document-sign-page">
						<p class="doc_title">'.get_the_title($post->ID).'</p>
						<p>'.$image_container.get_the_content($post->ID).'</p>
				</div>
				<div style="clear:both;"></div>';

				$string .='<table cellspacing="5" cellpadding="5" border="0" >';

				if(!empty($article_steps_meta["step_listView"][0]) && $article_steps_meta["step_listView"][0] == "yes"){
					$table_style = 'style="border: 0 none;"';
					$image_style='';
				} else {
					$table_style='';
					$image_style='style="vertical-align:bottom;"';
				}
			    
				$total_article = count($article_steps_meta["step"]);
				if(count($article_steps_meta["step"])) {
					$string .='<tr><td colspan="2"><span class="doc_title">Guide Step</span></td></tr><tr><td colspan="2"><table cellspacing="5" cellpadding="5" border="0" '.$table_style.'><tr>';

				for($i=0;$i<count($article_steps_meta["step"]);$i++){ 	
					if(isset($article_steps_meta["step_image"][$i])){
						 $kk=wp_get_attachment_image_src( $article_steps_meta["step_image"][$i], 'article-thumbnails', true );
						 $imgString='<a href="'.$kk[0].'" '.$light_box_class1.'><img '.$image_style.' src="'.$kk[0].'" width="'.$kk[1].'"  height="'.$kk[2].'" ></a>';				
					} else {
						$imgString='';
					}
			
				if(!empty($article_steps_meta["step_listView"][0]) && $article_steps_meta["step_listView"][0] == "yes"){
					if($i == 0){ $string .='<td width="100%" valign="top" style="border: 0 none;">';}


					$string .='<div class="document-sign-page" style="border-bottom: 1px solid #dcdcdc;display: inline-block;padding-bottom: 17px;position: relative;width: 100%;">
								<span style="width:35%;">'.$imgString.'</span><span style="float: left;width: 60%;"><strong>'.$article_steps_meta["step"][$i].' </strong><br><br>'.$article_steps_meta["step_title"][$i].'<br><br><strong>'.substr($article_steps_meta["note"][$i],0,125).'</strong></span>
				    </div><div style="clear:both;"></div>';

					if($i == $total_article-1){ $string .='</td>';}

				}else{
					
					$string .='<td width="33%" valign="top">' .$imgString.'<br><br><strong>'.$article_steps_meta["step"][$i].' </strong><br><br>'.$article_steps_meta["step_title"][$i].'<br><br><strong>'.substr($article_steps_meta["note"][$i],0,125).'</strong></td>';
									
					if(($i+1)%3==0) {$string .='</tr><tr>';}
				}
			} 

			$string .='</tr></table></td></tr></table>';
		}
		
		echo $string .=' </div>';
		?>
		</article><!-- #post -->
		<?php endwhile; ?>

	</div><!-- #content -->
</div><!-- #primary --> 

<!-- end middle container section -->
<?php
function add_themescript(){
    if(!is_admin()){
    wp_enqueue_script('jquery');
    wp_enqueue_script('thickbox',null,array('jquery'));
    wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');
    }
     
}
add_action('init','add_themescript');
?>

<div class="footer-agree loading" id="esig-footer">
	<div class="container">
		<div class="navbar-header agree-container">
			<?php wp_footer(); ?>			
		</div>

		<div class="nav navbar-nav navbar-right footer-btn">
			
		</div>
	</div>
</div>
</body>
</html>