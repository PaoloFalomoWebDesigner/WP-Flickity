<?php
/**
 * Plugin Name: WP Flickty
 * Plugin URI: http://dev.altercode.company/wordpress-plugins/plugins/wp-flickity/
 * Description: Wordpress Flickity Plugin
 * Version: 0.3
 * Author: PaoloFalomo
 * Author URI: http://www.paolofalomo.it/
 * Requires at least: 3.5.1
 * Tested up to: 4.4.2
 *
 * Text Domain: wp-flickity
 * Domain Path: /languages/
 *
 * CHANGELOG (last update at 2016-02-26 11:20:49 UTC):
 * @author PaoloFalomo
 * @version  0.3
 *           - (WP Repository) Credits Updated with a dedicated section
 *           - (WP Repository) Header Image Added
 * @version  0.2
 *           - Tested on 4.4.2
 * @version  0.1 2016-01-25 18:01:31 UTC
 *           - Initial Realease
 *           - Admin Page
 *           - Main Shortcode
 */


/*=========================== MAIN DEFINITIONS ===========================*/
/**
 * @since 0.1
 */
define('WP_FLICKITY_NAME','WP Flickity');
define('WP_FLICKITY_DOMAIN','wp-flickity');
define('WP_FLICKITY_MENUPOSITION',1);
define('WP_FLICKITY_UNIQUE_IDENTIFIER','wpflkty_');

global $wpdb;
$flickity_db_charset_collate = $wpdb->get_charset_collate();
$wp_flickity_db_version = '1.2';
$installed_ver = get_option( "wp_flickity_db_version" );
$wp_flickity_table_name = $wpdb->prefix . 'wp_flickity';

if ( $installed_ver != $wp_flickity_db_version ) {

	$sql = "CREATE TABLE $wp_flickity_table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		created_on datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		name tinytext NOT NULL,
		flickity_metadata MEDIUMBLOB DEFAULT '' NOT NULL,
		UNIQUE KEY id (id)
	) $flickity_db_charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	update_option( "wp_flickity_db_version", $wp_flickity_db_version );
}

/*==================================================================================*/
/**
 * PLUGIN INITIALIZER
 * @since  0.1 
 *         Added an Admin Menu Page
 */
function wp_flickity_menu() {
	//MENU PAGE
	add_menu_page( WP_FLICKITY_NAME, WP_FLICKITY_NAME, 'read', WP_FLICKITY_DOMAIN, 'wp_flickity_html_admin', 'dashicons-images-alt', WP_FLICKITY_MENUPOSITION );
}
add_action('admin_menu', 'wp_flickity_menu');


/**
 * ADMIN PAGE HTML
 * @return echo/html/text
 * @uses  definitions,wordpress,html
 */
function wp_flickity_html_admin(){
	global $wpdb,$wp_flickity_table_name;
	$wpflickity_subpage = isset($_GET['wp-flickity-page'])?$_GET['wp-flickity-page']:"main";
	$current_page_class = 'current-flickity-page';
	?>
	<style type="text/css" media="screen">
	/**
	 * TEMP CSS ZONE
	 * Next realease could have more cleaning code at backend too
	 */
		@import url(https://fonts.googleapis.com/css?family=Raleway:400,700,900);
		.wp-flickity-menu {
		    font-family: "Raleway";
		    font-weight: 900;
		    border-bottom: 10px solid rgba(0, 0, 0, 0.1);
		}

		.wp-flickity-menu li a,.wp-flickity-menu li a:focus {
		    background-color: white;
		    padding: 0px 20px;
		    text-decoration: none;
		    line-height: inherit;
		    outline: 0!important;
		    overflow: hidden;
		    /* display: inline-block; */
		    text-align: center;
		    /* vertical-align: middle; */
		    font-size: 15px;
		    width: auto;
		    float: left;
		    /* box-sizing: border-box; */
		}

		.wp-flickity-menu li {
		    display: block;
		    overflow: hidden;
		    height: 50px;
		    line-height: 51px;
		    text-align: center;
		    float: left;
		    width: auto;
		}

		.wp-flickity-menu ul {
		    overflow: hidden;
		}

		.wp-flickity-menu li a > span {
		    line-height: inherit;
		    font-size: inherit;
		    display: inline-block;
		    height: auto;
		    width: auto;
		    /* float: left; */
		    /* overflow: hidden; */
		}

		.wp-flickity-menu li a.current-flickity-page {
		    background-color: #8C8;
		    color: white;
		}

		.wp-flickity-initial-message {
		    background-color: rgb(255, 255, 255);
		    color: #565656;
		    text-align: center;
		    padding: 20px;
		    margin-top: 10px;
		}

		.wp-flickity-initial-message p {
		    font-size: 28px;
		    font-style: italic;
		    font-family: "Raleway";
		}

		.wp-flickity-initial-message p a {
		    color: #88CC88;
		    text-decoration: none;
		}

		.wp-flickity-menu li a:hover,
		.wp-flickity-menu li a.current-flickity-page {
		    box-shadow: 0px -5px 0px rgba(0, 0, 0, 0.25) inset;
		}
		.wp-flickity-pagecontent input#submit {
		    border: 0;
		    box-shadow: none;
		    border-radius: 0;
		    background-color: #88CC88;
		    text-shadow: none;
		}
		table.table-of-flickities {
		    background-color: white;
		    width: 100%;
		    table-layout: fixed;
		}
		table.table-of-flickities td,
		table.table-of-flickities th {
		    text-align: left;
		    border: 10px solid transparent;
		}
		a.flickity-edit-link {
		    background-color: #88CC88;
		    color: white;
		    text-transform: uppercase;
		    border: 10px solid #88CC88;
		    font-size: 12px;
		    display: inline-block;
		    font-weight: bold;
		    text-decoration: none;
		}
		ul.editing {
		    margin-top: 15px;
		    margin-bottom: 15px;
		}

		.wp-flickity-wrapper ul.editing {
		    opacity: 0.5;
		    float: left;
		    transition: 0.5s ease-in-out;
		}

		.wp-flickity-wrapper ul.editing:hover {
		    opacity: 1;
		}

		.wp-flickity-menu h2 > em {
		    color: #8C8;
		    font-weight: 700;
		}

		.wp-flickity-menu h2 {
		    color: #383838;
		    float: left;
		    width: auto;
		    display: block;
		    font-weight: 100;
		    margin: 15px 0px;
		    line-height: 20px;
		    background-color: white;
		    padding: 15px 30px;
		    margin-left: 12px;
		}

		.wp-flickity-menu {
		    overflow: hidden;
		}

		.wp-flickity-menu li {
		    margin-bottom: 0px!important;
		}
		div#flickity-images-wrapper {
		    float: left;
		}

		.flickity-slides {
		    width: 100%;
		    float: left;
		    margin-bottom: 20px;
		}

		button.upload-custom-img {
		    height: 100px;
		    width: 100px;
		    background-color: rgba(136, 204, 136, 0.42);
		    border: 0;
		    outline: 0!important;
		    cursor: pointer;
		    font-weight: bold;
		    font-family: "Raleway";
		    font-size: 9px;
		    padding: 15px;
		}

		div#flickity-images-wrapper > img {
		    width: 100px;
		    height: 100px;
		    float: left;
		}
		.wp-flickity-menu h3 {
		    color: #FFFFFF;
		    float: right;
		    width: auto;
		    display: block;
		    font-weight: 100;
		    margin: 15px 0px;
		    line-height: 20px;
		    background-color: #88CC88;
		    padding: 15px 30px;
		    margin-left: 12px;
		    font-family: "Open Sans";
		}
	</style>
	<div class="wrap">
		<div class="wp-flickity-wrapper">
			<div class="wp-flickity-menu">
				<ul class="<?=($wpflickity_subpage=="edit")?'editing':''?>">
					<li>
						<a class="<?=($wpflickity_subpage=="main")?$current_page_class:''?>" href="<?=admin_url( 'admin.php?page=wp-flickity') ?>">Flickity</a>
					</li>
					<li>
						<a class="<?=($wpflickity_subpage=="create-new")?$current_page_class:''?>" href="<?=admin_url( 'admin.php?page=wp-flickity&wp-flickity-page=create-new') ?>"><span class="dashicons dashicons-plus"></span>Add New<span class="dashicons dashicons-plus"></span></a>
					</li>
				</ul>
				<?php
				if($wpflickity_subpage=="edit"){
					$flickity_id = intval($_GET['flickity-id']);
					$slider_title = $wpdb->get_var("SELECT name FROM $wp_flickity_table_name WHERE id='".$flickity_id."'");
					?>
					<h2>Editing Slider: <em><?=$slider_title?></em></h2>
					<h3>[wp_flickity id="<?=$flickity_id?>"]</h3>
					<?
				}				
				?>

			</div>
			<!-- PLUGIN CONTENT -->
			<div class="wp-flickity-pagecontent">
				<?php if($wpflickity_subpage=="main"): ?>
					<?php
					$flickity_count = intval($wpdb->get_var( "SELECT COUNT(*) FROM $wp_flickity_table_name" ));
					if($flickity_count==0){
						?>
						<div class="wp-flickity-initial-message">
							<p>
								No Flickities found.<br>
								Let's start <a href="<?=admin_url( 'admin.php?page=wp-flickity&wp-flickity-page=create-new')?>">adding one</a>!
							</p>
						</div>
						<?
					}else{
						$myflickities = $wpdb->get_results( "SELECT id, name FROM $wp_flickity_table_name" );
						?>
						<table class="table-of-flickities">
							<thead>
								<tr>
									<th width="5">#</th>
									<th width="35">Name</th>
									<th width="60">Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								foreach ($myflickities as $row => $flickity_data) {?>
									<tr>
										<td><?=$flickity_data->id?></td>
										<td><?=$flickity_data->name?></td>
										<td><a class="flickity-edit-link" href="<?=admin_url( 'admin.php?page=wp-flickity&wp-flickity-page=edit&flickity-id='.$flickity_data->id)?>">Edit</a></td>
									</tr>
								<? }
								?>								
							</tbody>
						</table>
						<?
					}
				?>
				<?php elseif($wpflickity_subpage=="create-new"): ?>
					<?php
					if(isset($_POST['wp_flickity_slider_name']) and $_POST['wp_flickity_slider_name']!=""){
						$wpdb->insert(
							$wp_flickity_table_name,
							array(
								'name' => $_POST['wp_flickity_slider_name'],
							)
						);	
					}					
					?>
				 	<form method="post">
						<table class="form-table">
						  <tr valign="top">
						  <th scope="row">Flickity Slider Name</th>
						  <td><input type="text" name="wp_flickity_slider_name"/></td>
						  </tr>
						</table>
						<?php submit_button(); ?>
				  	</form>
				<?php elseif($wpflickity_subpage=="edit"): ?>
					<?php
					if(isset($_POST['flickity_metadata'])){
						$wpdb->update( 
							$wp_flickity_table_name, 
							array( 
								'flickity_metadata' => serialize($_POST['flickity_metadata'])
							), 
							array( 'id' => $flickity_id )
						);
					}
					?>
					<div id="flickity_slider_configurator">
						<div class="flickity-slides">
							<div id="flickity-images-wrapper">
								
							</div>
							<button class="upload-custom-img">ADD SLIDE/IMAGES</button>
						</div>
					</div>
					<form method="post">
						<?php
						$flickity_metadata = $wpdb->get_var( "SELECT flickity_metadata FROM $wp_flickity_table_name WHERE id='$flickity_id'" );
						$flickity_metadata = unserialize($flickity_metadata);
						?>
						<input type="hidden" id="flickities-images" name="flickity_metadata[images_ids]" value="<?=$flickity_metadata['images_ids']?>"/>
						<?php submit_button(); ?>
				  	</form>
					<script language="JavaScript">
					function images_metadata_to_viewport(){
						var images_ids = jQuery('#flickities-images').val().split(',');
						console.log(images_ids);
						if(images_ids[0]!=""){
							jQuery.ajax({
								url: 'admin-ajax.php',
								type: 'POST',
								data: {
									'action': 'flickity_ajax',
									'imagesids': images_ids
								},
							})
							.done(function(response) {
								//console.log(response);
								jQuery('#flickity-images-wrapper').html('');
								for(image_id in response){
									jQuery('#flickity-images-wrapper').prepend('<img src="'+response[image_id].thumbnail[0]+'">');
									
								}
							})
							.fail(function(err) {
								//console.log("error");
								//console.log(err);
							})
							.always(function() {
								console.log("complete");
							});	
						}	
					}
					jQuery(document).ready(function($) {
						$(window).load(function() {
							images_metadata_to_viewport();
						});
					});
					var file_frame;
					  jQuery('.upload-custom-img').live('click', function( event ){
					    event.preventDefault();
					    // If the media frame already exists, reopen it.
					    if ( file_frame ) {
					      file_frame.open();
					      return;
					    } 
					    // Create the media frame.
					    file_frame = wp.media.frames.file_frame = wp.media({
					      title: jQuery( this ).data( 'uploader_title' ),
					      button: {
					        text: jQuery( this ).data( 'uploader_button_text' ),
					      },
					      multiple: true  // Set to true to allow multiple files to be selected
					    });
					    // When an image is selected, run a callback.
					    file_frame.on( 'select', function() {
					      // We set multiple to false so only get one image from the uploader
					      	var selection = file_frame.state().get('selection');
					      	var images_ids = [];
						    selection.map( function( attachment ) {
						      attachment = attachment.toJSON();
						      images_ids.push(attachment.id);
						      // Do something with attachment.id and/or attachment.url here
						    });
						    jQuery('#flickities-images').val(images_ids.join(','));
						    images_metadata_to_viewport();
					      // Restore the main post ID
					      //wp.media.model.settings.post.id = wp_media_post_id;
					    });
					    // Finally, open the modal
					    file_frame.open();
					  });
					  
					  // Restore the main ID when the add media button is pressed
					  jQuery('a.add_media').on('click', function() {
					    //wp.media.model.settings.post.id = wp_media_post_id;
					  });
					</script>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}


/**
 * Checking the db if changed on plugin update (option)
 */
function wp_flickity_check_db() {
    global $wp_flickity_db_version;
    if ( get_site_option( 'wp_flickity_db_version' ) != $wp_flickity_db_version ) {
        wp_flickity_install();
    }
}
add_action( 'plugins_loaded', 'wp_flickity_check_db' );


/**
 * wp_flickity_install 
 * @since  0.1
 * @uses   register_activation_hook,dbDelta
 */
function wp_flickity_install() {
	global 	$wpdb,
			$wp_flickity_db_version,
			$wp_flickity_table_name,
			$flickity_db_charset_collate;

	$sql = "CREATE TABLE $wp_flickity_table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		created_on datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		name tinytext NOT NULL,
		flickity_metadata MEDIUMBLOB DEFAULT '' NOT NULL,
		UNIQUE KEY id (id)
	) $flickity_db_charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'wp_flickity_db_version', $wp_flickity_db_version );
}
register_activation_hook( __FILE__, 'wp_flickity_install' );


/**
 * FLICKITY AJAX BACKEND
 * @since  0.1
 * @uses  ajax,json,POST,wp_get_attachment_image_src,wp_die
 */
function flickity_ajax_callback() {
	$images_ids = $_POST['imagesids'];
	$response = array();
	foreach ($images_ids as $id) {
			$response[$id] = array(
			'full' => wp_get_attachment_image_src($id,'full'),
			'thumbnail' => wp_get_attachment_image_src($id,'thumbnail')
			);
	}
	$response = json_encode($response);

	// response output
	header( "Content-Type: application/json" );
	echo $response;

	wp_die(); // this is required to terminate immediately and return a proper response
}
add_action( 'wp_ajax_flickity_ajax', 'flickity_ajax_callback' );


/**
 * Load media files needed for Uploader
 * @since  0.1
 */
function load_wp_media_files() {
  wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'load_wp_media_files' );


/**
 * Load Media Uploader
 * @since  0.1
 */
function wp_flickity_needle_scripts() {
	wp_enqueue_script('media-upload');
}
add_action('admin_print_scripts', 'wp_flickity_needle_scripts');


/**
 * FRONTEND FLICKITY CSS
 * @since  0.1
 * @uses  cdn
 */
function wp_flickity_css_library() {
	wp_enqueue_style( 'wp-flickity-css', 'https://npmcdn.com/flickity@1.1/dist/flickity.min.css', false ); 
}
add_action( 'wp_enqueue_scripts', 'wp_flickity_css_library',1 );


/**
 * FRONTEND FLICKITY JS
 * @since  0.1
 * @uses  cdn
 */
function wp_flickity_script_library() {
	wp_enqueue_script( 'wp-flickity-js', 'https://npmcdn.com/flickity@1.1/dist/flickity.pkgd.min.js', false );
}
add_action( 'wp_enqueue_scripts', 'wp_flickity_script_library',100 );


/**
 * WP FLICKITY MAIN SHORTCODE FUNCTION
 * @param  (array/shortcodeAtts) $wp_fkty_props Properties of flickity
 * @return (html/text) This function Fires the result of the slider configured at backend
 * @since  0.1
 */
function wp_flickity( $wp_fkty_props ) {
	$wp_fkty_props = shortcode_atts( array(
		'id' => 0
	), $wp_fkty_props );
	$flickity_slider_id = $wp_fkty_props['id'];
	$flickity_html_shortcode = "";
	global 	$wpdb,
			$wp_flickity_table_name;
	$flickity_slider_metadata = $wpdb->get_var( "SELECT flickity_metadata FROM $wp_flickity_table_name WHERE id='$flickity_slider_id'" );
	$flickity_slider_metadata = unserialize($flickity_slider_metadata);
	$flickity_slider_images = explode(',', $flickity_slider_metadata['images_ids']);
	$h = '<div id="wpflickity-'.$flickity_slider_id.'" class="gallery" wp-flickity-sliderid="'.$flickity_slider_id.'">';
	foreach ( $flickity_slider_images as $image_id) {
		$image = wp_get_attachment_image_src($image_id,'large');
		$h.='<img width="'.$image[1].'" height="'.$image[2].'" 
			class="gallery-cell-image" 
			src="'.$image[0].'" />';
	}
	$h .="</div>";
	$script = "
	<script>
	jQuery(document).ready(function(){
		jQuery('[wp-flickity-sliderid=\"".$flickity_slider_id."\"]').flickity({
		  // options
		  cellAlign: 'center',
		  contain: true,
		  imagesLoaded: true,
		  lazyload:2,
		  percentPosition:false
		});
	});
	</script>";
	$flickity_html_shortcode = $h.$script;
	return $flickity_html_shortcode;
}
add_shortcode( 'wp_flickity','wp_flickity' );

/*
                                                                                                                          
		                                                                                                                      dddddddd
		TTTTTTTTTTTTTTTTTTTTTTThhhhhhh                                                                                        d::::::d
		T:::::::::::::::::::::Th:::::h                                                                                        d::::::d
		T:::::::::::::::::::::Th:::::h                                                                                        d::::::d
		T:::::TT:::::::TT:::::Th:::::h                                                                                        d:::::d 
		TTTTTT  T:::::T  TTTTTT h::::h hhhhh           eeeeeeeeeeee             eeeeeeeeeeee    nnnn  nnnnnnnn        ddddddddd:::::d 
		        T:::::T         h::::hh:::::hhh      ee::::::::::::ee         ee::::::::::::ee  n:::nn::::::::nn    dd::::::::::::::d 
		        T:::::T         h::::::::::::::hh   e::::::eeeee:::::ee      e::::::eeeee:::::een::::::::::::::nn  d::::::::::::::::d 
		        T:::::T         h:::::::hhh::::::h e::::::e     e:::::e     e::::::e     e:::::enn:::::::::::::::nd:::::::ddddd:::::d 
		        T:::::T         h::::::h   h::::::he:::::::eeeee::::::e     e:::::::eeeee::::::e  n:::::nnnn:::::nd::::::d    d:::::d 
		        T:::::T         h:::::h     h:::::he:::::::::::::::::e      e:::::::::::::::::e   n::::n    n::::nd:::::d     d:::::d 
		        T:::::T         h:::::h     h:::::he::::::eeeeeeeeeee       e::::::eeeeeeeeeee    n::::n    n::::nd:::::d     d:::::d 
		        T:::::T         h:::::h     h:::::he:::::::e                e:::::::e             n::::n    n::::nd:::::d     d:::::d 
		      TT:::::::TT       h:::::h     h:::::he::::::::e               e::::::::e            n::::n    n::::nd::::::ddddd::::::dd
		      T:::::::::T       h:::::h     h:::::h e::::::::eeeeeeee        e::::::::eeeeeeee    n::::n    n::::n d:::::::::::::::::d
		      T:::::::::T       h:::::h     h:::::h  ee:::::::::::::e         ee:::::::::::::e    n::::n    n::::n  d:::::::::ddd::::d
		      TTTTTTTTTTT       hhhhhhh     hhhhhhh    eeeeeeeeeeeeee           eeeeeeeeeeeeee    nnnnnn    nnnnnn   ddddddddd   ddddd
                                                                                                                              
                Thanks to metafizzy
                http://metafizzy.co

                Original Script framework made by metafizzi
                Visit http://flickity.metafizzy.co for more informations                                                                                                            
                                                                                                                              
 */
//