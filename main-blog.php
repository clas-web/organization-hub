<?php
/*
Plugin Name: Organization Hub (Site)
Plugin URI: 
Description: 
Version: 0.0.1
Author: Crystal Barton
Author URI: http://www.crystalbarton.com
Network: False
*/


register_activation_hook( __FILE__, array('OrgHub_MainBlog', 'activate') );

require( dirname(__FILE__).'/main.php' );

if( is_admin() ):

add_action( 'wp_loaded', array('OrgHub_MainBlog', 'load') );
add_action( 'admin_menu', array('OrgHub_MainBlog', 'update'), 5 );

endif;


if( !class_exists('OrgHub_MainBlog') ):
class OrgHub_MainBlog
{
	
	public static function load()
	{
		require_once( dirname(__FILE__).'/admin-pages/require.php' );
		
		// Site admin page.
		$orghub_pages = new APL_Handler( false );

		$orghub_pages->add_page( new OrgHub_UploadAdminPage('orghub-upload') );
		$orghub_pages->setup();
	}
	
	public static function activate()
	{
		if ( ! $network_wide )
			return;

		deactivate_plugins( plugin_basename( __FILE__ ), TRUE, TRUE );

		header( 'Location: ' . network_admin_url( 'plugins.php?deactivate=true' ) );
		exit;
	}

	public static function update()
	{
//		$version = get_option( ORGANIZATION_HUB_DB_VERSION_OPTION );
//  	if( $version !== ORGANIZATION_HUB_DB_VERSION )
//  	{
 			$model = OrgHub_Model::get_instance();
 			$model->create_tables();
//  	}
 		
 		update_option( ORGANIZATION_HUB_VERSION_OPTION, ORGANIZATION_HUB_VERSION );
 		update_option( ORGANIZATION_HUB_DB_VERSION_OPTION, ORGANIZATION_HUB_DB_VERSION );
	}
	
}
endif;

