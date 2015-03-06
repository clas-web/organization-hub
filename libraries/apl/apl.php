<?php
/**
 * Setup the APL library for use in a plugin or theme.
 * Includes the required files needed for APL to function.
 * 
 * @package    apl
 * @author     Crystal Barton <cbarto11@uncc.edu>
 * @copyright  2014-2015 
 * @version    1.0
 */


$apl_version = '1.0';

global $apl_libraries;
if( empty($apl_libraries) ) $apl_libraries = array();

$apl_libraries[$apl_version] = array(
	dirname(__FILE__).'/functions.php',
	dirname(__FILE__).'/admin-menu.php',
	dirname(__FILE__).'/admin-page.php',
	dirname(__FILE__).'/tab-admin-page.php',
	dirname(__FILE__).'/tab-link.php',
	dirname(__FILE__).'/handler.php',
);


add_action( 'plugins_loaded', 'apl_load', 1 );


/**
 * 
 */
if( !function_exists('apl_load') ):
function apl_load()
{
	if( defined('APL') ) return;
	
	global $apl_libraries;
	ksort( $apl_libraries, SORT_STRING );
	
	$library_versions = array_keys($apl_libraries);
	if( count($library_versions) == 0 )
	{
		die( 'No Admin Page Library available to load.' );
	}
	$version = $library_versions[ count($library_versions)-1 ];
	
	define( 'APL', 'Admin Page Library' );
	define( 'APL_VERSION', $version );

	foreach( $apl_libraries[$version] as $file )
	{
		require_once( $file );
	}
	
// 	apl_print( APL_VERSION );
}
endif;

