<?php
/*
Plugin Name: Organization Hub - APL Test
Plugin URI: 
Description: 
Version: 0.0.1
Author: Crystal Barton
Author URI: http://www.crystalbarton.com
*/


//error_reporting(-1);

if( !defined('ORGANIZATION_HUB') ):

define( 'ORGANIZATION_HUB', 'Organization Hub' );

define( 'ORGANIZATION_HUB_PLUGIN_PATH', dirname(__FILE__) );
define( 'ORGANIZATION_HUB_PLUGIN_URL', plugins_url('', __FILE__) );

define( 'ORGANIZATION_HUB_VERSION', '0.0.1' );
define( 'ORGANIZATION_HUB_DB_VERSION', '1.0' );

define( 'ORGANIZATION_HUB_VERSION_OPTION', 'organization-hub-version' );
define( 'ORGANIZATION_HUB_DB_VERSION_OPTION', 'organization-hub-db-version' );

define( 'ORGANIZATION_HUB_OPTIONS', 'organization-hub-options' );
define( 'ORGANIZATION_HUB_LOG_FILE', dirname(__FILE__).'/log.txt' );

endif;



if( is_admin() ):

add_action( 'wp_loaded', array('OrgHub_Main', 'load') );
add_action( 'network_admin_menu', array('OrgHub_Main', 'update'), 5 );
add_action( 'admin_enqueue_scripts', array('OrgHub_Main', 'enqueue_scripts') );

require_once( dirname(__FILE__).'/apl/handler.php' );
require_once( dirname(__FILE__).'/admin-pages/require.php' );

endif;


if( !class_exists('OrgHub_Main') ):
class OrgHub_Main
{
	public static function load()
	{
		$orghub_pages = new APL_Handler( true );
		
		$menu = new APL_AdminMenu( 'orghub', 'Organization Hub' );
		$menu->display_menu_tab_list = true;
		$menu->add_page( new OrgHub_UsersAdminPage );
		$menu->add_page( new OrgHub_UploadAdminPage );
		$menu->add_page( new OrgHub_SitesAdminPage );
		$menu->add_page( new OrgHub_LogAdminPage );
		
		$orghub_pages->add_menu( $menu );
	}
	
	public static function update()
	{
//		$version = get_site_option( ORGANIZATION_HUB_DB_VERSION_OPTION );
//  	if( $version !== ORGANIZATION_HUB_DB_VERSION )
//  	{
 			$model = OrgHub_Model::get_instance();
 			$model->create_tables();
//  	}
 		
 		update_site_option( ORGANIZATION_HUB_VERSION_OPTION, ORGANIZATION_HUB_VERSION );
 		update_site_option( ORGANIZATION_HUB_DB_VERSION_OPTION, ORGANIZATION_HUB_DB_VERSION );
	}

	public static function enqueue_scripts()
	{
		wp_enqueue_script( 'apl-ajax', plugins_url('apl/ajax.js', __FILE__), array('jquery') );
		wp_enqueue_style( 'orghub-style', plugins_url('admin-pages/styles/style.css', __FILE__) );
	}
}
endif;

