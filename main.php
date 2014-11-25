<?php
/*
Plugin Name: Organization Hub
Plugin URI: 
Description: 
Version: 0.0.1
Author: Crystal Barton
Author URI: http://www.crystalbarton.com
*/


define( 'ORGANIZATION_HUB_PLUGIN_PATH', dirname(__FILE__) );

define( 'ORGANIZATION_HUB_VERSION', '0.0.1' );
define( 'ORGANIZATION_HUB_DB_VERSION', '1.0' );

define( 'ORGANIZATION_HUB_VERSION_OPTION', 'organization-hub-version' );
define( 'ORGANIZATION_HUB_DB_VERSION_OPTION', 'organization-hub-db-version' );



if( is_admin() ):

//----------------------------------------------------------------------------------------
// Setup the plugin's admin pages.
//----------------------------------------------------------------------------------------
add_action( 'network_admin_menu', array('OrganizationHub_Main', 'setup_admin_pages') );
add_action( 'admin_init', array('OrganizationHub_Main', 'setup_actions'), 5 );
add_action( 'admin_init', array('OrganizationHub_Main', 'register_settings'), 10 );

endif;


class OrganizationHub_Main
{
	
	private static $_page = null;
	
	
	/**
	 *
	 */
	public static function setup_admin_pages()
	{
	    $hook = add_menu_page(
			'Organization Hub',
			'Organization Hub',
			'manage_network',
			'organization-hub',
			array( 'OrganizationHub_Main', 'show_admin_page' )
		);
		
		//echo 'hook: '.$hook;
        add_action( "load-$hook", array( 'OrganizationHub_Main', 'add_screen_options' ) );

		require_once( dirname(__FILE__).'/functions.php' );
		
 		// $version = get_site_option( ORGANIZATION_HUB_DB_VERSION_OPTION );
//  		if( $version !== ORGANIZATION_HUB_DB_VERSION )
//  		{
 			require_once( ORGANIZATION_HUB_PLUGIN_PATH . '/classes/model.php' );
 			$model = OrganizationHub_Model::get_instance();
 			$model->create_tables();
//  		}
 		
 		update_site_option( ORGANIZATION_HUB_VERSION_OPTION, ORGANIZATION_HUB_VERSION );
 		update_site_option( ORGANIZATION_HUB_DB_VERSION_OPTION, ORGANIZATION_HUB_DB_VERSION );
	}

      		
	/**
	 *
	 */
	public static function init_page()
	{
		if( self::$_page !== null ) return true;
		
		global $nh_admin_pages, $pagenow;
		switch( $pagenow )
		{
			case 'options.php':
				$page = ( !empty($_POST['option_page']) ? $_POST['option_page'] : null );
				break;
			
			case 'admin.php':
				$page = ( !empty($_GET['page']) ? $_GET['page'] : null );
				break;
			
			default: return false; break;
		}
		
		if( $page !== 'organization-hub' ) return false;
		
		$path = ORGANIZATION_HUB_PLUGIN_PATH . '/admin-page/organization-hub.php';
		if( !file_exists($path) ) return false;
		
		require_once( dirname(__FILE__).'/admin-page.php' );
		require_once( $path );
		
		self::$_page = call_user_func( array('OrganizationHub_AdminPage_Main', 'get_instance'), 'organization-hub' );
		return true;
	}
	
	
	/**
	 *
	 */
	public static function setup_actions()
	{
		global $pagenow;
		switch( $pagenow )
		{
			case 'options.php':
				if( !self::init_page() ) return;
				break;
			
			case 'admin.php':
				if( !self::init_page() ) return;
				add_action( 'admin_enqueue_scripts', array(self::$_page, 'enqueue_scripts') );
				add_action( 'admin_head', array(self::$_page, 'add_head_script') );
				break;
			
			default: return; break;
		}
		
		add_action( self::$_page->slug.'-register-settings', array(self::$_page, 'register_settings') );
		add_action( self::$_page->slug.'-add-settings-sections', array(self::$_page, 'add_settings_sections') );
		add_action( self::$_page->slug.'-add-settings-fields', array(self::$_page, 'add_settings_fields') );
	}


	/**
	 *
	 */
	public static function register_settings()
	{
		if( !self::init_page() ) return;
		
		do_action( self::$_page->slug.'-register-settings' );
		do_action( self::$_page->slug.'-add-settings-sections' );
		do_action( self::$_page->slug.'-add-settings-fields' );
		
// 		register_setting( self::$_page->slug, 'organization-hub-options' );
// 		add_filter( 'sanitize_option_organization-hub-options', array(get_class(), 'process_input'), 10, 2 );
	}
	
	
	/**
	 *
	 */
	public static function process_input( $input, $option )
	{
		$page = $_POST['option_page'];
		$tab = ( !empty($_POST['tab']) ? $_POST['tab'] : null );
		$post = ( !empty($_POST[$option]) ? $_POST[$option] : null );
		$options = get_site_option( 'organization-hub-options', array() );
		
		if( $tab !== null )
		$options = apply_filters( $page.'-'.$tab.'-process-input', $options, $page, $tab, $option, $post );
		
		$options = apply_filters( $page.'-process-input', $options, $page, $tab, $option, $post );
		
		return $options;
	}
	

	/**
	 *
	 */
	public static function show_admin_page()
	{
		if( !self::init_page() ) return;
		self::$_page->show();
	}
	
	
	/**
	 *
	 */
	public static function add_screen_options()
	{
		if( !self::init_page() ) return;
		self::$_page->add_screen_options();
	}

}	

