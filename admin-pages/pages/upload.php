<?php
/**
 * Controls the admin page "Upload".
 * 
 * @package    organization-hub
 * @subpackage admin-pages/pages
 * @author     Crystal Barton <atrus1701@gmail.com>
 */

if( !class_exists('OrgHub_UploadAdminPage') ):
class OrgHub_UploadAdminPage extends APL_AdminPage
{
	
	/**
	 * Constructor.
	 */
	public function __construct(
		$name = 'upload',
		$menu_title = 'Batch Upload',
		$page_title = 'Batch Upload',
		$capability = 'administrator' )
	{
		parent::__construct( $name, $menu_title, $page_title, $capability );
		
		$this->add_tab( new OrgHub_UploadListTabAdminPage($this) );
		$this->add_tab( new OrgHub_UploadUploadTabAdminPage($this) );
		$this->add_tab( new OrgHub_UploadLogTabAdminPage($this) );
		
		if( !is_network_admin() )
		{
			$this->add_tab( new OrgHub_UploadSettingsTabAdminPage($this) );
		}
	}
	
} // class OrgHub_UploadAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_UploadAdminPage') )

