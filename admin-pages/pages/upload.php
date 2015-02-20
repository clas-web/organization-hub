<?php
/**
 * OrgHub_UploadAdminPage
 * 
 * This class controls the admin page "Upload".
 * 
 * @package    orghub
 * @subpackage admin-pages/pages
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_UploadAdminPage') ):
class OrgHub_UploadAdminPage extends APL_AdminPage
{
	
	/**
	 * Creates an OrgHub_UploadAdminPage object.
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
	}
	
} // class OrgHub_UploadAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_UploadAdminPage') )

