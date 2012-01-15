<?php
/*  
Plugin Name: Instagrate to WordPress
Plugin URI: http://www.polevaultweb.com/instagrate-to-wordpress/  
Description: Plugin for automatic posting of Instagram images into a WordPress blog.
Author: polevaultweb 
Version: 1.0
Author URI: http://www.polevaultweb.com/

Copyright 2012  polevaultweb  (email : info@polevaultweb.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

define( 'ITW_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'ITW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ITW_PLUGIN_BASE', plugin_basename( __FILE__ ) );
define( 'ITW_PLUGIN_SETTINGS', 'instagratetowordpress');

define( 'RETURN_URI', plugin_dir_url( __FILE__ ).'php/callback.php');

require_once ITW_PLUGIN_PATH.'php/instagram.php';

if (!class_exists("instagrate_to_wordpress")) {

	
	class instagrate_to_wordpress {
			
		/* Plugin loading method */
		public static function load_plugin() {
			
			//cache fix
			session_cache_limiter( FALSE );
			session_start(); 
			
			//settings menu
			add_action('admin_menu',get_class()  . '::register_settings_menu' );
			//settings link
			add_filter('plugin_action_links', get_class()  . '::register_settings_link', 10, 2 );
			//styles and scripts
			add_action('admin_init', get_class()  . '::register_styles');
			//register the listener function
			add_action( 'pre_get_posts', get_class()  . '::auto_post_images');	
			
		
			
		}
		
		/* Add menu item for plugin to Settings Menu */
		public static function register_settings_menu() {  
   		  			
   			add_options_page( 'Instagrate to WordPress', 'Instagrate to WordPress', 1, ITW_PLUGIN_SETTINGS, get_class() . '::settings_page' );
	  				
		}

		/* Add settings link to Plugin page */
		public static function register_settings_link($links, $file) {  
   		  		
			static $this_plugin;
				if (!$this_plugin) $this_plugin = ITW_PLUGIN_BASE;
				 
				if ($file == $this_plugin){
				$settings_link = '<a href="options-general.php?page='.ITW_PLUGIN_SETTINGS.'">' . __('Settings', ITW_PLUGIN_SETTINGS) . '</a>';
				array_unshift( $links, $settings_link );
			}
			return $links;
			
	  				
		}
		
		/* Register custom stylesheets and script files */
		public static function register_styles() {
		 
			if (isset($_GET['page']) && $_GET['page'] == ITW_PLUGIN_SETTINGS) {
		 
				//register styles
				wp_register_style( 'itw_style', ITW_PLUGIN_URL . 'css/style.css');
				
				//enqueue styles	
				wp_enqueue_style('itw_style' );
				wp_enqueue_style('dashboard');
				//enqueue scripts
				wp_enqueue_script('dashboard');
			
			}
		
		}
		
		/* Register default options for plugin link, author, category, post type */
		public static function set_default_options($lastid) {
			
			
			//update manual last id
			$manuallstid= '';
			$manuallstid= get_option('itw_manuallstid');
			
			if ($manuallstid == '')
			{
				update_option('itw_manuallstid', $lastid);
			}
			
			
			
			$configured= '';
			$configured= get_option('itw_configured');
						
			if ($configured != 'Installed') {
			
				
				
							
				//Set plugin link to true
				update_option('itw_pluginlink', true);
			
				//Set custom post type to post
				//update_option('itw_customposttype', 'post');
			
				//Set author
				$current_user =  wp_get_current_user();
				$username = $current_user->ID;
				
				//print $username;
				update_option('itw_postauthor', $username);
				
				//set cats as earliest cat id
				$args = array(
							'type'                     => 'post',
							'child_of'                 => 0,
							'parent'                   => '',
							'orderby'                  => 'id',
							'order'                    => 'ASC',
							'hide_empty'               => 1,
							'hierarchical'             => 1,
							'exclude'                  => '',
							'include'                  => '',
							'number'                   => 1,
							'taxonomy'                 => 'category',
							'pad_counts'               => false );
	
				$categories = get_categories( $args );
				foreach($categories as $cats) {
				
				$cat = $cats->cat_ID;
										
				}
				update_option('itw_postcats', $cat);
				

			}
		
		}
		
		/* Instagram post feed array */
		public static function get_images() {
		
			if(!$access_token ):
			
				//get current last id
				$manuallstid = get_option('itw_manuallstid');
				//get access token
				$access_token = get_option('itw_accesstoken');
				//get userid
				$userid = get_option('itw_userid');
				
				$instagram = new itw_Instagram(CLIENT_ID, CLIENT_SECRET, $access_token);
				
				$params =  array('min_id' => $manuallstid);
			
				$data = $instagram->get('users/'.$userid.'/media/recent', $params);
				//var_dump($data);
			
			
				//echo $manuallstid;
				$images = array();
				
				if($data->meta->code == 200):
				
				
					foreach($data->data as $item):
												
								$images[] = array(
									"id" => $item->id,
									"title" => (isset($item->caption->text)?filter_var($item->caption->text, FILTER_SANITIZE_STRING):""),
									"image_small" => $item->images->thumbnail->url,
									"image_middle" => $item->images->low_resolution->url,
									"image_large" => $item->images->standard_resolution->url
								);				
				
					endforeach;
				
				endif;
							
				//var_dump($images);			
				return $images;
			
			endif;
	
		}
		
		public static function get_last_id($data) {
		//echo $data[0]["id"];
			//return $data[0]["id"];
		
		}

		
		
		/* Main function to post Instagram images */
		public static function auto_post_images() {
		
			$manuallstid = get_option('itw_manuallstid');
					
			$images = self::get_images();
			
			//get count of array of images
			$count = sizeof($images);
			
			//set counter
			$last_id = 0;
			
			//loop through array to get image data
			for ($i = 0; $i < $count; $i++) {
					
				//Don't include image of $manuallstid
				if ($images[$i]["id"] != $manuallstid) :
					
				//get image variables
				$title = $images[$i]["title"];
				$image = $images[$i]["image_large"];
				
				$last_id = $images[$i]["id"];
				//echo $last_id;
				
				/*
				echo $last_id;
				echo $title;
				echo $image;
				*/
							
				//post new images to wordpress
				self::blog_post($title,$image);

				
				endif;
			
			}
			
			if ($last_id != 0)
			{
				//update last id field in database with last id of image added
								
				//echo '<h1>'.$images[0]["id"].'</h1>';
				update_option('itw_manuallstid', $images[0]["id"]);
			}

			
					
		}
		
		/* Posting to WordPress */
		public static function blog_post($post_title, $post_image) {

			
			$imagesize = get_option('itw_imagesize');
			$imageclass = get_option('itw_imageclass');
			//$customposttype = get_option('itw_customposttype');
			$postcats = get_option('itw_postcats');
			$postauthor = get_option('itw_postauthor');
			$customtitle = get_option('itw_customtitle');
			$customtext = get_option('itw_customtext');
			$pluginlink = get_option('itw_pluginlink');
							
	
			//Image class
			if ($imageclass != '')
			{
				$imageclass = 'class="'.$imageclass.'" ';
			}
			
			//Image size
			if ($imagesize != '')
			{
				$imagesize = 'width="'.$imagesize.'" height="'.$imagesize.'" ';
			}
			
			//Custom Post Title
			if ($customtitle != '' ){
			
				$pos = strpos(strtolower($customtitle),'%%title%%');
				if($pos === false) {
					
					//no %%title%% found so put instagram title after custom title
					$post_title = $customtitle.' '.$post_title;
				
				}
				else {
				 	
				 	//%%title%% found so replace it with instagram title
				 	$post_title = str_replace("%%title%%", $post_title, $customtitle);
				}
		
			}
			
			//Custom Post Text
			
			$post_body = '<a href="'.$post_image.'" title="'.$post_title.'"><img src="'.$post_image.'" '.$imageclass.' alt="'.$post_title.'" '.$imagesize.' /></a>';
			
			if ($customtext != '' ){
			
				$pos = strpos(strtolower($customtext),'%%image%%');
				if($pos === false) {
					
					//no %%image%% found so put instagram title after custom title
					$post_body = $customtext.'<br/>'.$post_body;
				
				}
				else {
				 	
				 	//%%image%% found so replace it with instagram title
				 	$post_body = str_replace("%%image%%", '<br/>'.$post_body.'<br/>', $customtext);
				}
			
			}
			
			//Plugin link credit
			if ($pluginlink == true ) {
			
			
				$post_body  = $post_body.' <br/><small>Posted by <a href="http://wordpress.org/extend/plugins/instagrate-to-wordpress/">Instagrate to WordPress</a></small>';
			
			}
			
			
			// Create post object
		  	$my_post = array(
		     'post_title' => $post_title,
		     'post_content' => $post_body,
		     'post_status' => 'publish',
		     'post_author' => $postauthor,
		     'post_category' => array($postcats)
		 	 );
		
			// Insert the post into the database
		  	wp_insert_post( $my_post );
			
		}	

			
		
		/* Plugin Settings page and settings data */
		public static function settings_page() {
		
			$oldplugin ='instapost-press/instapost-press.php';
			
			if(is_plugin_active($oldplugin))
			{
				$oldplugintest = 1;
			
			}
			else {
							
				$instagram = new itw_Instagram(CLIENT_ID, CLIENT_SECRET,$access_token);
			
				//session_cache_limiter( TRUE );			
				if (isset($_SESSION['access_token'])) {
				
					update_option('itw_accesstoken', $_SESSION['access_token']);
					update_option('itw_username', $_SESSION['username']);
					update_option('itw_userid', $_SESSION['userid']);
				
				
					unset($_SESSION['access_token']);
					unset($_SESSION['username']);
					unset($_SESSION['userid']);
				
				}
				elseif (isset($_SESSION['error_reason'])) {
				
					$msg = 'You did not authorise the plugin to access your Instagram account. Maybe try again - ';
					$msg_class = 'itw_disconnected';
					
					$loginUrl = $instagram->authorizeUrl(REDIRECT_URI.'?return_uri='.htmlentities(RETURN_URI));
					
					
					unset($_SESSION['error_reason']);
					unset($_SESSION['error_description']);
					
					update_option('itw_accesstoken', '');
					update_option('itw_username', '');
					update_option('itw_userid', '');
					update_option('itw_manuallstid', '');
				}
			
				if ($msg_class != 'itw_disconnected')
				{
				
				$access_token = get_option('itw_accesstoken');
				$instagram = new itw_Instagram(CLIENT_ID, CLIENT_SECRET, $access_token);
			
				//echo $access_token;
				
				if(!$access_token){
					// no access token in db
					
					$msg = 'Please login securely to Instagram to authorise the plugin - ';
					$msg_class = 'itw_setup';	
					$loginUrl = $instagram->authorizeUrl(REDIRECT_URI.'?return_uri='.htmlentities(RETURN_URI));
				
				
				} else {   

					//logged in
					try {
					
						$username = get_option('itw_username');
						$userid = get_option('itw_userid');
						$msg = $username;
						$msg_class = 'itw_connected';
						
						
						$feed = $instagram->get('users/'.$userid.'/media/recent');
						
						if($feed->meta->code == 200): 
						//var_dump($feed);
						
							if($_POST['itw_hidden'] == 'Y') {
														
								update_option('itw_configured', 'Installed');
								
								$manuallstid  = $_POST['itw_manuallstid'];
								update_option('itw_manuallstid', $manuallstid);
								
								$imagesize  = $_POST['itw_imagesize'];
								update_option('itw_imagesize', $imagesize);
								
								$imageclass  = $_POST['itw_imageclass'];
								update_option('itw_imageclass', $imageclass);
								
								//$customposttype  = $_POST['itw_customposttype'];
								//update_option('itw_customposttype', $customposttype);
								
								$postcats  = $_POST['itw_postcats'];
								update_option('itw_postcats', $postcats);
								
								$postauthor  = $_POST['itw_postauthor'];
								update_option('itw_postauthor', $postauthor);
								
								$customtitle  = $_POST['itw_customtitle'];
								update_option('itw_customtitle', $customtitle);
								
								$customtext  = $_POST['itw_customtext'];
								update_option('itw_customtext', $customtext);
								
								
								$pluginlink  = $_POST['itw_pluginlink'];
								update_option('itw_pluginlink', $pluginlink);
									
								?>
								
								<div class="itw_saved"><p><?php _e('Plugin settings saved!' ); ?></p></div>
								<div class="clear"></div>
								<?php
							} else {
									
									
								//set defaults if need
								$lastid = self::get_last_id($feed);
								self::set_default_options($lastid);
							
								$manuallstid = get_option('itw_manuallstid');
								//echo $manuallstid;
								$imagesize = get_option('itw_imagesize');
								$imageclass = get_option('itw_imageclass');
								//$customposttype = get_option('itw_customposttype');
								$postcats = get_option('itw_postcats');
								$postauthor = get_option('itw_postauthor');
								$customtitle = get_option('itw_customtitle');
								$customtext = get_option('itw_customtext');
								$pluginlink = get_option('itw_pluginlink');
								
							}
							
						else:
						
							$msg = 'Error: '.$feed->meta->error_type.' - '.$feed->meta->error_message;
							$msg_class = 'itw_disconnected';
							$loginUrl = 'hide';						
						
						endif;
						
						
						
						//update_option('itw_configured', '');
					
						
						} catch(InstagramApiError $e) {
						
						update_option('itw_accesstoken', '');
						update_option('itw_username', '');
						update_option('itw_userid', '');
						update_option('itw_manuallstid', '');
					
						$msg = 'The Instagram Authorisation token has expired - ';
						$msg_class = 'itw_disconnected';
						$loginUrl = $instagram->authorizeUrl(REDIRECT_URI.'?return_uri='.htmlentities(RETURN_URI));
						//die($e->getMessage());
					}
				}
				}
			
			}
			
			
		
			
			?>
			
			<!-- BEGIN Wrap -->
			<div class="wrap">
				<div class="h2_left">
					<h2 class="instagrate_logo">Instagrate to WordPress</h2>
				</div>
				<?php if(isset($oldplugintest)): ?>
					<div class="clear"></div>
					<div class="itw_issue">
						<p>
						This plugin is a newer version of <b>InstaPost Press</b> which has been discontinued.</p>
						<p>Please deactivate and delete <b>InstaPost Press</b> <a href="<?php echo pluginsURL().'#instapost-press' ?>">here</a>.
						</p>
						<p>	Once done you can configure the settings of this plugin and begin to use it!				
						</p>
					</div>
				
				<?php else: ?>
				<?php if(isset($loginUrl)): ?>
				
				<div class="clear"></div>
				<div class="<?php echo $msg_class ?>">
				<p>
				<?php echo $msg ?>
				<?php if ($loginUrl != 'hide'): ?>
				<a href="<?php echo $loginUrl; ?>">Log in</a>
				<?php endif; ?>
				</p></div>
				
				<?php else: ?>
					<div class="loggedin">
						<div class="itw_connected">
							<p>
								Connected to Instagram as <b><?php echo $msg; ?></b>
							</p>
						</div>
					</div>
					<div class="clear"></div>
				
					<!-- BEGIN ipp_content_left -->
					<div id="ipp_content_left" class="postbox-container">
						
						<!-- BEGIN metabox-holder -->
						<div class="metabox-holder">
						
							<!-- BEGIN meta-box-sortables ui-sortable -->
							<div class="meta-box-sortables ui-sortable">
							
				
								<form name="itw_form" method="post" autocomplete="off" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
								<input type="hidden" name="itw_hidden" value="Y">
						
								
								<!-- BEGIN wordpress -->
								<div id="wordpress" class="postbox">
								
									<div class="handlediv" title="Click to toggle">
										<br>
									</div>
						
									<?php echo "<h3 class='hndle'><span>" . __( 'Settings', 'itw_trdom' ) . "</span></h3>"; ?>
									
									<!-- BEGIN inside -->
									<div class="inside">
										<h4>Last Instagram Image</h4>
										
										<p class="itw_info">All Images after this image will get auto posted. Select to retrospectively post images from your feed.</p>
										
										<p><label class="textinput">Last Image:</label>
										<?php 
										
										if (isset($_POST['itw_manuallstid']))
										{
										$manuallstid = $_POST['itw_manuallstid'];
										}
										
										foreach($feed->data as $item):
										
										$title = (isset($item->caption->text)?filter_var($item->caption->text, FILTER_SANITIZE_STRING):"");
										$title = truncateString($title,80);
										$id = $item->id;
										
										$selected = '';					
										
										if ( $manuallstid == $id ) {
											$selected = "selected='selected'"; 
										  }
										 								
										$options[] = "<option value='{$id}' $selected >{$title}</option>";
										
										endforeach; ?>
										
										<select name="itw_manuallstid" class="img_select">
										<?php echo implode("\n", $options); ?>
										</select>
										</p>
										<h4>WordPress Post</h4>
										
										<p class="itw_info">Default WordPress post settings</p>
										
										<p><label class="textinput">Image Size:</label><input type="text" name="itw_imagesize" value="<?php echo $imagesize; ?>" ></p>
										
										<p><label class="textinput">Image CSS Class:</label><input type="text" name="itw_imageclass" value="<?php echo $imageclass; ?>" ></p>
										
										<!--<p><label class="textinput">Post Type:</label>-->
										<?php 
										/*
										$args=array(
													  'public'   => true,
													  '_builtin' => true
													); 
										$output = 'names'; 
										$operator = 'and';
										$post_types=get_post_types($args,$output,$operator); 
										
										if (isset($_POST['itw_customposttype']))
										{
										$customposttype = $_POST['itw_customposttype'];
										}
										
										foreach($post_types as $post_type):
										
										$selected = '';
										if ( $customposttype  == $post_type ) {
											$selected = "selected='selected'"; 
										  }
										 								
										$options_pt[] = "<option value='{$post_type}' $selected >{$post_type}</option>";
										
										endforeach;
										*/
										?>
										<!--
										<select name="itw_customposttype">
										<?php //echo implode("\n", $options_pt); ?>
										</select>
										</p>
										-->
						
										<p><label class="textinput">Post Category:</label>
						
										 <?php $args = array(
										
										
										'selected'                => $postcats,
										'include_selected'        => true,
										'name'                    => 'itw_postcats'
										);
										
										wp_dropdown_categories( $args ); ?> 
										</p>
										<p><label class="textinput">Post Author:</label>
										<?php $args = array(
										
										
										'selected'                => $postauthor,
										'include_selected'        => true,
										'name'                    => 'itw_postauthor'
										
										); 
										 wp_dropdown_users( $args ); ?> </p>
										 
										 <p class="itw_info">If the below custom text fields are left blank, only the Instagram text and image will be used in your post. If you enter text it will appear before the title and image. To position the Instagram data with your custom text use the syntax %%title%% and %%image%%</p>
										<p><label class="textinput">Custom Title Text:</label><input type="text" class="body_title" name="itw_customtitle" value="<?php echo $customtitle; ?>" > <small>eg. %%title%% - from Instagram</small></p>
										
										<p><label class="textinput">Custom Body Text:</label><textarea class="body_text" name="itw_customtext" ><?php echo $customtext; ?></textarea> <small>eg. Check out this new image %%image%% from Instagram</small></p>
										 
										<h4>Plugin Link</h4>
									
										<p class="itw_info">This will place a small link for the plugin at the bottom of the post content, eg. <small>Posted by <a href="http://wordpress.org/extend/plugins/instagrate-to-wordpress/">Instagrate to WordPress</a></small> </p>
										<p>
										
										
										<input type="checkbox" name="itw_pluginlink" <?php if ($pluginlink ==true) { echo 'checked="checked"'; } ?> /> Show plugin link 
										 </p>
										<p>
										</p>
									
										
											<p class="submit">
								<input type="submit" class="button-primary" name="Submit" value="<?php _e('Update Options', 'ipp_trdom' ) ?>" />
							
								</p>
								</form>
										
										
						 			<!-- END inside -->
						 			</div>
						
								<!-- END wordpress -->
								</div>
								
														
							
								
							<!-- END meta-box-sortables ui-sortable -->
							</div>		
					
						<!-- END metabox-holder -->
						</div>
				
					<!-- END ipp_content_left -->
					</div>
					
					<!-- BEGIN ipp_content_right -->
					<div id="ipp_content_right" class="postbox-container">
					
						<!-- BEGIN metabox-holder -->
						<div class="metabox-holder">	
						
							<!-- BEGIN meta-box-sortables ui-sortable -->
							<div class="meta-box-sortables ui-sortable">
							
								<!-- BEGIN images -->
								<div id="images" class="postbox">
								
									<div class="handlediv" title="Click to toggle">
									<br>
									</div>
										
									<h3 class='hndle'><span>Instagram Feed -<small> Most recent at top</small></span></h3>
										
										<!-- BEGIN inside -->
										<div class="inside">
											
											<?php foreach($feed->data as $item): ?>
											<?php $title = (isset($item->caption->text)?filter_var($item->caption->text, FILTER_SANITIZE_STRING):"");
												  $title = truncateString($title,80);
										
										?>
												
												<div class="image_left">
													<a class="feed_image" href="#">
														<span class="overlay">
															<span class="caption">
																<?php echo $title ?><br/>
																
															</span>
														</span>
														<img src="<?php echo $item->images->thumbnail->url; ?>" alt="<?php echo $title ?>" /><br />
													</a>
												</div>
												
											<?php endforeach; ?>
											
											<div class="clear"></div>
										<!-- END inside -->
										</div>
										
								<!-- END images -->	
								</div>
								
							<!-- END meta-box-sortables ui-sortable -->
							</div>	
						
						<!-- END metabox-holder -->
						</div>
						
					<!-- END ipp_content_right -->	
					</div>

					
					
				<?php endif; ?>
				
				
				
				<div class="clear"></div>
				<!-- BEGIN Footer -->
				<div id="itw_footer">
				
				
					<div id="links">
						We hope you enjoy the plugin. Any issues please contact us -  		
						<a href="mailto:info@polevaultweb.com">Contact</a> |
						<a title="Follow on Twitter" href="http://twitter.com/#!/polevaultweb">@polevaultweb</a> |
						<a href="http://www.polevaultweb.com/instapost-press/">Plugin Site</a> |
						<a href="http://led24.de/iconset/">16px Icons</a>
					</div>
					
					<div id="pvw">
					
						<a id="logo" href="http://www.polevaultweb.com/" title="Plugin by Polevaultweb" target="_blank"><img src="<?php echo plugins_url('',__FILE__); ?>/images/polevaultweb_logo.png" alt="polevaultweb logo" width="120" /></a>
					
					</div>
				
	
				
				</div>
				<!-- END Footer -->
				<div class="clear"></div>
				
				<?php endif; ?>
			
			<!-- END Wrap -->
			</div>
			<?php
			
				
		}
		
	}
	
}

if (class_exists("instagrate_to_wordpress")) {

	// Load plugin
	instagrate_to_wordpress::load_plugin();
	
}

?>