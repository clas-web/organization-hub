<?php
/**
 * OrgHub_UsersAdminPage
 * 
 * This class controls the admin page "Users".
 * 
 * @package    orghub
 * @subpackage admin-pages/pages
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_UsersAdminPage') ):
class OrgHub_UsersAdminPage extends APL_AdminPage
{
	
	/**
	 * Creates an OrgHub_UsersAdminPage object.
	 */
	public function __construct()
	{
		parent::__construct( 'users', 'Users', 'Users' );
		
		$this->add_tab( new OrgHub_UsersListTabAdminPage($this) );
		$this->add_tab( new OrgHub_UsersUploadTabAdminPage($this) );
		$this->add_tab( new OrgHub_UsersLogTabAdminPage($this) );
	}
		
} // class OrgHub_UsersAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_UsersAdminPage') )

