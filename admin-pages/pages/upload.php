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
	public function __construct()
	{
		parent::__construct( 'upload', 'Batch Upload', 'Batch Upload' );
        
		$this->add_tab( new OrgHub_UploadListTabAdminPage($this) );
		$this->add_tab( new OrgHub_UploadUploadTabAdminPage($this) );
		$this->add_tab( new OrgHub_UploadLogTabAdminPage($this) );
	}
	
} // class OrgHub_UploadAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_UploadAdminPage') )

