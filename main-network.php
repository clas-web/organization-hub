<?php
/*
Plugin Name: Organization Hub (Network)
Plugin URI: 
Description: 
Version: 0.0.1
Author: Crystal Barton
Author URI: http://www.crystalbarton.com
*/


require( dirname(__FILE__).'/main.php' );

if( is_admin() ):

add_action( 'wp_loaded', array('OrgHub_MainNetwork', 'load') );
add_action( 'admin_menu', array('OrgHub_Main', 'update'), 5 );

endif;


if( !class_exists('OrgHub_MainNetwork') ):
class OrgHub_MainNetwork
{
	
	public static function load()
	{
		// Network admin pages.
		$orghub_pages = new APL_Handler( true );
		
		$menu = new APL_AdminMenu( 'orghub', 'Organization Hub' );
		$menu->add_page( new OrgHub_UsersAdminPage );
		$menu->add_page( new OrgHub_SitesAdminPage );
		$menu->add_page( new OrgHub_UploadAdminPage );
		
		$orghub_pages->add_menu( $menu );
		$orghub_pages->setup();
	}
	
}
endif;

