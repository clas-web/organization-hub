<?php
/**
 * OrgHub_SitesAdminPage
 * 
 * This class controls the admin page "Sites".
 * 
 * @package    orghub
 * @subpackage admin-pages/pages
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_SitesAdminPage') ):
class OrgHub_SitesAdminPage extends APL_AdminPage
{
	
	/**
	 * Creates an OrgHub_SitesAdminPage object.
	 */
	public function __construct()
	{
		parent::__construct( 'sites', 'Sites', 'Sites' );
		
		$this->add_tab( new OrgHub_SitesListTabAdminPage($this) );
		$this->add_tab( new OrgHub_SitesUploadTabAdminPage($this) );
		$this->add_tab( new OrgHub_SitesLogTabAdminPage($this) );
	}
	
} // class OrgHub_SitesAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_SitesAdminPage') )

