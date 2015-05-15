<?php
/*
Plugin Name: Organization Hub (Network)
Plugin URI: 
Description: 
Version: 0.0.1
Author: Crystal Barton
Author URI: http://www.crystalbarton.com
Network: True
*/


require( dirname(__FILE__).'/main.php' );

if( is_admin() ):
add_action( 'wp_loaded', array('OrgHub_MainNetwork', 'load') );
endif;


if( !class_exists('OrgHub_MainNetwork') ):
class OrgHub_MainNetwork
{
	
	public static function load()
	{
		require_once( dirname(__FILE__).'/admin-pages/require.php' );
		
		// Network admin pages.
		$orghub_pages = new APL_Handler( true );
		
		$menu = new APL_AdminMenu( 'orghub', 'Organization Hub' );
		$menu->add_page( new OrgHub_UsersAdminPage );
		$menu->add_page( new OrgHub_SitesAdminPage );
		$menu->add_page( new OrgHub_UploadAdminPage );
		$menu->add_page( new OrgHub_SettingsAdminPage );
		
		$orghub_pages->add_menu( $menu );
		$orghub_pages->setup();

		if( $orghub_pages->controller )
		{
			add_action( 'admin_enqueue_scripts', array('OrgHub_Main', 'enqueue_scripts') );
			add_action( 'network_admin_menu', array('OrgHub_MainNetwork', 'update'), 5 );
		}
	}

	public static function update()
	{
		$version = get_site_option( ORGANIZATION_HUB_DB_VERSION_OPTION );
		if( $version !== ORGANIZATION_HUB_DB_VERSION )
		{
 			$model = OrgHub_Model::get_instance();
 			$model->create_tables();
		}
 		
 		update_site_option( ORGANIZATION_HUB_VERSION_OPTION, ORGANIZATION_HUB_VERSION );
 		update_site_option( ORGANIZATION_HUB_DB_VERSION_OPTION, ORGANIZATION_HUB_DB_VERSION );
	}
	
}
endif;

