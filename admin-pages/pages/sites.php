<?php



/**
 * OrgHub_SitesAdminPage
 * 
 * 
 * 
 * @package    orghub
 * @subpackage admin-pages/pages
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_SitesAdminPage') ):
class OrgHub_SitesAdminPage extends APL_AdminPage
{
	
	public function __construct()
	{
		parent::__construct( 'sites', 'Sites', 'Sites' );
	}
	
	public function display()
	{
		echo 'SITES';
	}
	
} // class OrgHub_SitesAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_SitesAdminPage') ):

