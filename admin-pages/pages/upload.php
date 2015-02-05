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
	
	private $model = null;
	
	
	/**
	 * Creates an OrgHub_UploadAdminPage object.
	 */
	public function __construct()
	{
		parent::__construct( 'upload', 'Upload', 'Upload' );
        $this->model = OrgHub_Model::get_instance();
        
		$this->add_tab( new OrgHub_UsersUploadTabAdminPage($this) );
		$this->add_tab( new OrgHub_SitesUploadTabAdminPage($this) );
		$this->add_tab( new OrgHub_ContentUploadTabAdminPage($this) );
	}
	
} // class OrgHub_UploadAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_UploadAdminPage') ):

