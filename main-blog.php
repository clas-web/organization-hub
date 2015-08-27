<?php
/*
Plugin Name: Organization Hub (Site)
Plugin URI: https://github.com/clas-web/organization-hub
Description: The Organization Hub (Site) is a batch importer for posts, pages, links, taxonomies, and users.
Version: 1.0
Author: Crystal Barton
Author URI: https://www.linkedin.com/in/crystalbarton
Network: False
*/


register_activation_hook( __FILE__, 'orghubsite_activate' );
require( __DIR__.'/main.php' );


if( is_admin() ):
	add_action( 'wp_loaded', 'orghubsite_load' );
endif;


/**
 * Setup the site admin pages.
 */
if( !function_exists('orghubsite_load') ):
function orghubsite_load()
{
	require_once( __DIR__.'/admin-pages/require.php' );
	
	$orghub_pages = new APL_Handler( false );

	$orghub_pages->add_page( new OrgHub_UploadAdminPage('orghub-upload') );
	$orghub_pages->setup();
	
	if( $orghub_pages->controller )
	{
		add_action( 'admin_enqueue_scripts', 'orghub_enqueue_scripts' );
		add_action( 'admin_menu', 'orghubsite_update', 5 );
	}
}
endif;


/**
 * Prevent activation for an individual site.
 * @param  bool  $network_wide  True if the network activated, else False.
 */
if( !function_exists('orghubsite_activate') ):
function orghubsite_activate( $network_wide )
{
	if( !$network_wide ) return;

	deactivate_plugins( plugin_basename(__FILE__), true, true );

	header( 'Location: '.network_admin_url( 'plugins.php?deactivate=true' ) );
	exit;
}
endif;


/**
 * Update the database if a version change.
 */
if( !function_exists('orghubsite_update') ):
function orghubsite_update()
{
	$version = get_option( ORGANIZATION_HUB_DB_VERSION_OPTION );
	if( $version !== ORGANIZATION_HUB_DB_VERSION )
	{
		$model = OrgHub_Model::get_instance();
		$model->create_tables();
	}
		
	update_option( ORGANIZATION_HUB_VERSION_OPTION, ORGANIZATION_HUB_VERSION );
	update_option( ORGANIZATION_HUB_DB_VERSION_OPTION, ORGANIZATION_HUB_DB_VERSION );
}
endif;

