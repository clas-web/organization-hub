<?php
/**
 * OrgHub_LogAdminPage
 * 
 * This class controls the admin page "Log".
 * 
 * @package    orghub
 * @subpackage admin-pages/pages
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_LogAdminPage') ):
class OrgHub_LogAdminPage extends APL_AdminPage
{
	
	/**
	 * Creates an OrgHub_LogAdminPage object.
	 */
	public function __construct()
	{
		parent::__construct( 'log', 'Log', 'Log' );
	}
	
	
	/**
	 * Displays the current admin page.
	 */
	public function display()
	{
		echo 'LOG';
	}
	
} // class OrgHub_LogAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_LogAdminPage') ):

