<?php
/*
Plugin Name: Organization Hub (Site)
Plugin URI: 
Description: 
Version: 0.0.1
Author: Crystal Barton
Author URI: http://www.crystalbarton.com
*/


require( dirname(__FILE__).'/main.php' );

if( is_admin() ):

add_action( 'wp_loaded', array('OrgHub_MainBlog', 'load') );
add_action( 'admin_menu', array('OrgHub_Main', 'update'), 5 );

endif;


if( !class_exists('OrgHub_MainBlog') ):
class OrgHub_MainBlog
{
	
	public static function load()
	{
		// Site admin page.
		$orghub_pages = new APL_Handler( false );

		$orghub_pages->add_page( new OrgHub_UploadAdminPage('orghub-upload') );
		$orghub_pages->setup();
	}
	
}
endif;

