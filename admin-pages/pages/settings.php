<?php
/**
 * OrgHub_SettingsAdminPage
 * 
 * 
 * 
 * @package    orghub
 * @subpackage admin-pages/pages
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_SettingsAdminPage') ):
class OrgHub_SettingsAdminPage extends APL_AdminPage
{
	
	/**
	 * 
	 */
	public function __construct()
	{
		parent::__construct( 'settings', 'Settings', 'Settings' );
	}
	

	/**
	 * 
	 */
	public function display()
	{
		echo 'SETTINGS';
	}
	
} // class OrgHub_SettingsAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_SettingsAdminPage') ):

