<?php
/*
Plugin Name: Organization Hub (Network)
Plugin URI: https://github.com/clas-web/organization-hub
Description: The Organization Hub (Network) is a collection of useful tools for maintaining a multisite WordPress instance for an organization.  The Users page keeps track of the organization users, their profile site, and any connections posts (see Connections Hub).  The Sites page is a listing of all the current sites, its posts and pages count, and the last time it was updated and by whom.  The Upload page is used to batch import large amounts of posts, pages, links, taxonomies, users, and sites.
Version: 1.0.1
Author: Crystal Barton
Author URI: https://www.linkedin.com/in/crystalbarton
Network: True
*/


require( __DIR__.'/main.php' );


if( is_admin() ):
	add_action( 'wp_loaded', 'orghubnet_load' );
endif;


/**
 * Setup the network admin pages.
 */
if( !function_exists('orghubnet_load') ):
function orghubnet_load()
{
	require_once( __DIR__.'/admin-pages/require.php' );
	
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
		add_action( 'admin_enqueue_scripts', 'orghub_enqueue_scripts' );
		add_action( 'network_admin_menu', 'orghubnet_update', 5 );
	}
}
endif;


/**
 * Update the database if a version change.
 */
if( !function_exists('orghubnet_update') ):
function orghubnet_update()
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
endif;

