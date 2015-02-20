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
	public function __construct(
		$name = 'sites',
		$menu_title = 'Sites',
		$page_title = 'Sites',
		$capability = 'administrator' )
	{
		parent::__construct( $name, $menu_title, $page_title, $capability );
		
		$this->add_tab( new OrgHub_SitesListTabAdminPage($this) );
		$this->add_tab( new OrgHub_SitesUploadTabAdminPage($this) );
		$this->add_tab( new OrgHub_SitesLogTabAdminPage($this) );
	}
	
} // class OrgHub_SitesAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_SitesAdminPage') )

